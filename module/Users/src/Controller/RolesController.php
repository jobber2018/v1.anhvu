<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/15/19 10:29 AM
 *
 */

namespace Users\Controller;


use Doctrine\ORM\EntityManager;
use EmailTemplate\Service\EmailTemplateManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeAdminController;
use Users\Entity\Privileges;
use Users\Entity\User;
use Users\Form\ChangePasswordForm;
use Users\Form\ResetPasswordForm;
use Users\Form\UserForm;
use Users\Service\RolesManager;
use Users\Service\UserManager;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class RolesController extends SuldeAdminController
{
    private $entityManager;
    private $rolesManager;

    public function __construct(EntityManager $entityManager, RolesManager $rolesManager)
    {
        $this->entityManager = $entityManager;
        $this->rolesManager = $rolesManager;
    }

    public function indexAction(){
        $roles = $this->rolesManager->getAll();
        return new ViewModel(['roles'=>$roles]);
    }

    public function privilegesAction(){
        $roleId = $this->params()->fromRoute('id',0);
        if($roleId<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        //get all roles
        $roles = $this->rolesManager->getAll();
        //get current roles
        $role = $this->rolesManager->getByID($roleId);
        //get all privileges in system
        $allPrivileges = $this->entityManager->getRepository(Privileges::class)->findAll();

        //sort role group for tree
        $roleGroup = array();
        foreach ($allPrivileges as $privileges){
            $key =substr($privileges->getController(),0,strpos($privileges->getController(),'\\'));
            $roleGroup[$key][]=$privileges;
        }

        //get user by role
        $user = $this->rolesManager->getUser($role->getCode());
        $role->setUser($user);
        return new ViewModel(['roles'=>$roles,'role'=>$role,'roleGroup'=>$roleGroup]);
    }
    public function updatePrivilegesAction(){
        $roleId = $this->params()->fromRoute('id',0);
        if($roleId<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();

            $role = $this->rolesManager->getByID($roleId);

            //remove old privilege
            foreach ($role->getPrivileges() as $key=>$privileges){
                $role->removePrivileges($privileges);
            }

            //add new privileges
            if(count($data['privileges'])) {
                foreach ($data['privileges'] as $key=>$privilegesId) {
                    $privileges = $this->entityManager->getRepository(Privileges::class)->find($privilegesId);
                    $role->addPrivileges($privileges);
                    $this->entityManager->persist($privileges);
                }
            }
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage('Updated successfully.');
            return $this->redirect()->toRoute('roles-admin',['action'=>'privileges','id'=>$roleId]);
        }
        $this->flashMessenger()->addErrorMessage('There was an error.');
    }
    public function addUserAction(){
        $roleId = $this->params()->fromRoute('id',0);
        if($roleId<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        if($this->getRequest()->isPost()){
            $userId = $this->getRequest()->getPost('user_id');
            $role = $this->rolesManager->getByID($roleId);
            $userManager = new UserManager($this->entityManager);
            $user = $userManager->getByID($userId);
            $user->setRole($role->getCode());
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage('Updated successfully.');
            return $this->redirect()->toRoute('roles-admin',['action'=>'privileges','id'=>$roleId]);
        }
    }
    public function deleteUserAction(){
        $roleId = $this->params()->fromRoute('id',0);
        if($roleId<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        if($this->getRequest()->isPost()){
            $userId = $this->getRequest()->getPost('userDelete_id');

            $userManager = new UserManager($this->entityManager);
            $user = $userManager->getByID($userId);

            $user->setRole(Define::DEFAULT_USER_ROLE);
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage('Delete successfully.');
            return $this->redirect()->toRoute('roles-admin',['action'=>'privileges','id'=>$roleId]);
        }
    }
}