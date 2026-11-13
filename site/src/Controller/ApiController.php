<?php

namespace Joomla\Component\WscAnalytics\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscCoreHelper;

/**
 * Public REST Controller for Zero-PII client telemetry, Gateway Webhooks, /llms.txt & IndexNow key verification.
 */
class ApiController extends BaseController {

    /**
     * Cookieless Zero-PII telemetry ingestion endpoint for JavaScript tracker.
     */
    public function track(): void {
        $rawContent = file_get_contents('php://input');
        if (empty($rawContent)) {
            echo new JsonResponse(['ok' => false, 'error' => 'empty_payload'], null, true);
            $this->app->close();
        }

        $data = json_decode($rawContent, true);
        if (!is_array($data) || empty($data['events']) || !is_array($data['events'])) {
            echo new JsonResponse(['ok' => false, 'error' => 'invalid_format'], null, true);
            $this->app->close();
        }

        $rawIp = $this->app->input->server->getString('REMOTE_ADDR', '');
        $ua = $this->app->input->server->getString('HTTP_USER_AGENT', '');
        $sessionHash = WscCoreHelper::hashClientIp($rawIp, $ua);
        $count = 0;

        foreach ($data['events'] as $item) {
            if (empty($item['e'])) {
                continue;
            }

            $eventType = (string) $item['e'];
            $urlPath   = (string) ($item['p'] ?? '/');
            $referrer  = (string) ($item['r'] ?? '');
            $meta      = (isset($item['m']) && is_array($item['m'])) ? $item['m'] : [];

            unset($meta['ip']);
            $meta['session_hash'] = $sessionHash;
            $meta['user_agent'] = substr($ua, 0, 255);

            if (WscCoreHelper::trackEvent($eventType, $urlPath, $referrer, $meta)) {
                $count++;
            }
        }

        echo new JsonResponse(['ok' => true, 'queued' => $count]);
        $this->app->close();
    }

    /**
     * Webhook receiver for aggregated 7-Card Bento reports from WSC Gateway.
     */
    public function webhook(): void {
        $rawContent = (string) file_get_contents('php://input');
        $signature = $this->app->input->server->getString('HTTP_X_WSC_SIG', '');

        $creds = WscCoreHelper::getProjectCredentials();
        $secret = !empty($creds['api_secret']) ? $creds['api_secret'] : $creds['local_key'];
        $expectedSig = hash_hmac('sha256', $rawContent, $secret);

        if ($signature === '' || !hash_equals($expectedSig, $signature)) {
            $this->app->setHeader('status', '403', true);
            echo new JsonResponse(['ok' => false, 'error' => 'invalid_signature'], null, true);
            $this->app->close();
        }

        $data = json_decode($rawContent, true);
        if (!is_array($data)) {
            $this->app->setHeader('status', '400', true);
            echo new JsonResponse(['ok' => false, 'error' => 'invalid_json'], null, true);
            $this->app->close();
        }

        $saved = WscCoreHelper::saveWebhookReport($data);
        echo new JsonResponse(['ok' => $saved]);
        $this->app->close();
    }

    /**
     * Direct controller fallback for dynamic /llms.txt AI Manifest.
     */
    public function llmsTxt(): void {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        if (!$params->get('llms_txt_enabled', 1)) {
            $this->app->setHeader('status', '404', true);
            echo '404 Not Found';
            $this->app->close();
        }

        header('Content-Type: text/markdown; charset=utf-8');
        header('X-Robots-Tag: noindex');
        echo WscCoreHelper::generateLlmsTxt();
        $this->app->close();
    }
}
