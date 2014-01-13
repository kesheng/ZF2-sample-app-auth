<?php
namespace Application\Controller;

use Sglib\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventManagerInterface;


class UserController extends AbstractActionController
{
    protected $authService;
    protected $authStorage;
    protected $userRegistrationForm;
    protected $userLoginForm;



    public function __construct(
        \Application\Service\Auth  $authService,
        \Application\Service\AuthStorage  $authStorage
    )
    {
        $this->authService  = $authService;
        $this->authStorage  = $authStorage;
    }


     /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return void
     */
    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);

        // using $this in the closure, which won’t work. need to pull the controller instance from the event and use it
        $controller = $this;
        $events->attach('dispatch', function ($e) use ($controller) {
            //return $controller->redirect()->toRoute('home');
        }, 100); // execute before executing action logic

        return $this;
    }


    public function loginAction()
    {
        //if already login, redirect to success page
        if ($this->getAuthService()->hasIdentity()){
            return $this->redirect()->toRoute('success');
        }

        $form = $this->getUserLoginForm();

        return array(
            'form'      => $form,
            'messages'  => $this->flashmessenger()->getMessages()
        );
    }


    public function authenticateAction()
    {
        $form       = $this->getForm();
        $redirect = 'login';

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()){
                //check authentication...
                $this->getAuthService()->getAdapter()
                                       ->setIdentityValue($request->getPost('username'))
                                       ->setCredentialValue($request->getPost('password'));

                $result = $this->getAuthService()->authenticate();
                foreach($result->getMessages() as $message)
                {
                    //save message temporary into flashmessenger
                    $this->flashmessenger()->addMessage($message);
                }

                if ($result->isValid()) {
                    $redirect = 'success';
                    //check if it has rememberMe :
                    if ($request->getPost('rememberme') == 1 ) {
                        $this->getSessionStorage()
                             ->setRememberMe(1);
                        //set storage again
                        $this->getAuthService()->setStorage($this->getSessionStorage());
                    }

                    $this->getAuthService()->getStorage()->write($request->getPost('username'));
                }
            }
        }

        return $this->redirect()->toRoute($redirect);
    }


    public function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();

        $this->flashmessenger()->addMessage("You've been logged out");
        return $this->redirect()->toRoute('login');
    }






    public function getAuthService()
    {
        if (empty($this->authservice) === true) {
            $this->authservice = $this->getServiceLocator()->get('Application\Service\Auth');
        }

        return $this->authservice;
    }


    public function getSessionStorage()
    {
        if (empty($this->authStorage) === true) {
            $this->authStorage = $this->getServiceLocator()->get('Application\Service\AuthStorage');
        }

        return $this->authStorage;
    }


    public function getUserLoginForm()
    {
        if (empty($this->userLoginForm) === true) {
            $this->userLoginForm = $this->getServiceLocator()->get('Application\Form\User\LoginForm');
        }

        return $this->userLoginForm;
    }


    public function setUserLoginForm($userLoginForm)
    {
        $this->userLoginForm = $userLoginForm;

        return $this;
    }


    public function getUserRegistrationForm()
    {
        if (empty($this->userRegistrationForm) === true) {
            $this->userRegistrationForm = $this->getServiceLocator()->get('Application\Form\User\RegistrationForm');
        }

        return $this->userRegistrationForm;
    }


    public function setUserRegistrationForm($userRegistrationForm)
    {
        $this->userRegistrationForm = $userRegistrationForm;

        return $this;
    }
}