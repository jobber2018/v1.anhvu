<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/17/19 3:20 PM
 *
 */


namespace Users\Service;


use Users\Entity\Roles;
use Users\Entity\User;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Zend\Session\SessionManager;

class AuthAdapter implements AdapterInterface
{
    private $entityManager;
    private $mobile;
    private $email;
    private $password;

    public function __construct($entityManager){
        $this->entityManager = $entityManager;
    }

    public function setMobile($mobile){
        $this->mobile = $mobile;
    }
    public function setEmail($email){
        $this->email = $email;
    }
    public function setPassword($password){
        $this->password = $password;
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     *     If authentication cannot be performed
     */
    public function authenticate()
    {

        if($this->mobile){
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(array("mobile"=>$this->mobile));
        }else{
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(array("email"=>$this->email));
        }

        if(!$user){
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ['The mobile number is not correct.']);
        }else{
            $bcrypt = new Bcrypt();

            $userPassword = $this->password; //pw do người dừng nhập
            $passwordHash = $user->getPassword(); // pw đã lưu trong db

            if($bcrypt->verify($userPassword,$passwordHash)){

                $roles = $this->entityManager->getRepository(Roles::class)->findOneBy(array("code"=>$user->getRole()));
                $privileges = array();
                if($roles){
                    foreach ($roles->getPrivileges() as $key =>$privilege){
                        $privileges[] = array(
                            "controller"=>$privilege->getController()
                        ,"action"=>$privilege->getAction()
                        ,'allow'=>$privilege->getAllow()
                        ,'name'=>$privilege->getName()
                        );
                    }
                }
                $user->setPrivileges($privileges);

                $_SESSION['userInfo']=$user;

                return new Result(Result::SUCCESS,
                    $user,
                    ['Logged in successfully.']
                );
            }
            else{
                return new Result(
                    Result::FAILURE_CREDENTIAL_INVALID ,
                    null,
                    ['Wrong login information, incorrect password.']);
            }
        }

    }
}