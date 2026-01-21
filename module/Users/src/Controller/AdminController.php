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
use Users\Entity\User;
use Users\Form\ChangePasswordForm;
use Users\Form\ResetPasswordForm;
use Users\Form\UserForm;
use Users\Service\UserManager;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $userManager;
    public function __construct(EntityManager $entityManager, UserManager $userManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
    }

    public function indexAction(){

        $page = $this->params()->fromQuery('page', 1);

        $p_name = trim($this->params()->fromQuery('name', ""));
        $p_mobile = trim($this->params()->fromQuery('mobile', ""));
        $p_email = trim($this->params()->fromQuery('email', ""));
        $p_status = trim($this->params()->fromQuery('status', "1"));

        if($p_status =='on') $p_status=1;

        $query = $this->userManager->searchUser($p_name, $p_mobile, $p_email, $p_status);
        //$SQL = $query->getSQL();

        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(Define::ITEM_PAGE_COUNT);
        $paginator->setCurrentPageNumber($page);
        return new ViewModel([
            'paginator'=>$paginator
            ,'queryParam'=>$this->params()->fromQuery()
            ,'searchFormData'=>array("name"=>$p_name,"mobile"=>$p_mobile,"status"=>$p_status,"email"=>$p_email)
        ]);

        /*$users = $this->entityManager->getRepository(User::class)->findAll();
        return new ViewModel(['users'=>$users]);*/
    }

    public function inactiveListAction(){

        $page = $this->params()->fromQuery('page', 1);

        $p_name = trim($this->params()->fromQuery('name', ""));
        $p_mobile = trim($this->params()->fromQuery('mobile', ""));
        $p_email = trim($this->params()->fromQuery('email', ""));

        $query = $this->userManager->searchUser($p_name, $p_mobile, $p_email, 0);

        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(Define::ITEM_PAGE_COUNT);
        $paginator->setCurrentPageNumber($page);
        return new ViewModel([
            'paginator'=>$paginator
            ,'queryParam'=>$this->params()->fromQuery()
            ,'searchFormData'=>array("name"=>$p_name,"mobile"=>$p_mobile,"status"=>$p_status,"email"=>$p_email)
        ]);
    }

    public function activeAction(){
        $userId = $this->params()->fromPost('id',0);

        if($userId<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        $result=array();
        $result['status']=1;

        try{
            $user = $this->userManager->getById($userId);
            $this->userManager->activeUser($user);
            $this->flashMessenger()->addSuccessMessage('Account has been activated successfully');
        }catch (\Exception $e){
            $result['status']=0;
            $message = $e->getMessage();
            $result['message']= $message;
            $this->flashMessenger()->addErrorMessage($message);
        }
        return new JsonModel($result);
    }

    public function addAction(){
        $form = new UserForm('add');
      	include("module/phpmailer/class.smtp.php");
	  	include "module/phpmailer/class.phpmailer.php"; 
	  	include("module/phpmailer/config.php"); 

        if($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            //$data['role']='user';
            $form->setData($data);
            try{
                if($form->isValid()){
                    $data = $form->getData();
                    $user = $this->userManager->addUser($data);
                    $this->flashMessenger()->addSuccessMessage('Thêm thành công user '. $user->getMobile());

                    //send mail notification
                    if(!$data['email']) $mailTo=$this->userInfo->getEmail();
                    else $mailTo = $data['email'];

                    if($user->getRole()=='user')
                        $url=Common::getDomain()."/user-login.html";
                    else{
                        $url=Common::getDomain()."/admin-login.html";
                    }

                    //$emailTemplateManager = new EmailTemplateManager($this->entityManager);
                    //$emailTemplateManager->sendMail($mailTo,null,'register-user-admin',array('name'=>$data['fullname'],'url'=>$url,'username'=>$data['mobile'],'password'=>$data['password']));
                  
                    $context.="<br />Họ tên: ".$data['fullname'];
                    $context.="<br />URL Đăng nhập: ".$url;
                    $context.="<br />Username: ".$data['mobile'];
                    $context.="<br />Password: ".$data['password'];
                    $subject = 'Đăng ký tài khoản thành công trên vnhomestay.com.vn';                    
                  	sendMailer($subject, $context, $nameTo, $mailTo, $diachicc='', $emailFrom, $nameFrom);

                    return $this->redirect()->toRoute('user-admin');
                }
            }catch (\Exception $e){
                $message = $e->getMessage();
                $this->flashMessenger()->addErrorMessage($message);
            }
        }

        return new ViewModel(['form'=>$form]);
    }

    public function editAction(){
        $userID = $this->params()->fromRoute('id',0);
        if($userID<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        $user = $this->userManager->getByID($userID);

        if(!$user){
            $this->getResponse()->setStatusCode('404');
            return;
        }
//        $user->setRole('admin');//set for test
//        $roles = $this->userManager->getRoles($user->getRole());
//        $user->setPrivileges($roles->getPrivileges());
        $form = new UserForm('edit');

        if(!$this->getRequest()->isPost()){
            $data = [
                'mobile'=> $user->getMobile(),
                'email' => $user->getEmail(),
                'fullname'=>$user->getFullname(),
                'role'=>$user->getRole(),
                'birthdate'=>$user->getBirthday()
            ];
            $form->setData($data);
            return new ViewModel(['form'=>$form,'user'=>$user]);
        }

        $data = $this->params()->fromPost();
        $form->setData($data);

        if($form->isValid()){
            $data = $form->getData();
            $this->userManager->editUser($user,$data);
            $this->flashMessenger()->addSuccessMessage('Cập nhật thành công');
            return $this->redirect()->toRoute('user-admin');
        }else{
            $this->flashMessenger()->addErrorMessage($form->getMessages());
        }
        return new ViewModel(['form'=>$form,'user'=>$user]);
    }

    public function deleteAction(){
        $userID = $this->params()->fromRoute('id',0);
        if($userID<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        $user = $this->userManager->getByID($userID);

        if(!$user){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        if($this->getRequest()->isPost()){
            $btn = $this->getRequest()->getPost('delete','No');
            if($btn=="Yes"){
                $this->userManager->removeUser($user);
                $this->flashMessenger()->addSuccessMessage('Xóa thành công user: '. $user->getMobile());
            }
            return $this->redirect()->toRoute('user-admin');
        }
        return new ViewModel(['user'=>$user]);
    }

    public function profileAction(){

        $request = $this->getRequest();

        $form = new UserForm('edit');

        $userInfo = $this->userManager->getByID($this->userInfo->getid());

        $viewModel = new ViewModel(['form' => $form, 'user' => $userInfo]);

        if($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $data['role'] = $userInfo->getRole();
            $data['email'] = $userInfo->getEmail();
            $form->setData($data);
            if($form->isValid()){
                $imageUpload = new ImageUpload('file-avatar', $request->getFiles()->toArray(), 'users/');
                $fileUrl = $imageUpload->upload();
                if($fileUrl){
                    //xoa file cu
                    if($userInfo->getAvatar())
                        if(file_exists(ROOT_PATH.$userInfo->getAvatar())){
                            unlink(ROOT_PATH.$userInfo->getAvatar());
                        }
                    $userInfo->setAvatar('/img/'.$fileUrl);
                }
                $data = $form->getData();
                $this->userManager->editUser($userInfo,$data);
                $this->flashMessenger()->addSuccessMessage('Cập nhật thành công!');
            }
        }else{
            $data = [
                'fullname'=>$userInfo->getFullname(),
                'email'=>$userInfo->getEmail(),
                'birthdate'=>$userInfo->getBirthday()
            ];
            $form->setData($data);
        }
        return $viewModel;
    }

    public function changePasswordAction(){
        $userID = $this->params()->fromRoute('id',0);
        if($userID<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        $user = $this->userManager->getByID($userID);

        if(!$user){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        $form = new ChangePasswordForm();
        if($this->getRequest()->isPost()){

            $data = $this->params()->fromPost();
            $data['old_pw']="empty1@3";
            $form->setData($data);
            if($form->isValid()){
                $data = $form->getData();
                $check = $this->userManager->changePassword($user,$data,false);
                if(!$check){
                    $this->flashMessenger()->addErrorMessage('Mật khẩu cũ chưa đúng, vui lòng kiểm tra lại');
                    return $this->redirect()->toRoute('user-admin',['action'=>'change-password','id'=>$user->getId()]);
                }
                else{
                    $this->flashMessenger()->addSuccessMessage('Mật khẩu đã thay đổi');
                    return $this->redirect()->toRoute('user-admin');
                }
            }else{
                $this->flashMessenger()->addErrorMessage($form->getMessages());
            }
        }
        return new ViewModel(['form'=>$form]);

    }

    /*public function resetPasswordAction(){
        $form = new ResetPasswordForm;
        if($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $form->setData($data);
            if($form->isValid()){
                $user = $this->entityManager->getRepository(User::class)->findOneByEmail($data['email']);
                if($user !==null){
                    $this->userManager->createTokenPasswordReset($user);
                    $this->flashMessenger()->addSuccessMessage('Kiểm tra hộp thư để Reset Password!');

                }
                else{
                    $this->flashMessenger()->addErrorMessage('Email không tồn tại');
                }
                return $this->redirect()->toRoute('resetpassword');
            }
        }
        return new ViewModel(['form'=>$form]);
    }*/
}