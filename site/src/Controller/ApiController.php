<?php

namespace Joomla\Component\WscAnalytics\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscCoreHelper;

/**
 * Public REST Controller for client telemetry and Gateway Webhook in Joomla.
 */
class ApiController extends BaseController {

    /**
     * Telemetry ingestion endpoint for JavaScript tracker.
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

        $ip = $this->app->input->server->getString('REMOTE_ADDR', '');
        $ua = $this->app->input->server->getString('HTTP_USER_AGENT', '');
        $count = 0;

        foreach ($data['events'] as $item) {
            if (empty($item['e'])) {
                continue;
            }

            $eventType = (string) $item['e'];
            $urlPath   = (string) ($item['p'] ?? '/');
            $referrer  = (string) ($item['r'] ?? '');
            $meta      = (isset($item['m']) && is_array($item['m'])) ? $item['m'] : [];

            $meta['ip'] = $ip;
            $meta['user_agent'] = $ua;

            if (WscCoreHelper::trackEvent($eventType, $urlPath, $referrer, $meta)) {
                $count++;
            }
        }

        echo new JsonResponse(['ok' => true, 'queued' => $count]);
        $this->app->close();
    }

    /**
     * Webhook receiver for aggregated 3-Card reports from WSC Gateway.
     */
    public function webhook(): void {
        $rawContent = file_get_contents('php://input');
        $signature = $this->app->input->server->getString('HTTP_X_WSC_SIG', '');

        $creds = WscCoreHelper::getProjectCredentials();
        $expectedSig = hash_hmac('sha256', $rawContent, $creds['local_key']);

        if (!hash_equals($expectedSig, $signature)) {
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
}
