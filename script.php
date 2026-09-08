<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\Component\WscAnalytics\Administrator\Helper\WscCoreHelper;

/**
 * Installation and update script for WSC Analytics Joomla Extension.
 */
class Com_Wsc_AnalyticsInstallerScript {

    /**
     * Post-installation routine.
     */
    public function postflight(string $type, InstallerAdapter $parent): void {
        if ($type === 'install' || $type === 'update') {
            // 1. Automatically enable bundled system plugin
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('wsc_analytics'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
            $db->setQuery($query);
            $db->execute();

            // 2. Initialize schemas and credentials
            if (class_exists(WscCoreHelper::class)) {
                WscCoreHelper::initSchemas();
                WscCoreHelper::getProjectCredentials();
            }
        }
    }
}
