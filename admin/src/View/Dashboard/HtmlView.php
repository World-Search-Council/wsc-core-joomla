<?php

namespace Joomla\Component\WscAnalytics\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscCoreHelper;

/**
 * HTML View class for WSC Analytics Dashboard.
 */
class HtmlView extends BaseHtmlView {

    public array $cards = [];
    public array $credentials = [];
    public bool $hasData = false;

    public function display($tpl = null) {
        $this->cards = WscCoreHelper::getDashboardCards();
        $this->credentials = WscCoreHelper::getProjectCredentials();

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
        ToolbarHelper::title(Text::_('WSC Analytics & Bot Shield'), 'chart-pie');
        ToolbarHelper::preferences('com_wsc_analytics');
    }

    protected function addAssets(): void {
        $doc = Factory::getApplication()->getDocument();
        $wa = $doc->getWebAssetManager();

        $wa->registerAndUseStyle('com_wsc_analytics.dashboard', 'media/com_wsc_analytics/css/wsc-dashboard.css');
        $wa->registerAndUseScript('com_wsc_analytics.dashboard', 'media/com_wsc_analytics/js/wsc-dashboard.js', [], ['defer' => true]);

        $token = Factory::getApplication()->getSession()->getFormToken();

        // Inject configuration variables
        $doc->addScriptOptions('wscDashboard', [
            'cards' => $this->cards,
            'card1' => $this->cards['card1_traffic'] ?? $this->cards['card1'] ?? null,
            'card2' => $this->cards['card2_insights'] ?? $this->cards['card2'] ?? null,
            'card3' => $this->cards['card3_bots'] ?? $this->cards['card3'] ?? null,
            'cardEngagement' => $this->cards['card_engagement'] ?? null,
            'cardOutbound' => $this->cards['card_outbound'] ?? null,
            'cardEcommerce' => $this->cards['card_ecommerce'] ?? null,
            'domainHealth' => $this->cards['domain_health'] ?? null,
            'pagespeed' => $this->cards['pagespeed'] ?? null,
            'projectId' => $this->credentials['project_id'],
            'token' => $token,
            'refreshUrl' => 'index.php?option=com_wsc_analytics&task=api.refresh&' . $token . '=1',
            'cardsUrl' => 'index.php?option=com_wsc_analytics&task=api.cards&' . $token . '=1',
            'pagespeedUrl' => 'index.php?option=com_wsc_analytics&task=api.pagespeedAudit&' . $token . '=1',
            'sentinelUrl' => 'index.php?option=com_wsc_analytics&task=api.sentinelScan&' . $token . '=1',
            'siteHost' => \Joomla\CMS\Uri\Uri::getInstance()->getHost(),
            'siteUrl' => \Joomla\CMS\Uri\Uri::root(),
        ]);
    }
}
