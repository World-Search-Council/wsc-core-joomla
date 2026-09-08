<?php

namespace Joomla\Component\WscAnalytics\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscCoreHelper;

/**
 * Controller for Admin AJAX sync operations.
 */
class ApiController extends BaseController {

    /**
     * AJAX action to manually trigger micro-batch sync.
     */
    public function refresh(): void {
        $this->checkToken('get');

        $synced = WscCoreHelper::processBatchExport();

        echo new JsonResponse([
            'ok' => true,
            'synced_events' => $synced,
            'last_sync' => date('Y-m-d H:i:s'),
        ]);

        $this->app->close();
    }

    /**
     * AJAX action to proxy AI Coverage lookup to WSC Gateway.
     */
    public function aiCoverage(): void {
        $this->checkToken('post');

        $input = $this->input->json;
        $queryUrl = trim((string) $input->get('query', '', 'string'));
        $timeframe = trim((string) $input->get('timeframe', '30d', 'string'));

        if (empty($queryUrl)) {
            echo new JsonResponse(['ok' => false, 'error' => 'query_required'], 400);
            $this->app->close();
        }

        $creds = WscCoreHelper::getProjectCredentials();
        if (empty($creds['project_id']) || empty($creds['api_secret'])) {
            echo new JsonResponse(['ok' => false, 'error' => 'not_configured'], 400);
            $this->app->close();
        }

        $payload = json_encode([
            'project_id' => $creds['project_id'],
            'query'      => $queryUrl,
            'timeframe'  => in_array($timeframe, ['7d', '30d'], true) ? $timeframe : '30d',
        ]);

        $signature = hash_hmac('sha256', $payload, $creds['api_secret']);

        try {
            $http = \Joomla\CMS\Http\HttpFactory::getHttp();
            $headers = [
                'Content-Type' => 'application/json',
                'X-WSC-Sig'    => $signature,
            ];
            $response = $http->post('https://gateway.worldseocouncil.com/v1/ai-coverage/query', $payload, $headers, 10);
            $decoded = json_decode((string) $response->body, true);

            echo new JsonResponse($decoded ?: ['status' => 'error']);
        } catch (\Throwable $e) {
            echo new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 502);
        }

        $this->app->close();
    }

    /**
     * AJAX action to get current cached 7-Card Suite as JSON.
     */
    public function cards(): void {
        $this->checkToken('get');
        $cards = WscCoreHelper::getDashboardCards();
        echo new JsonResponse(['ok' => true, 'cards' => $cards]);
        $this->app->close();
    }

    /**
     * AJAX action to trigger SpeedSentinel 2.0 Lab Audit.
     */
    public function pagespeedAudit(): void {
        $this->checkToken('post');
        $input = $this->input->json;
        $url = trim((string) $input->get('url', \Joomla\CMS\Uri\Uri::root(), 'string'));
        $strategy = in_array($input->get('strategy', 'mobile', 'string'), ['mobile', 'desktop'], true) ? $input->get('strategy', 'mobile', 'string') : 'mobile';

        $result = WscCoreHelper::triggerSpeedSentinelAudit($url, $strategy);
        echo new JsonResponse($result, null, !($result['ok'] ?? false));
        $this->app->close();
    }

    /**
     * AJAX action to trigger Sentinel Domain Health Scan.
     */
    public function sentinelScan(): void {
        $this->checkToken('post');
        $input = $this->input->json;
        $domain = trim((string) $input->get('domain', \Joomla\CMS\Uri\Uri::getInstance()->getHost(), 'string'));

        $result = WscCoreHelper::triggerSentinelScan($domain);
        echo new JsonResponse($result, null, !($result['ok'] ?? false));
        $this->app->close();
    }
}
