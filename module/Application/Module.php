<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Authentication\AuthenticationService;

class Module
{
    public function onBootstrap($event)
    {
        $application = $event->getApplication();
        $configuration = $application->getServiceManager()->get('Configuration');


        $event->getApplication()->getServiceManager()->get('translator');

        $eventManager = $event->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchErrorLog'));

        // Session
        $this->onBootstrapSession($configuration['session']);
    }


    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }


    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Zend\Authentication\AuthenticationService' => function($serviceManager) {
                    return $serviceManager->get('doctrine.authenticationservice.orm_default');

                }
            )
        );
    }


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


     /**
     * On bootstrap create session
     *
     * @param array $configuration
     * @return void
     */
    public function onBootstrapSession($configuration)
    {
        $sessionConfig = new \Zend\Session\Config\SessionConfig();
        $sessionConfig->setOptions($configuration);

        $sessionManager = new \Zend\Session\SessionManager($sessionConfig);
        $sessionManager->start();
    }


    public function onDispatchErrorLog(\Zend\Mvc\MvcEvent $event)
    {
        if ($event->getError() === 'error-exception') {
            $exception = $event->getParam('exception');

            $event->getApplication()->getServiceManager()->get('Zend\Log\Logger')->crit($exception);
        }
    }
}