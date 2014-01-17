<?php

namespace Application\Form\User;

class Registration extends \Zend\Form\Form
{
    public function __construct($name = null)
    {
        parent::__construct();

        $this->setAttribute('method', 'post');


        $this->add(array(
            'type' => 'Zend\Form\Element\Email',
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

        $this->add(array(
            'type' => 'Zend\Form\Element\Password',
            'name' => 'password-confirm',
            'options' => array(
                'label' => 'Password (confirm)',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Button',
            'name' => 'submit',
            'options' => array(
                'label' => 'Register',
            ),
            'attributes' => array(
                'type' => 'submit',
            ),
        ));
    }
}