<?php

namespace Joomla\Component\WscAnalytics\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Default Display Controller for WSC Analytics Admin.
 */
class DisplayController extends BaseController {

    protected $default_view = 'dashboard';

    public function display($cachable = false, $urlparams = []) {
        return parent::display($cachable, $urlparams);
    }
}
