<?php
namespace Application;

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Album',
                        'action'     => 'index',
                    ),
                ),
            ),
            'album' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/album[/][:action][/:id]',
                    'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Album',
                        'action'     => 'index',
                    ),
                ),
            ),
            'user' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/user',
                    'defaults' => array(
                        'controller'    => 'Application\Controller\User',
                        'action'        => 'login',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'login' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/login',
                            'defaults' => array(
                                'controller' => 'Application\Controller\User',
                                'action'     => 'login',
                            ),
                        ),
                    ),
                    'process' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    'success' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/success',
                            'defaults' => array(
                                'controller' => 'Application\Controller\User',
                                'action'     => 'loginsuccess',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            //'Application\Controller\Album' => 'Application\Controller\AlbumController',
        ),
        'factories' => array(
            'Application\Controller\Album' => function(\Zend\Mvc\Controller\ControllerManager $controllerManager) {
                $serviceLocator = $controllerManager->getServiceLocator();

                $controller = new \Application\Controller\AlbumController(
                    $serviceLocator->get('Application\Service\Album')
                );

                return $controller;
            },
            'Application\Controller\User' => function(\Zend\Mvc\Controller\ControllerManager $controllerManager) {
                $serviceLocator = $controllerManager->getServiceLocator();

                $controller = new \Application\Controller\UserController(
                    $serviceLocator->get('Zend\Authentication\AuthenticationService'),
                    $serviceLocator->get('Application\Service\User'),
                    $serviceLocator->get('Application\Service\AuthStorage')
                );

                return $controller;
            },
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            // Log
            'Zend\Log\Logger' => function($sm){
                $logger = new \Zend\Log\Logger;
                $writer = new \Zend\Log\Writer\Stream('./data/log/'.date('Y-m-d').'-error.log');

                $logger->addWriter($writer);

                return $logger;
            },
            // Services
            'Application\Service\Album' => function($serviceManager) {
                $service = new \Application\Service\Album();
                $service->setApplicationForm(
                    $serviceManager->get('Application\Form\AlbumForm')
                );

                return $service;
            },
            // Forms
            'Application\Form\AlbumForm' => function ($sm) {
                $inputFilter = $sm->get('Application\Entity\Album')->getInputFilter();
                $form = new \Application\Form\AlbumForm($sm);
                $form->setInputFilter($inputFilter);

                return $form;
            },
            'Application\Form\User\Login' => function ($sm) {
                $inputFilter = $sm->get('Application\Entity\User')->getInputFilter();
                $form = new \Application\Form\User\Login($sm);
                $form->setInputFilter($inputFilter);

                return $form;
            },
            'Application\Form\User\Registration' => function ($sm) {
                $inputFilter = $sm->get('Application\Entity\User')->getInputFilter();
                $form = new \Application\Form\User\Registration($sm);
                $form->setInputFilter($inputFilter);

                return $form;
            },
        ),
        'invokables' => array(
            // Entities
            'Application\Entity\Album' => 'Application\Entity\Album',
            'Application\Entity\User' => 'Application\Entity\User',
            // Service
            'Application\Service\User' => 'Application\Service\User',
            'Application\Service\AuthStorage' => 'Application\Service\AuthStorage',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
        ),
        'authentication' => array(
            'orm_default' => array(
                'objectManager' => 'Doctrine\ORM\EntityManager',
                'identityClass' => 'Application\Entity\User',
                'identityProperty' => 'email',
                'credentialProperty' => 'password',
                'credentialCallable' => 'Application\Entity\User::hashPassword'
            ),
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);