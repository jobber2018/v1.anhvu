<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-15
 * Time: 15:55
 */

namespace Werehouse\Controller;


use Admin\Service\AdminManager;
use Doctrine\ORM\EntityManager;
use Exception;
use Product\Entity\ProductActivity;
use Product\Service\ProductManager;
use Sell\Entity\SellOrder;
use Sell\Entity\SellOrderActivity;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeAdminController;
use Supplier\Service\SupplierManager;
use Users\Entity\User;
use Werehouse\Entity\Werehouse;
use Werehouse\Entity\WerehouseOrder;
use Werehouse\Form\WerehouseForm;
use Werehouse\Service\WerehouseManager;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class StaffController extends SuldeAdminController
{
    private $entityManager;
    private $werehouseManager;

    public function __construct(EntityManager $entityManager, WerehouseManager $werehouseManager)
    {
        $this->entityManager = $entityManager;
        $this->werehouseManager = $werehouseManager;
    }

    public function indexAction(){
        $allOrder = $this->werehouseManager->getAllOrder();
        return new ViewModel(['allOrder'=>$allOrder]);
    }

    public function choiceSupplierAction(){
        $supplierManager = new SupplierManager($this->entityManager);
        $supplier = $supplierManager->getAll();
        return new ViewModel(['supplier'=>$supplier]);
    }

    public function createDraftAction(){
        $supplierID = $this->params()->fromRoute('id',0);

        $supplierManager = new SupplierManager($this->entityManager);
        $supplier = $supplierManager->getById($supplierID);

        $productManager = new ProductManager($this->entityManager);
        $productList = $productManager->getAll();
        return new ViewModel(['supplier'=>$supplier,'productList'=>$productList]);
    }
    public function detailAction(){
        $werehouseId = $this->params()->fromRoute('id',0);
        $werehouseOrder = $this->werehouseManager->getOrderById($werehouseId);

        $form=new WerehouseForm();

        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $request = $this->getRequest();
                $imageUpload = new ImageUpload('imageFile', $request->getFiles()->toArray(), 'invoice/werehouse/');
                $fileUrl = $imageUpload->upload();
                if($fileUrl){
                    $werehouseOrder->setInvoice('/img/'.$fileUrl);
                    $this->entityManager->flush();
                    $this->flashMessenger()->addSuccessMessage('Đã upload hoá đơn nhập hàng NCC: '.$werehouseOrder->getSupplier()->getName(). ' ['.$werehouseOrder->getCodeOrder().']');
                }else{
                    $this->flashMessenger()->addErrorMessage('Có lỗi upload hoá đơn nhập hàng NCC: '.$werehouseOrder->getSupplier()->getName(). ' ['.$werehouseOrder->getCodeOrder().']');
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $this->flashMessenger()->addErrorMessage($message);
            }
            return $this->redirect()->toRoute('werehouse-admin');
        }
        return new ViewModel([
            'werehouseOrder'=>$werehouseOrder,
            'supplier'=>$werehouseOrder->getSupplier(),
            'werehouse'=>$werehouseOrder->getWerehouse(),
            'form'=>$form
        ]);
    }

    public function editDraftAction(){
        $orderID = $this->params()->fromRoute('id',0);

        $werehouseOrder = $this->werehouseManager->getOrderById($orderID);

        $request = $this->getRequest();

        //edit draft
        if($request->isPost()){
            try{
                if($werehouseOrder->getStatus()==1)
                    throw new Exception("Đơn đã nhập kho. không thể lưu nháp!");

                $data = $request->getPost("pro");

                $totalOrder=0;

                foreach ($data as $key=>$item){
                    $obj=json_decode(json_encode($item));
                    $box=$obj->box;
                    $price=$obj->price;
                    $productId = $obj->id;
                    $werehouseId = $obj->werehouse;

                    $productManager = new ProductManager($this->entityManager);
                    $product = $productManager->getById($productId);

                    $totalPrice=$price*$box;
                    $totalOrder = $totalOrder+ $totalPrice;

                    //warehouse da co
                    if($werehouseId){
                        //$werehouse = $this->werehouseManager->getById($werehouseId);
                        $werehouse = $werehouseOrder->getWerehouseById($werehouseId);
                    }else{
                        $werehouse = new Werehouse();
                    }

                    $werehouse->setPrice($price);
                    $werehouse->setQuantity($box);
                    $werehouse->setProduct($product);

                    $werehouse->setWerehouseOrder($werehouseOrder);
                    $werehouseOrder->addWerehouse($werehouse);

                }

                $werehouseOrder->setStatus(0);
                $werehouseOrder->setTotalPrice($totalOrder);

                $this->entityManager->persist($werehouseOrder);
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage('Lưu nháp thành công!');
                $result['status']=1;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);

        }else{
            $productManager = new ProductManager($this->entityManager);
            $productList = $productManager->getAll();
        }
        return new ViewModel([
            'supplier'=>$werehouseOrder->getSupplier()
            ,'productList'=>$productList
            ,'werehouse'=>$werehouseOrder->getWerehouse()
            ,'werehouseOrder'=>$werehouseOrder
        ]);
    }
}