<?php

namespace Joomla\Component\WscAnalytics\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscCoreHelper;

/**
 * Controller for Admin AJAX sync, diagnostics, AI Coverage, Benchmarks, WSC Membership & IndexNow (v2.2.3).
 */
class ApiController extends BaseController {

    /**
     * AJAX action to manually trigger micro-batch sync.
     */
    public function refresh(): void {
        $this->checkToken('get');

        $synced = WscCoreHelper::processBatchExport();

        echo new JsonResponse([
            'ok'            => true,
            'synced_events' => $synced,
            'last_sync'     => gmdate('Y-m-d H:i:s') . ' UTC',
        ]);

        $this->app->close();
    }

    /**
     * AJAX action to query AI Coverage URL Inspector (Gateway + SQLite fallback).
     */
    public function aiCoverage(): void {
        $this->checkToken('get');

        $input = $this->input->json;
        $queryUrl = trim((string) $input->get('query', '', 'string'));
        $timeframe = trim((string) $input->get('timeframe', '30d', 'string'));

        if ($queryUrl === '') {
            echo new JsonResponse(['ok' => false, 'error' => 'query_required'], null, true);
            $this->app->close();
        }

        $result = WscCoreHelper::queryAiCoverage($queryUrl, $timeframe);
        echo new JsonResponse($result, null, empty($result['ok']));
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
        $this->checkToken('get');
        $input = $this->input->json;
        $url = trim((string) $input->get('url', Uri::root(), 'string'));
        $rawStrategy = (string) $input->get('strategy', 'mobile', 'string');
        $strategy = in_array($rawStrategy, ['mobile', 'desktop'], true) ? $rawStrategy : 'mobile';

        $result = WscCoreHelper::triggerSpeedSentinelAudit($url, $strategy);
        echo new JsonResponse($result, null, empty($result['ok']));
        $this->app->close();
    }

    /**
     * AJAX action to trigger Sentinel Domain Health Scan.
     */
    public function sentinelScan(): void {
        $this->checkToken('get');
        $input = $this->input->json;
        $domain = trim((string) $input->get('domain', Uri::getInstance(Uri::root())->getHost(), 'string'));

        $result = WscCoreHelper::triggerSentinelScan($domain);
        echo new JsonResponse($result, null, empty($result['ok']));
        $this->app->close();
    }

    /**
     * AJAX action to refresh dynamic Industry Benchmarks.
     */
    public function benchmarks(): void {
        $this->checkToken('get');
        $result = WscCoreHelper::getIndustryBenchmarks(true);
        echo new JsonResponse($result);
        $this->app->close();
    }

    /**
     * AJAX action to auto-classify Industry Vertical and refresh Benchmarks.
     */
    public function classifyVertical(): void {
        $this->checkToken('get');
        $result = WscCoreHelper::classifyVertical();
        echo new JsonResponse($result, null, empty($result['ok']));
        $this->app->close();
    }

    /**
     * AJAX action to verify WSC Member / Agency credential against www.worldsearchcouncil.org.
     */
    public function licenseVerify(): void {
        $this->checkToken('get');
        $input = $this->input->json;
        $key = trim((string) $input->get('license_key', '', 'string'));
        $coBrand = null;

        $cbName = trim((string) $input->get('agency_name', '', 'string'));
        $cbUrl  = trim((string) $input->get('agency_url', '', 'string'));
        $cbMail = trim((string) $input->get('agency_email', '', 'string'));
        $cbLogo = trim((string) $input->get('agency_logo', '', 'string'));
        if ($cbName !== '' || $cbUrl !== '' || $cbMail !== '' || $cbLogo !== '') {
            $coBrand = [
                'name'  => $cbName,
                'url'   => $cbUrl,
                'email' => $cbMail,
                'logo'  => $cbLogo,
            ];
        }

        $result = WscCoreHelper::verifyLicense($key, $coBrand);
        echo new JsonResponse($result, null, empty($result['ok']));
        $this->app->close();
    }

    /**
     * AJAX action to disconnect active WSC Member / Agency credential.
     */
    public function licenseDisconnect(): void {
        $this->checkToken('get');
        $result = WscCoreHelper::disconnectLicense();
        echo new JsonResponse($result);
        $this->app->close();
    }

    /**
     * AJAX action to push a URL manually to IndexNow.
     */
    public function indexNowSubmit(): void {
        $this->checkToken('get');
        $input = $this->input->json;
        $url = trim((string) $input->get('url', Uri::root(), 'string'));
        $result = WscCoreHelper::submitIndexNow([$url], 'manual');
        echo new JsonResponse($result, null, empty($result['ok']));
        $this->app->close();
    }
}
