<?php
namespace Application\Form\User;

class Login extends \Zend\Form\Form
{
    public function __construct($name = null)
    {
        parent::__construct();

        $this->setAttribute('method', 'post');


        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'email',
            'options' => array(
                'label' => 'Email',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Password',
            'name' => 'password',
            'options' => array(
                'label' => 'Password',
            ),
        ));

        $this->add(
            array(
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'rememberme',
                'options' => array(
                    'label' => 'Remember Me',
                    'checked_value' => 1,
                    'unchecked_value' => 0
                ),
            )
        );

        $this->add(array(
            'type' => 'Zend\Form\Element\Button',
            'name' => 'submit',
            'options' => array(
                'label' => 'Login',
            ),
            'attributes' => array(
                'type' => 'submit',
            ),
        ));
    }
}