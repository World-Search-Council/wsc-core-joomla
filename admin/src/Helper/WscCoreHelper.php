<?php

namespace Joomla\Component\WscAnalytics\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Log\Log;
use PDO;
use Exception;

/**
 * Core storage, encryption, and gateway sync engine for Joomla.
 */
class WscCoreHelper {

    public const GATEWAY_DEFAULT = 'https://api.worldseocouncil.com';

    private static ?PDO $queuePdo = null;
    private static ?PDO $reportsPdo = null;

    /**
     * Get or create secure data storage directory.
     */
    public static function getStorageDir(): string {
        $dir = JPATH_ADMINISTRATOR . '/cache/wsc_analytics';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }

        // Security Hardening: write .htaccess, web.config, index.php
        $htaccess = $dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Require all denied\nDeny from all\n");
        }
        $webconfig = $dir . '/web.config';
        if (!file_exists($webconfig)) {
            @file_put_contents($webconfig, '<configuration><system.webServer><authorization><deny users="*" /></authorization></system.webServer></configuration>');
        }
        $indexphp = $dir . '/index.html';
        if (!file_exists($indexphp)) {
            @file_put_contents($indexphp, '<!DOCTYPE html><html><body bgcolor="#FFFFFF"></body></html>');
        }

        return $dir;
    }

    /**
     * Get PDO SQLite Queue connection with WAL acceleration.
     */
    public static function getQueuePdo(): PDO {
        if (self::$queuePdo === null) {
            $path = self::getStorageDir() . '/queue_temp.sqlite';
            self::$queuePdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$queuePdo->exec('PRAGMA journal_mode = WAL;');
            self::$queuePdo->exec('PRAGMA synchronous = NORMAL;');
            self::$queuePdo->exec('PRAGMA busy_timeout = 5000;');
        }
        return self::$queuePdo;
    }

    /**
     * Get PDO SQLite Reports connection.
     */
    public static function getReportsPdo(): PDO {
        if (self::$reportsPdo === null) {
            $path = self::getStorageDir() . '/reports_temp.sqlite';
            self::$reportsPdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$reportsPdo->exec('PRAGMA journal_mode = WAL;');
            self::$reportsPdo->exec('PRAGMA synchronous = NORMAL;');
            self::$reportsPdo->exec('PRAGMA busy_timeout = 5000;');
        }
        return self::$reportsPdo;
    }

    /**
     * Initialize SQLite database table schemas.
     */
    public static function initSchemas(): void {
        try {
            $qPdo = self::getQueuePdo();
            $qPdo->exec("
                CREATE TABLE IF NOT EXISTS wsc_events_queue (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    created_at INTEGER NOT NULL,
                    event_type TEXT NOT NULL,
                    url_path TEXT NOT NULL,
                    referrer TEXT,
                    payload_json TEXT NOT NULL,
                    synced INTEGER DEFAULT 0
                );
                CREATE INDEX IF NOT EXISTS idx_wsc_queue_synced ON wsc_events_queue (synced, created_at);
            ");

            $rPdo = self::getReportsPdo();
            $rPdo->exec("
                CREATE TABLE IF NOT EXISTS wsc_dashboard_reports (
                    id TEXT PRIMARY KEY,
                    report_type TEXT NOT NULL,
                    period_type TEXT NOT NULL,
                    updated_at INTEGER NOT NULL,
                    payload TEXT NOT NULL
                );

                CREATE TABLE IF NOT EXISTS wsc_daily_overview (
                    date TEXT PRIMARY KEY,
                    human_visitors INTEGER DEFAULT 0,
                    human_pageviews INTEGER DEFAULT 0,
                    bot_hits INTEGER DEFAULT 0,
                    ai_training_hits INTEGER DEFAULT 0,
                    ai_rag_hits INTEGER DEFAULT 0,
                    interaction_events INTEGER DEFAULT 0,
                    human_pct REAL DEFAULT 100.0
                );

                CREATE TABLE IF NOT EXISTS wsc_bot_stats (
                    date TEXT NOT NULL,
                    bot_name TEXT NOT NULL,
                    bot_category TEXT NOT NULL,
                    bot_type TEXT NOT NULL,
                    hits INTEGER DEFAULT 1,
                    blocked INTEGER DEFAULT 0,
                    PRIMARY KEY (date, bot_name)
                );
            ");
        }
        catch (Exception $e) {
            Log::add('WSC SQLite Schema Init Error: ' . $e->getMessage(), Log::ERROR, 'com_wsc_analytics');
        }
    }

    /**
     * Get or initialize Project Credentials (Project ID + AES Key).
     */
    public static function getProjectCredentials(): array {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        $projectId = $params->get('project_id', '');
        $localKey  = $params->get('local_key', '');

        if (empty($projectId) || empty($localKey)) {
            $projectId = 'wsc_joo_' . bin2hex(random_bytes(8));
            $localKey  = bin2hex(random_bytes(32)); // 256-bit AES key

            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_wsc_analytics'));
            $db->setQuery($query);
            $rawParams = $db->loadResult();

            $pObj = json_decode($rawParams ?: '{}', true) ?: [];
            $pObj['project_id'] = $projectId;
            $pObj['local_key']  = $localKey;

            $update = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($pObj)))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_wsc_analytics'));
            $db->setQuery($update);
            $db->execute();

            self::registerWithGateway($projectId, $localKey);
        }

        return [
            'project_id' => $projectId,
            'local_key'  => $localKey,
        ];
    }

    /**
     * Register project key with WSC Gateway.
     */
    public static function registerWithGateway(string $projectId, string $localKey): bool {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        $gatewayUrl = $params->get('gateway_url', self::GATEWAY_DEFAULT);
        $url = rtrim($gatewayUrl, '/') . '/v1/register';

        $payload = json_encode([
            'project_id' => $projectId,
            'local_key'  => $localKey,
            'cms'        => 'joomla',
            'version'    => '1.5.0',
        ]);

        try {
            $http = HttpFactory::getHttp();
            $response = $http->post($url, $payload, ['Content-Type' => 'application/json'], 8);
            if ($response->code === 200 || $response->code === 201) {
                return true;
            }
        }
        catch (Exception $e) {
            Log::add('WSC Gateway Registration deferred: ' . $e->getMessage(), Log::WARNING, 'com_wsc_analytics');
        }

        return false;
    }

    /**
     * Queue event into SQLite write buffer.
     */
    public static function trackEvent(string $type, string $path = '/', ?string $referrer = '', array $meta = []): bool {
        try {
            $pdo = self::getQueuePdo();
            $stmt = $pdo->prepare("
                INSERT INTO wsc_events_queue (created_at, event_type, url_path, referrer, payload_json, synced)
                VALUES (:created_at, :event_type, :url_path, :referrer, :payload_json, 0)
            ");

            $stmt->execute([
                ':created_at'   => time(),
                ':event_type'   => $type,
                ':url_path'     => $path ?: '/',
                ':referrer'     => $referrer ?: '',
                ':payload_json' => json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]);

            return true;
        }
        catch (Exception $e) {
            Log::add('WSC Queue Error: ' . $e->getMessage(), Log::ERROR, 'com_wsc_analytics');
            return false;
        }
    }

    /**
     * Flush un-synced micro-batch events to Gateway.
     */
    public static function processBatchExport(int $batchSize = 500): int {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        if (!$params->get('hybrid_enabled', 1)) {
            return 0;
        }

        $creds = self::getProjectCredentials();
        $pdo = self::getQueuePdo();

        $stmt = $pdo->prepare("SELECT * FROM wsc_events_queue WHERE synced = 0 ORDER BY id ASC LIMIT :limit");
        $stmt->bindValue(':limit', $batchSize, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return 0;
        }

        $ids = [];
        $events = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['id'];
            $events[] = [
                't' => (int) $row['created_at'],
                'e' => $row['event_type'],
                'p' => $row['url_path'],
                'r' => $row['referrer'],
                'm' => json_decode($row['payload_json'], true) ?: [],
            ];
        }

        $payloadJson = json_encode(['events' => $events], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Encrypt with AES-256-CBC
        $keyBinary = hex2bin($creds['local_key']);
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($payloadJson, 'AES-256-CBC', $keyBinary, OPENSSL_RAW_DATA, $iv);
        $encryptedBody = base64_encode($iv . $ciphertext);

        // Sign with HMAC-SHA256
        $signature = hash_hmac('sha256', $encryptedBody, $creds['local_key']);

        $gatewayUrl = $params->get('gateway_url', self::GATEWAY_DEFAULT);
        $url = rtrim($gatewayUrl, '/') . '/v1/ingest';

        try {
            $http = HttpFactory::getHttp();
            $headers = [
                'Content-Type'  => 'application/json',
                'X-WSC-Project' => $creds['project_id'],
                'X-WSC-Sig'     => $signature,
            ];

            $response = $http->post($url, $encryptedBody, $headers, 10);
            if ($response->code === 200 || $response->code === 202) {
                $inClause = implode(',', $ids);
                $pdo->exec("UPDATE wsc_events_queue SET synced = 1 WHERE id IN ($inClause)");
                return count($ids);
            }
        }
        catch (Exception $e) {
            Log::add('WSC Ingest Sync Error: ' . $e->getMessage(), Log::WARNING, 'com_wsc_analytics');
        }

        return 0;
    }

    /**
     * Save incoming 7-Card reports from WSC Gateway Webhook.
     */
    public static function saveWebhookReport(array $data): bool {
        try {
            $pdo = self::getReportsPdo();
            $stmt = $pdo->prepare("
                INSERT INTO wsc_dashboard_reports (id, report_type, period_type, updated_at, payload)
                VALUES (:id, :report_type, :period_type, :updated_at, :payload)
                ON CONFLICT(id) DO UPDATE SET
                    updated_at = excluded.updated_at,
                    payload = excluded.payload
            ");

            $now = time();

            $cardMappings = [
                'card1'           => ['traffic', $data['card1'] ?? $data['card1_traffic'] ?? null],
                'card1_traffic'   => ['traffic', $data['card1_traffic'] ?? $data['card1'] ?? null],
                'card2'           => ['insights', $data['card2'] ?? $data['card2_insights'] ?? null],
                'card2_insights'  => ['insights', $data['card2_insights'] ?? $data['card2'] ?? null],
                'card3'           => ['bots', $data['card3'] ?? $data['card3_bots'] ?? null],
                'card3_bots'      => ['bots', $data['card3_bots'] ?? $data['card3'] ?? null],
                'card_engagement' => ['engagement', $data['card_engagement'] ?? $data['card4'] ?? null],
                'card_outbound'   => ['outbound', $data['card_outbound'] ?? $data['card5'] ?? null],
                'card_ecommerce'  => ['ecommerce', $data['card_ecommerce'] ?? $data['card6'] ?? null],
                'domain_health'   => ['sentinel', $data['domain_health'] ?? $data['sentinel'] ?? $data['card7'] ?? null],
                'pagespeed'       => ['pagespeed', $data['pagespeed'] ?? null],
            ];

            foreach ($cardMappings as $cardId => $meta) {
                $type = $meta[0];
                $payload = $meta[1];
                if ($payload !== null) {
                    $stmt->execute([
                        ':id'          => $cardId,
                        ':report_type' => $type,
                        ':period_type' => 'multi',
                        ':updated_at'  => $now,
                        ':payload'     => is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }

            return true;
        }
        catch (Exception $e) {
            Log::add('WSC Webhook Save Error: ' . $e->getMessage(), Log::ERROR, 'com_wsc_analytics');
            return false;
        }
    }

    /**
     * Get all 7 Cards for Dashboard view.
     */
    public static function getDashboardCards(): array {
        $cards = [
            'card1'           => null,
            'card2'           => null,
            'card3'           => null,
            'card1_traffic'   => null,
            'card2_insights'  => null,
            'card3_bots'      => null,
            'card_engagement' => null,
            'card_outbound'   => null,
            'card_ecommerce'  => null,
            'domain_health'   => null,
            'pagespeed'       => null,
        ];

        try {
            $pdo = self::getReportsPdo();
            $stmt = $pdo->query("SELECT id, payload FROM wsc_dashboard_reports");
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $id = $row['id'];
                $decoded = json_decode($row['payload'], true);
                $cards[$id] = $decoded;

                // Populate alias mappings
                if ($id === 'card1' && empty($cards['card1_traffic'])) $cards['card1_traffic'] = $decoded;
                if ($id === 'card1_traffic' && empty($cards['card1'])) $cards['card1'] = $decoded;
                if ($id === 'card2' && empty($cards['card2_insights'])) $cards['card2_insights'] = $decoded;
                if ($id === 'card2_insights' && empty($cards['card2'])) $cards['card2'] = $decoded;
                if ($id === 'card3' && empty($cards['card3_bots'])) $cards['card3_bots'] = $decoded;
                if ($id === 'card3_bots' && empty($cards['card3'])) $cards['card3'] = $decoded;
            }
        }
        catch (Exception $e) {
            Log::add('WSC Read Cards Error: ' . $e->getMessage(), Log::ERROR, 'com_wsc_analytics');
        }

        return $cards;
    }

    /**
     * Trigger SpeedSentinel 2.0 Lab Audit from Joomla.
     */
    public static function triggerSpeedSentinelAudit(string $url, string $strategy = 'mobile'): array {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        $gatewayUrl = $params->get('gateway_url', self::GATEWAY_DEFAULT);
        $creds = self::getProjectCredentials();

        $body = json_encode(['url' => $url, 'strategy' => $strategy]);
        $sig = hash_hmac('sha256', $body, $creds['local_key']);

        try {
            $http = HttpFactory::getHttp();
            $headers = [
                'Content-Type'  => 'application/json',
                'X-WSC-Project' => $creds['project_id'],
                'X-WSC-Sig'     => $sig,
            ];

            $response = $http->post(rtrim($gatewayUrl, '/') . '/v1/pagespeed/audit', $body, $headers, 15);
            $decoded = json_decode((string) $response->body, true);
            return $decoded ?: ['ok' => true, 'status' => 'queued'];
        }
        catch (Exception $e) {
            Log::add('SpeedSentinel Audit Error: ' . $e->getMessage(), Log::WARNING, 'com_wsc_analytics');
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Trigger Sentinel Domain Health Scan from Joomla.
     */
    public static function triggerSentinelScan(string $domain): array {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        $gatewayUrl = $params->get('gateway_url', self::GATEWAY_DEFAULT);
        $creds = self::getProjectCredentials();

        $body = json_encode(['domain' => $domain, 'sync' => false]);
        $sig = hash_hmac('sha256', $body, $creds['local_key']);

        try {
            $http = HttpFactory::getHttp();
            $headers = [
                'Content-Type'  => 'application/json',
                'X-WSC-Project' => $creds['project_id'],
                'X-WSC-Sig'     => $sig,
            ];

            $response = $http->post(rtrim($gatewayUrl, '/') . '/v1/sentinel/scan', $body, $headers, 15);
            $decoded = json_decode((string) $response->body, true);
            return $decoded ?: ['ok' => true, 'status' => 'queued'];
        }
        catch (Exception $e) {
            Log::add('Sentinel Scan Error: ' . $e->getMessage(), Log::WARNING, 'com_wsc_analytics');
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Daily maintenance and SQLite optimization.
     */
    public static function cleanupOldData(): void {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        $retention = (int) $params->get('retention_days', 30);
        $threshold = time() - ($retention * 86400);

        try {
            $pdo = self::getQueuePdo();
            $pdo->exec("DELETE FROM wsc_events_queue WHERE synced = 1 AND created_at < $threshold");

            $pdo->exec('PRAGMA optimize;');
            self::getReportsPdo()->exec('PRAGMA optimize;');
        }
        catch (Exception $e) {
            Log::add('WSC Maintenance Error: ' . $e->getMessage(), Log::ERROR, 'com_wsc_analytics');
        }
    }
}
