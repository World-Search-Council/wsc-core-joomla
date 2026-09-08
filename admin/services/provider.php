<?php

defined('_JEXEC') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\Component;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\WscAnalytics\Administrator\Extension\WscAnalyticsComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void {
        $container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\WscAnalytics'));
        $container->registerServiceProvider(new Component('\\Joomla\\Component\\WscAnalytics'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new WscAnalyticsComponent($container->get(MVCFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                return $component;
            }
        );
    }
};
