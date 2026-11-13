<?php

namespace Joomla\Component\WscAnalytics\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscCoreHelper;

/**
 * HTML View class for WSC Analytics & Search Health Dashboard (v2.2.3).
 */
class HtmlView extends BaseHtmlView {

    public array $cards = [];
    public array $credentials = [];
    public array $membership = [];
    public array $benchmarks = [];
    public array $featuredPartners = [];
    public array $indexnowLogs = [];
    public bool $llmsTxtEnabled = true;
    public bool $indexnowEnabled = false;
    public bool $hasData = false;

    public function display($tpl = null) {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        $this->cards = WscCoreHelper::getDashboardCards();
        $this->credentials = WscCoreHelper::getProjectCredentials();
        $this->membership = WscCoreHelper::getMembershipState();
        $this->benchmarks = WscCoreHelper::getIndustryBenchmarks(false);
        $this->featuredPartners = WscCoreHelper::getFeaturedPartners();
        $this->indexnowLogs = WscCoreHelper::getIndexNowLogs();
        $this->llmsTxtEnabled = (bool) $params->get('llms_txt_enabled', 1);
        $this->indexnowEnabled = (bool) $params->get('indexnow_enabled', 0);

        $this->hasData = !empty($this->cards['card1'])
            || !empty($this->cards['card2'])
            || !empty($this->cards['card3'])
            || !empty($this->cards['card_engagement'])
            || !empty($this->cards['card_outbound'])
            || !empty($this->cards['card_ecommerce'])
            || !empty($this->cards['domain_health']);

        $this->addToolbar();
        $this->addAssets();

        return parent::display($tpl);
    }

    protected function addToolbar(): void {
        ToolbarHelper::title(Text::_('WSC Analytics & Search Health (v2.2.3)'), 'chart-pie');
        ToolbarHelper::preferences('com_wsc_analytics');
    }

    protected function addAssets(): void {
        $doc = Factory::getApplication()->getDocument();
        $wa = $doc->getWebAssetManager();

        $wa->registerAndUseStyle('com_wsc_analytics.dashboard', 'media/com_wsc_analytics/css/wsc-dashboard.css');
        $wa->registerAndUseScript('com_wsc_analytics.dashboard', 'media/com_wsc_analytics/js/wsc-dashboard.js', [], ['defer' => true]);

        $token = Factory::getApplication()->getSession()->getFormToken();
        $baseAjax = 'index.php?option=com_wsc_analytics&' . $token . '=1&task=';

        $doc->addScriptOptions('wscDashboard', [
            'version'             => WscCoreHelper::VERSION,
            'cards'               => $this->cards,
            'card1'               => $this->cards['card1_traffic'] ?? $this->cards['card1'] ?? null,
            'card2'               => $this->cards['card2_insights'] ?? $this->cards['card2'] ?? null,
            'card3'               => $this->cards['card3_bots'] ?? $this->cards['card3'] ?? null,
            'cardEngagement'      => $this->cards['card_engagement'] ?? null,
            'cardOutbound'        => $this->cards['card_outbound'] ?? null,
            'cardEcommerce'       => $this->cards['card_ecommerce'] ?? null,
            'domainHealth'        => $this->cards['domain_health'] ?? null,
            'pagespeed'           => $this->cards['pagespeed'] ?? null,
            'benchmarks'          => $this->benchmarks,
            'membership'          => $this->membership,
            'projectId'           => $this->credentials['project_id'],
            'hybridEnabled'       => !empty($this->credentials['hybrid_enabled']),
            'token'               => $token,
            'refreshUrl'          => $baseAjax . 'api.refresh',
            'cardsUrl'            => $baseAjax . 'api.cards',
            'pagespeedUrl'        => $baseAjax . 'api.pagespeedAudit',
            'sentinelUrl'         => $baseAjax . 'api.sentinelScan',
            'aiCoverageUrl'       => $baseAjax . 'api.aiCoverage',
            'benchmarksUrl'       => $baseAjax . 'api.benchmarks',
            'classifyVerticalUrl' => $baseAjax . 'api.classifyVertical',
            'licenseVerifyUrl'    => $baseAjax . 'api.licenseVerify',
            'licenseDisconnectUrl'=> $baseAjax . 'api.licenseDisconnect',
            'indexNowUrl'         => $baseAjax . 'api.indexNowSubmit',
            'siteHost'            => Uri::getInstance(Uri::root())->getHost(),
            'siteUrl'             => Uri::root(),
        ]);
    }
}
