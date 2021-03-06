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
 * @ORM\Table(name="user")
 */
class User extends AbstractModel
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $user_id;

    /**
     * @ORM\Column(name="email", type="string", nullable=false, length=255)
     */
    public $email;

    /**
     * @ORM\Column(name="password", type="string", length=255)
     */
    public $password;

    /**
     * @ORM\Column(name="salt", type="string", length=255)
     */
    public $salt;

    /**
     * @ORM\Column(name="username", type="string", length=255)
     */
    public $username;

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



    public function getPassword()
    {
        return $this->password;
    }


    public function getSalt()
    {
        return $this->salt;
    }


    public function setPassword($plaintextPassword)
    {
        $salt = "";
        $rounds = 7;
        $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
        for($i=0; $i < 22; $i++) {
          $salt .= $salt_chars[array_rand($salt_chars)];
        }

        $salt = sprintf('$2a$%02d$', $rounds) . $salt;

        $this->password = $password = crypt('password', $salt);
        return $this->password;
    }


    public static function hashPassword($user, $password)
    {
        return ($user->getPassword() === crypt($password, $user->getSalt()));
    }


    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(
                array(
                    'name' => 'email',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'NotEmpty',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'messages' => array(
                                    'isEmpty' => 'Please enter your email address',
                                )
                            ),
                        ),
                        array(
                            'name' => 'EmailAddress',
                            'options' => array(
                                'messages' => array(
                                    'emailAddressInvalidFormat' => 'Please enter a valid email address in the format name@emailaddress',
                                )
                            )
                        ),
                    ),
                )
            );

            $inputFilter->add(
                array(
                    'name' => 'password',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'NotEmpty',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'messages' => array(
                                    'isEmpty' => 'Please enter your password',
                                )
                            ),
                        ),
                        array(
                            'name'    => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min' => 6,
                                'max' => 128,
                            )
                        ),
                        array(
                            'name' => 'identical',
                            'options' => array('token' => 'password' )
                        ),
                    ),
                )
            );

            $inputFilter->add(array(
                'name' => 'password-confirm',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                ),
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