<?php

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Plugin\System\WscAnalytics\Extension\WscAnalytics;

// Proxy class for classic plugin loaders if needed
if (!class_exists('PlgSystemWscAnalytics')) {
    class PlgSystemWscAnalytics extends WscAnalytics {
    }
}
