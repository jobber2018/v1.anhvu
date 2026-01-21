<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Supplier\Controller;

use Supplier\Entity\Supplier;
use Supplier\Form\SupplierForm;
use Supplier\Service\SupplierManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeAdminController;
use Users\Entity\User;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $supplierManager;

    public function __construct(EntityManager $entityManager, SupplierManager $supplierManager)
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
    }


    /**
     * @return ViewModel
     */
    public function indexAction(){
        $supplier = $this->supplierManager->getAll();
        return new ViewModel([
            'supplier'=>$supplier
        ]);
    }

    public function addAction(){
        $form =new SupplierForm("add");

        $request = $this->getRequest();
        if($request->isPost()){
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($data);

            if($form->isValid()){
                try{
                    $this->userInfo;
                    $userId= $this->userInfo->getId();
                    $user = $this->entityManager->getRepository(User::class)->find($userId);

                    $supplier = new Supplier();
                    $supplier->setName($data["name"]);
                    $supplier->setMobile($data["mobile"]);
                    $supplier->setAddress($data["address"]);
                    $supplier->setUser($user);
                    $supplier->setCreatedDate(new \DateTime());

                    $this->entityManager->persist($supplier);
                    $this->entityManager->flush();

                    $this->flashMessenger()->addSuccessMessage('Đã thêm mới NCC '.$data["name"]);
                    return $this->redirect()->toRoute('supplier-admin');
                }catch (\Exception $e){
                    $message = $e->getMessage();
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        }
        return new ViewModel(['form'=>$form]);
    }

    public function editAction(){
        $supplierId = $this->params()->fromRoute('id',0);
        $form =new SupplierForm("edit");

        $supplier = $this->supplierManager->getById($supplierId);

        $request = $this->getRequest();
        if($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($data);

            if ($form->isValid()) {
                try {

                    $supplier->setName($data["name"]);
                    $supplier->setMobile($data["mobile"]);
                    $supplier->setAddress($data["address"]);

                    $this->entityManager->persist($supplier);
                    $this->entityManager->flush();

                    $this->flashMessenger()->addSuccessMessage('Đã sửa thông tin NCC ' . $data["name"]);
                    return $this->redirect()->toRoute('supplier-admin');
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        }else{
            $data=[
                    "name"=>$supplier->getName(),
                    "mobile"=>$supplier->getMobile(),
                    "address"=>$supplier->getAddress()
                ];
            $form->setData($data);
        }
        return new ViewModel(['form'=>$form]);
    }

}