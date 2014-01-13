<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Sglib\Model\AbstractModel;

/**
 * An user.
 *
 * @ORM\Entity
 * @ORM\Table(name="album")
 */
class User extends AbstractModel
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="email", type="string", length=255)
     */
    public $email;

    /**
     * @ORM\Column(name="password", type="string", length=255)
     */
    public $password;

    /**
     * @ORM\Column(name="rememberme", type="boolean", nullable=false, options={"default" = 0})
     */
    public $rememberme;

    /**
     * @ORM\Column(name="date_updated", type="datetime", nullable=false)
     */
    protected $updatedAt;

    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    protected $createdAt;


    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name' => 'email',
                'type' => 'Zend\Form\Element\Email',
                'required' => true,
                'filters' => array(
                    'name' => 'StripTags',
                    'name' => 'StringTrim',
                ),
                'validators' => array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 5,
                        'max'      => 255,
                        'messages' => array(
                            \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid'
                        )
                    )
                ),
            ));

            $inputFilter->add(array(
                'name' => 'password',
                'type' => 'Zend\Form\Element\Password',
                'required' => true,
                'filters' => array('name' => 'StringTrim'),
                'validators' => array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                        'max'      => 128,
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name' => 'password-confirm',
                'required' => true,
                'filters' => array('name' => 'StringTrim'),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array( 'min' => 6 )
                    ),
                    array(
                        'name' => 'identical',
                        'options' => array('token' => 'password' )
                    ),
                ),
            ));

            /*$inputFilter->add(array(
                'name'     => 'username',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            ));*/

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}