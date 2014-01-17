<?php
namespace Application\Controller;

use Sglib\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventManagerInterface;


class UserController extends AbstractActionController
{
    protected $authService;
    protected $userService;
    protected $pcService;
    protected $authStorage;
    protected $userRegistrationForm;
    protected $userLoginForm;



    public function __construct(
        \Zend\Authentication\AuthenticationService  $authService,
        \Application\Service\User  $userService,
        \Application\Service\AuthStorage  $authStorage
    )
    {
        $this->authService  = $authService;
        $this->userService  = $userService;
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


    public function indexAction()
    {
        $salt = "";
        $rounds = 7;
        $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
        for($i=0; $i < 22; $i++) {
          $salt .= $salt_chars[array_rand($salt_chars)];
        }

        $salt = sprintf('$2a$%02d$', $rounds) . $salt;
        var_dump($salt);
        $password = crypt('password', $salt);

        var_dump($password);
        return false;
    }


    public function loginAction()
    {
        //if already login, redirect to success page
        if ($this->getAuthService()->hasIdentity()){
            return $this->redirect()->toRoute('home');
        }

        $form = $this->getUserLoginForm();

        return array(
            'form'      => $form,
            'messages'  => $this->flashmessenger()->getMessages()
        );
    }


    public function loginsuccessAction()
    {
        if (! $this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('user/login');
        }

        return new ViewModel();
    }


    public function authenticateAction()
    {
        $form = $this->getUserLoginForm();
        $inputFilter = $form->getInputFilter();
        $inputFilter->remove('password-confirm');

        $redirect = 'user/login';

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()){
                //check authentication...
                $this->getAuthService()->getAdapter()
                                       ->setIdentityValue($request->getPost('email'))
                                       ->setCredentialValue($request->getPost('password'));

                $result = $this->getAuthService()->authenticate();
                foreach($result->getMessages() as $message) {
                    //save message temporary into flashmessenger
                    $this->flashmessenger()->addMessage($message);
                }

                if ($result->isValid()) {
                    $redirect = 'home';
                    //check if it has rememberMe :
                    if ($request->getPost('rememberme') == 1 ) {
                        $this->getSessionStorage()
                             ->setRememberMe(1);
                        //set storage again
                        $this->getAuthService()->setStorage($this->getSessionStorage());
                    }

                    $this->getAuthService()->getStorage()->write($result->getIdentity());
                }
            } else {
                foreach ($form->getMessages() as $messages) {
                    foreach ($messages as $message) {
                        $this->flashmessenger()->addMessage($message);
                    }
                }
            }
        }

        //var_dump($form->getMessages());exit;
        return $this->redirect()->toRoute($redirect);
    }


    public function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();

        $this->flashmessenger()->addMessage("You've been logged out");
        return $this->redirect()->toRoute('user/login');
    }




    public function getAuthService()
    {
        if (empty($this->authService) === true) {
            $this->authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }

        return $this->authService;
    }


    public function getUserService()
    {
        if (empty($this->userService) === true) {
            $this->userService = $this->getServiceLocator()->get('Application\Service\User');
        }

        return $this->userService;
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
            $this->userLoginForm = $this->getServiceLocator()->get('Application\Form\User\Login');
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
            $this->userRegistrationForm = $this->getServiceLocator()->get('Application\Form\User\Registration');
        }

        return $this->userRegistrationForm;
    }


    public function setUserRegistrationForm($userRegistrationForm)
    {
        $this->userRegistrationForm = $userRegistrationForm;

        return $this;
    }
}