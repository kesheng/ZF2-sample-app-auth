<?php

namespace Application\Service;

use Sglib\Service\AbstractService;
use Application\Entity\User as UserEntity;

class User extends AbstractService
{
    protected $ablumForm;


    public function getAllUsers()
    {
        return $this->getEntityManager()->getRepository('Application\Entity\User')->findAll();
    }


    public function getUserById($id)
    {
        return $this->getEntityManager()->find('Application\Entity\User', $id);
    }


    public function save($data, $id = null)
    {
        if ($id) {
            $saveObject = $this->getUserById($id);
            if (! $saveObject instanceof UserEntity) {
                $error = 'invalid id save';
                return false;
            }
        } else {
            $saveObject = new UserEntity();
        }

        try {
            $saveObject->title  = $data['title'];
            $saveObject->artist = $data['artist'];

            if ($id == null) {
                $saveObject->createdAt = new \DateTime();;

                $this->getEntityManager()->persist($saveObject);
            }

            $this->getEntityManager()->flush();
            return true;
        } catch (\Exception $exception) {
            //var_dump($exception);exit;
            return false;
        }
    }


    public function delete($id)
    {
        $deleteObject = $this->getUserById($id);
        if (! $deleteObject instanceof UserEntity) {
            $error = 'invalid id delete';
            return false;
        }

        try {
            $this->getEntityManager()->remove($deleteObject);
            $this->getEntityManager()->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
