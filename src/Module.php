<?php
/**
 * Module.php - Module Class
 *
 * Module Class File for User Module
 *
 * @category Config
 * @package User
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\POS;

use Application\Controller\CoreController;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\EventManager\EventInterface as Event;
use Laminas\Mvc\MvcEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Session\Config\StandardConfig;
use Laminas\Session\SessionManager;
use Laminas\Session\Container;
use Laminas\I18n\Translator\TranslatorInterface;

class Module
{
    /**
     * Module Version
     *
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * Load module config file
     *
     * @return array
     * @since 1.0.0
     */
    public function getConfig() : array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Load Models
     *
     * @since 1.0.0
     */
    public function getServiceConfig() : array
    {
        return [
            'factories' => [
            ],
        ];
    }

    /**
     * Load Controllers
     */
    public function getControllerConfig() : array
    {
        return [
            'factories' => [
                Controller\BackendController::class => function ($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    $aPluginTbls = [];
                    $aPluginTbls['user'] = $container->get(\OnePlace\User\Model\UserTable::class);

                    return new Controller\BackendController(
                        $oDbAdapter,
                        false,
                        $container,
                        $aPluginTbls
                    );
                },
                Controller\ApiController::class => function ($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    $aPluginTbls = [];
                    $aPluginTbls['user'] = $container->get(\OnePlace\User\Model\UserTable::class);

                    return new Controller\ApiController(
                        $oDbAdapter,
                        false,
                        $container,
                        $aPluginTbls
                    );
                },
                Controller\WorktimeController::class => function ($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    $aPluginTbls = [];
                    $aPluginTbls['user'] = $container->get(\OnePlace\User\Model\UserTable::class);

                    return new Controller\WorktimeController(
                        $oDbAdapter,
                        false,
                        $container,
                        $aPluginTbls
                    );
                },
                Controller\CheckoutController::class => function ($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    $aPluginTbls = [];
                    $aPluginTbls['user'] = $container->get(\OnePlace\User\Model\UserTable::class);

                    return new Controller\CheckoutController(
                        $oDbAdapter,
                        false,
                        $container,
                        $aPluginTbls
                    );
                },
            ],
        ];
    }
}
