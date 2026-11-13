<?php

namespace Joomla\Plugin\System\WscAnalytics\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscBotHelper;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscCoreHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * System plugin for WSC Analytics (v2.2.3):
 * - Virtual /llms.txt AI manifest & /{indexnow_key}.txt ownership verification
 * - Early onAfterInitialise Zero-PII AI/RAG/Search bot capture
 * - Opt-in IndexNow instant push on published Joomla article save
 * - Cookieless Vanilla ES6 tracker injection
 */
class WscAnalytics extends CMSPlugin implements SubscriberInterface {

    protected $autoloadLanguage = true;

    /**
     * Subscribed events in Joomla 4 & 5.
     */
    public static function getSubscribedEvents(): array {
        return [
            'onAfterInitialise'   => 'onAfterInitialise',
            'onBeforeCompileHead' => 'onBeforeCompileHead',
            'onContentAfterSave'  => 'onContentAfterSave',
        ];
    }

    /**
     * Virtual route handler (/llms.txt & /{key}.txt) and early edge bot detection hook.
     */
    public function onAfterInitialise(): void {
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_wsc_analytics');

        // 1. Handle virtual /llms.txt and /{indexnow_key}.txt on public site client
        if ($app->isClient('site')) {
            $uri = Uri::getInstance();
            $relPath = '/' . ltrim((string) $uri->getPath(), '/');
            $basePath = rtrim((string) Uri::base(true), '/');
            if ($basePath !== '' && strpos($relPath, $basePath . '/') === 0) {
                $relPath = substr($relPath, strlen($basePath));
            }

            if ($relPath === '/llms.txt' && $params->get('llms_txt_enabled', 1)) {
                header('Content-Type: text/markdown; charset=utf-8');
                header('Cache-Control: public, max-age=3600');
                echo WscCoreHelper::generateLlmsTxt();
                $app->close();
                return;
            }

            if (preg_match('#^/([a-f0-9]{32})\.txt$#i', $relPath, $m)) {
                $creds = WscCoreHelper::getProjectCredentials();
                if (!empty($creds['indexnow_key']) && strcasecmp($m[1], $creds['indexnow_key']) === 0) {
                    header('Content-Type: text/plain; charset=utf-8');
                    header('Cache-Control: public, max-age=86400');
                    echo $creds['indexnow_key'];
                    $app->close();
                    return;
                }
            }
        }

        // 2. Early pre-cache bot sensor (Zero-PII)
        if (!$params->get('tracking_active', 1)) {
            return;
        }

        $botLevel = (string) $params->get('bot_tracking_level', 'search_ai');
        if ($botLevel === 'off') {
            return;
        }

        $ua = $app->input->server->getString('HTTP_USER_AGENT', '');
        if ($ua === '') {
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
        $rawIp = $app->input->server->getString('REMOTE_ADDR', '');
        $sessionHash = WscCoreHelper::hashClientIp($rawIp, $ua);

        WscCoreHelper::trackEvent('bot_hit', $path, $referrer, [
            'bot_name'     => $bot['name'],
            'bot_category' => $bot['category'],
            'bot_type'     => $bot['type'],
            'session_hash' => $sessionHash,
        ]);
    }

    /**
     * Opt-in IndexNow instant notification when a published Joomla article is saved.
     */
    public function onContentAfterSave(Event $event): void {
        $params = ComponentHelper::getParams('com_wsc_analytics');
        if (!$params->get('indexnow_enabled', 0)) {
            return;
        }

        $args = $event->getArguments();
        $context = (string) ($args[0] ?? '');
        $article = $args[1] ?? null;

        if ($context !== 'com_content.article' || !is_object($article)) {
            return;
        }

        $state = (int) ($article->state ?? 0);
        $access = (int) ($article->access ?? 1);
        if ($state !== 1 || $access !== 1 || empty($article->id)) {
            return;
        }

        $rootUrl = rtrim(Uri::root(), '/');
        $slug = (int) $article->id . (!empty($article->alias) ? '-' . $article->alias : '');
        $articleUrl = $rootUrl . '/index.php?option=com_content&view=article&id=' . rawurlencode($slug);

        WscCoreHelper::submitIndexNow([$articleUrl, $rootUrl . '/'], 'auto');
    }

    /**
     * Injects client-side cookieless tracker into HTML head before rendering.
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

        $user = Factory::getUser();
        if ($params->get('exclude_admins', 1) && $user->authorise('core.admin')) {
            return;
        }

        $doc = $app->getDocument();
        if ($doc->getType() !== 'html') {
            return;
        }

        $config = [
            'endpoint'   => Uri::root() . 'index.php?option=com_wsc_analytics&task=api.track',
            'mode'       => $params->get('tracking_mode', 'cookieless'),
            'consentKey' => 'wsc_consent',
            'version'    => WscCoreHelper::VERSION,
            'auto'       => [
                'downloads'  => (bool) $params->get('auto_downloads', 0),
                'media'      => (bool) $params->get('auto_media', 0),
                'outbound'   => (bool) $params->get('auto_outbound', 0),
                'forms'      => (bool) $params->get('auto_forms', 0),
                'scroll'     => (bool) $params->get('auto_scroll', 0),
                'timeOnPage' => (bool) $params->get('auto_time_on_page', 0),
            ],
        ];

        $wa = $doc->getWebAssetManager();
        $wa->registerAndUseScript('com_wsc_analytics.tracker', 'media/com_wsc_analytics/js/wsc-tracker.js', [], ['defer' => true]);

        $doc->addScriptDeclaration('window.WSC_CONFIG = ' . json_encode($config, JSON_UNESCAPED_SLASHES) . ';');
    }
}
