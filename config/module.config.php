<?php
/**
 * module.config.php - User Config
 *
 * Main Config File for Application Module
 *
 * @category Config
 * @package Application
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\POS;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'touchscreen' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/basestation/touchscreen',
                    'defaults' => [
                        'controller' => Controller\BackendController::class,
                        'action'     => 'touchscreen',
                    ],
                ],
            ],
            'backend-worktime' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/basestation/worktime',
                    'defaults' => [
                        'controller' => Controller\BackendController::class,
                        'action'     => 'worktime',
                    ],
                ],
            ],
            'backend-cashregister' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/basestation/cashregister',
                    'defaults' => [
                        'controller' => Controller\BackendController::class,
                        'action'     => 'cashregister',
                    ],
                ],
            ],
            'pos-backend' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/pos/backend[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\BackendController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'layout/touchscreen'           => __DIR__ . '/../view/layout/touchscreen.phtml',
        ],
        'template_path_stack' => [
            'pos' => __DIR__ . '/../view',
        ],
    ],

    'plc_x_user_plugins' => [

    ],

    # Translator
    'translator' => [
        'locale' => 'de_DE',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
];
