<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-14
 * Time: 10:54
 */

namespace Users\Controller;


use Doctrine\ORM\EntityManager;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeUserController;
use Users\Form\ChangePasswordForm;
use Users\Form\UserForm;
use Users\Service\UserManager;
use Zend\Filter\File\Rename;
use Zend\View\Model\ViewModel;

class UserController extends SuldeUserController
{

    private $entityManager;
    private $userManager;
    public function __construct(EntityManager $entityManager, UserManager $userManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
    }

    public function profileAction(){
        $userID = $this->userInfo->getId();

        if($userID<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        $userInfo = $this->userManager->getByID($this->userInfo->getid());

        $form = new UserForm('edit');

        $viewModel = new ViewModel(['form' => $form, 'userInfo' => $userInfo]);

        if(!$this->getRequest()->isPost()){
            $data = [
                'email' => $userInfo->getEmail(),
                'fullname'=>$userInfo->getFullname(),
                'birthdate'=>$userInfo->getBirthday()
            ];
            $form->setData($data);
            return $viewModel;
        }

        $data = $this->params()->fromPost();

        $request = $this->getRequest();

        $data['role']=$userInfo->getRole();

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


            $viewModel->setVariable('message','Cập nhật thành công');
        }

        return $viewModel;
    }

    public function changePasswordAction(){
        $userID=$this->userInfo->getId();
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
            $form->setData($data);
            if($form->isValid()){
                $data = $form->getData();
                $check = $this->userManager->changePassword($user,$data);
                if(!$check){
                    $this->flashMessenger()->addErrorMessage('Mật khẩu cũ chưa đúng, vui lòng kiểm tra lại');
                    return $this->redirect()->toRoute('user-front',['action'=>'change-password']);
                }
                else{
                    $this->flashMessenger()->addSuccessMessage('Mật khẩu đã thay đổi');
                    return $this->redirect()->toRoute('user-front',['action'=>'profile']);
                }
            }
        }
        return new ViewModel(['form'=>$form]);

    }
}