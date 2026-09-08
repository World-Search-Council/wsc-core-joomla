<?php

namespace Joomla\Plugin\System\WscAnalytics\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscBotHelper;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscCoreHelper;
use Joomla\Event\SubscriberInterface;

/**
 * System plugin for WSC Analytics: early bot capture and client tracker injection.
 */
class WscAnalytics extends CMSPlugin implements SubscriberInterface {

    protected $autoloadLanguage = true;

    /**
     * Subscribed events in Joomla 4/5.
     */
    public static function getSubscribedEvents(): array {
        return [
            'onAfterInitialise'   => 'onAfterInitialise',
            'onBeforeCompileHead' => 'onBeforeCompileHead',
        ];
    }

    /**
     * Early edge bot detection hook.
     */
    public function onAfterInitialise(): void {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        if (!$params->get('tracking_active', 1)) {
            return;
        }

        $botLevel = $params->get('bot_tracking_level', 'search_ai');
        if ($botLevel === 'off') {
            return;
        }

        $app = Factory::getApplication();
        $ua = $app->input->server->getString('HTTP_USER_AGENT', '');
        if (empty($ua)) {
            return;
        }

        $bot = WscBotHelper::detectBot($ua);
        if (!$bot) {
            return;
        }

        if ($botLevel === 'ai_only' && ($bot['category'] ?? '') !== 'ai') {
            return;
        }

        $uri = Uri::getInstance();
        $path = $uri->getPath() ?: '/';
        $referrer = $app->input->server->getString('HTTP_REFERER', '');
        $ip = $app->input->server->getString('REMOTE_ADDR', '');

        WscCoreHelper::trackEvent('bot_hit', $path, $referrer, [
            'bot_name'     => $bot['name'],
            'bot_category' => $bot['category'],
            'bot_type'     => $bot['type'],
            'ip'           => $ip,
        ]);
    }

    /**
     * Injects client-side tracker into HTML head before rendering.
     */
    public function onBeforeCompileHead(): void {
        $app = Factory::getApplication();
        if ($app->isClient('administrator')) {
            return;
        }

        $params = ComponentHelper::getParams('com_wsc_analytics');
        if (!$params->get('tracking_active', 1)) {
            return;
        }

        // Exclude administrators if configured
        $user = Factory::getUser();
        if ($params->get('exclude_admins', 1) && $user->authorise('core.admin')) {
            return;
        }

        $doc = Factory::getApplication()->getDocument();
        if ($doc->getType() !== 'html') {
            return;
        }

        $config = [
            'endpoint' => Uri::root() . 'index.php?option=com_wsc_analytics&task=api.track',
            'mode' => $params->get('tracking_mode', 'cookieless'),
            'consentKey' => 'wsc_consent',
            'auto' => [
                'downloads' => (bool) $params->get('auto_downloads', 0),
                'media' => (bool) $params->get('auto_media', 0),
                'outbound' => (bool) $params->get('auto_outbound', 0),
                'forms' => (bool) $params->get('auto_forms', 0),
                'scroll' => (bool) $params->get('auto_scroll', 0),
                'timeOnPage' => (bool) $params->get('auto_time_on_page', 0),
            ],
        ];

        $wa = $doc->getWebAssetManager();
        $wa->registerAndUseScript('com_wsc_analytics.tracker', 'media/com_wsc_analytics/js/wsc-tracker.js', [], ['defer' => true]);

        $doc->addScriptDeclaration('window.WSC_CONFIG = ' . json_encode($config) . ';');
    }
}
