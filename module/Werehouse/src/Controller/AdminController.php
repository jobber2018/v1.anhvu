<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Werehouse\Controller;

use Exception;
use Google\Service\AdExchangeBuyer\Product;
use Product\Entity\ProductActivity;
use Product\Entity\ProductInventory;
use Product\Service\ProductManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\ImageUpload;
use Supplier\Entity\Supplier;
use Supplier\Service\SupplierManager;
use Users\Entity\User;
use Werehouse\Entity\Werehouse;
use Werehouse\Entity\WerehouseCheck;
use Werehouse\Entity\WerehouseOrder;
use Werehouse\Entity\WerehouseOrderInvoice;
use Werehouse\Entity\WerehouseSheet;
use Werehouse\Form\WerehouseForm;
use Werehouse\Service\WerehouseManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeAdminController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $werehouseManager;

    public function __construct(EntityManager $entityManager, WerehouseManager $werehouseManager)
    {
        $this->entityManager = $entityManager;
        $this->werehouseManager = $werehouseManager;
    }

    /**
     * @return ViewModel
     */
    public function indexAction(){
        $allOrder = $this->werehouseManager->getAllOrder();
        return new ViewModel(['allOrder'=>$allOrder]);
    }
    public function addAction(){
        $supplierID = $this->params()->fromRoute('id',0);

        $supplierManager = new SupplierManager($this->entityManager);
        $supplier = $supplierManager->getById($supplierID);

        $request = $this->getRequest();

        if($request->isPost()){
            try{
                $data = $request->getPost("pro");
                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                $werehouseOrder = new WerehouseOrder();
                $totalOrder=0;

                $productManager = new ProductManager($this->entityManager);

                foreach ($data as $key=>$item){
                    $obj=json_decode(json_encode($item));
                    $boxQty=$obj->box;
                    $priceBox=$obj->price;
                    $productId = $obj->id;

                    $product = $productManager->getById($productId);

                    $boxUnit=$product->getBoxUnit();
                    $productQty=$boxQty*$boxUnit;//sl sp nhap

                    $totalPrice = $priceBox*$boxQty;
                    $totalOrder = $totalOrder+ $totalPrice;

                    $inventory = $product->getInventory();
                    $product->setInventory($inventory+$productQty);

                    $averagePrice = $product->getAveragePrice();
                    if($averagePrice>0)
                        $averagePriceNew = ($averagePrice*$inventory + $totalPrice)/($inventory+$productQty);
                    else $averagePriceNew=$priceBox/$boxUnit;

                    $product->setAveragePrice(round($averagePriceNew));

                    $werehouse = new Werehouse();
                    $werehouse->setPrice($priceBox);
                    $werehouse->setQuantity($boxQty);
                    $werehouse->setProduct($product);
                    $werehouse->setBoxUnit($product->getBoxUnit());

                    $werehouse->setWerehouseOrder($werehouseOrder);
                    $werehouseOrder->addWerehouse($werehouse);

                    //tao moi ban ghi product activity
                    $productActivity = new ProductActivity();
                    $productActivity->setUser($user);
                    $productActivity->setProduct($product);
                    $productActivity->setNote('Nhập kho');
                    $productActivity->setCreatedDate(new \DateTime());
//                    $productActivity->setUrl("/admin/werehouse/view/"..".html");
                    $productActivity->setChange($productQty);
                    $this->entityManager->persist($productActivity);
                }

                $werehouseOrder->setUser($user);
                $werehouseOrder->setTotalPrice($totalOrder);
                $werehouseOrder->setSupplier($supplier);
                $werehouseOrder->setStatus(1);
                $werehouseOrder->setPay(0);
                $werehouseOrder->setCreatedDate(new \DateTime());
                $this->entityManager->persist($werehouseOrder);
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage('Nhập kho thành công!');
                $result['status']=1;
                //return $this->redirect()->toRoute('werehouse-admin');
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);

        }else{
            $productManager = new ProductManager($this->entityManager);
            $productList = $productManager->getAll();
        }
        return new ViewModel(['supplier'=>$supplier,'productList'=>$productList]);
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
    /**
     * Xoa san pham draft khoi don hang
     * @return JsonModel
     */
    public function deleteWerehouseAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $werehouseId = $request->getPost("id");
                $werehouse = $this->werehouseManager->getById($werehouseId);

                if($werehouse->getWerehouseOrder()->getStatus()==1)
                    throw new Exception("Đơn đã nhập kho. không thể xoá!");
                $this->entityManager->remove($werehouse);
                $this->entityManager->flush();
                $result['msg']="Đã xoá sản phẩm: ".$werehouse->getProduct()->getName();
                $result['status']=1;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result['status']=0;
            $result['msg']="Không thể thực hiện xoá dữ liệu!";
        }
        return new JsonModel($result);
    }
    public function saveDraftAction(){
        $supplierID = $this->params()->fromRoute('id',0);

        $request = $this->getRequest();

        //save draft
        if($request->isPost()){
            try{
                $data = $request->getPost("pro");

//                $this->userInfo;
                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                $werehouseOrder = new WerehouseOrder();
                $totalOrder=0;

                foreach ($data as $key=>$item){
                    $obj=json_decode(json_encode($item));
                    $box=$obj->box;
                    $price=$obj->price;
                    $productId = $obj->id;

                    $productManager = new ProductManager($this->entityManager);
                    $product = $productManager->getById($productId);

                    $totalPrice=$price*$box;
                    $totalOrder = $totalOrder+ $totalPrice;

                    $werehouse = new Werehouse();
                    $werehouse->setPrice($price);
                    $werehouse->setQuantity($box);
                    $werehouse->setProduct($product);

                    $werehouse->setWerehouseOrder($werehouseOrder);
                    $werehouseOrder->addWerehouse($werehouse);
                }

                $werehouseOrder->setUser($user);
                $werehouseOrder->setStatus(0);
                $werehouseOrder->setTotalPrice($totalOrder);
                $supplier=$this->entityManager->getRepository(Supplier::class)->find($supplierID);
                $werehouseOrder->setSupplier($supplier);
                $werehouseOrder->setCreatedDate(new \DateTime());
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
            $result['msg']='Cannot access!';
            $result['status']=0;
            return new JsonModel($result);
        }
    }
    public function draftToOrderAction(){
        $orderID = $this->params()->fromRoute('id',0);

        $werehouseOrder = $this->werehouseManager->getOrderById($orderID);

        $request = $this->getRequest();

        if($request->isPost()){
            try{
                if($werehouseOrder->getStatus()==1)
                    throw new Exception('Không thể thực hiện do đơn hàng đã nhập kho!');

                $data = $request->getPost("pro");
                $totalOrder=0;

                $productManager = new ProductManager($this->entityManager);

                foreach ($data as $key=>$item){
                    $obj=json_decode(json_encode($item));
                    $boxQty=$obj->box;
                    $priceBox=$obj->price;
                    $productId = $obj->id;
                    $werehouseId = $obj->werehouse;

                    $product = $productManager->getById($productId);

                    $boxUnit=$product->getBoxUnit();

                    $productQty=$boxQty*$boxUnit;//sl sp nhap

                    $totalPrice = $priceBox*$boxQty;
                    $totalOrder = $totalOrder+ $totalPrice;

                    $inventory = $product->getInventory();
                    $product->setInventory($inventory+$productQty);

                    $averagePrice = $product->getAveragePrice();
                    if($averagePrice>0)
                        $averagePriceNew = ($averagePrice*$inventory + $totalPrice)/($inventory+$productQty);
                    else $averagePriceNew=$priceBox/$boxUnit;

                    $product->setAveragePrice(round($averagePriceNew));

                    //warehouse da co
                    if($werehouseId){
//                        $werehouse = $this->werehouseManager->getById($werehouseId);
                        $werehouse = $werehouseOrder->getWerehouseById($werehouseId);
                    }else{
                        $werehouse = new Werehouse();
                    }
                    $werehouse->setPrice($priceBox);
                    $werehouse->setQuantity($boxQty);
                    $werehouse->setProduct($product);
                    $werehouse->setBoxUnit($product->getBoxUnit());

                    $werehouse->setWerehouseOrder($werehouseOrder);
                    $werehouseOrder->addWerehouse($werehouse);

                    //tao moi ban ghi product activity
                    $productActivity = new ProductActivity();
                    $productActivity->setUser($werehouseOrder->getUser());
                    $productActivity->setProduct($product);
                    $productActivity->setNote('Nhập kho');
                    $productActivity->setCreatedDate(new \DateTime());
                    $productActivity->setUrl("/admin/werehouse/view/".$werehouseOrder->getId().".html");
                    $productActivity->setChange($productQty);
                    $this->entityManager->persist($productActivity);
                }

                $werehouseOrder->setTotalPrice($totalOrder);
                $werehouseOrder->setCreatedDate(new \DateTime());
                $werehouseOrder->setStatus(1);
                $werehouseOrder->setPay(0);
                $this->entityManager->persist($werehouseOrder);
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage('Nhập kho thành công!');
                $result['status']=1;
                //return $this->redirect()->toRoute('werehouse-admin');
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);
        }
        $result['status']=0;
        return new JsonModel($result);
    }
    public function viewAction(){
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

    public function choiceSupplierAction(){
        $supplierManager = new SupplierManager($this->entityManager);
        $supplier = $supplierManager->getAll();
        return new ViewModel(['supplier'=>$supplier]);
    }

    /**
     * @return ViewModel
     */
    public function productNormAction(){
        $productManager = new ProductManager($this->entityManager);
        $productNorm = $productManager->getProductNorm();

        return new ViewModel(['product'=>$productNorm]);
    }
    public function exportNormAction(){
        $view = new ViewModel();
        $view->setTerminal(true);
        $filename='product-norm-'.date('Ymd').'.csv';
        $delimiter=',';

        $fields=array('Mã sản phẩm,Tên sản phẩm,Số lượng nhập,Tồn kho,Quy cách,Giá nhập gần nhất, NCC');
        $content=implode($delimiter,$fields).PHP_EOL;

        $productManager = new ProductManager($this->entityManager);
        $productNorm = $productManager->getProductNorm();
        foreach ($productNorm as $productItem){
            $nameSupplier='';
            $lastInputPrice=$productItem->getLastInputPrice()->getPrice();
            if($lastInputPrice)
                $nameSupplier=$productItem->getLastInputPrice()->getWerehouseOrder()->getSupplier()->getName();

            $lineData=array($productItem->getCode()
                ,$productItem->getName().'|'.$productItem->getWeight()
                ,($productItem->getNormInput()/$productItem->getBoxUnit())
                ,$productItem->getInventory()
                ,$productItem->getBoxUnit()
                ,$lastInputPrice
                ,$nameSupplier
            );
            $content.=implode($delimiter,$lineData).PHP_EOL;
        }

        $response        = $this->getResponse();
        $responseHeaders = $response
            ->getHeaders()
            ->addHeaders([
                'Content-Disposition'       => 'attachment;filename=' . $filename,
                'Content-Type'              => 'text/csv charset=UTF-8',
                'Content-Transfer-Encoding' => 'binary',
                'Expires'                   => 0,
                'Cache-Control'             => 'must-revalidate',
                'Pragma'                    => 'public',
            ]);
        $response->setHeaders($responseHeaders);

        $response->setContent($content);

        return $response;
    }

    public function inventoryCheckAction(){
        $sheetId = $this->params()->fromRoute('id',0);
        $werehouseSheet = $this->werehouseManager->getSheetById($sheetId);
        $productManager = new ProductManager($this->entityManager);

        $request = $this->getRequest();

        if($request->isPost()){
            try{
                $proId = $request->getPost("pid");
                $qty=$request->getPost("qty");

//                if($werehouseSheet->getStatus()==1)
//                    throw new Exception("Phiếu kiểm kho đã được duyệt. Không thể thêm sản phẩm vào phiếu kiểm kho!");

                $product = $productManager->getById($proId);

                if($werehouseSheet->isProductAlreadyExist($proId))
                    throw new Exception('Sản phẩm '.$product->getName().' đã có trong phiếu kiểm kho!');

                $werehouseCheck = new WerehouseCheck();
                $werehouseCheck->setProduct($product);
                $werehouseCheck->setWerehouseSheet($werehouseSheet);
                $werehouseCheck->setActualInventory($qty);
                $werehouseCheck->setBookInventory($product->getInventory());
                $werehouseCheck->setCreatedDate(new \DateTime());
                $werehouseCheck->setIsUpdate(0);
                $this->entityManager->persist($werehouseCheck);
                $this->entityManager->flush();

                if(is_null($product->getImg()))
                    $img= '<img src="/img/icons/no-image-icon.png" class="pull-left" width="40px">';
                else
                    $img= '<img src="'.$product->getImg().'" style ="max-width:40px; max-height:30px">';


                $result['data']=array(
                    'id'=>$werehouseCheck->getId(),
                    'img'=>$img,
                    'code'=>$product->getCode(),
                    'pack_code'=>$product->getPackCode(),
                    'name'=>$product->getName(),
                    'actual_inventory'=>$qty,
                    'book_inventory'=>$product->getInventory(),
                    'created_date'=>Common::formatDateTime(new \DateTime()));
                $result['msg']='Đã thêm sản phẩm '. $product->getName(). ' vào phiếu kiểm kho!';
                $result['status']=1;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }

            return new JsonModel($result);
        }else{
            //danh sach san pham
            $productList = $productManager->getAll();
            return new ViewModel([
                'productList'=>$productList,
                'werehouseSheet'=>$werehouseSheet
            ]);
        }
    }
    public function inventorySheetAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            $userId= $this->userInfo->getId();
            $werehouseSheet = $this->werehouseManager->getSheetByIdAndDateNow($userId);
//            echo $werehouseSheet->getId();
            if(count($werehouseSheet)==0){
                $werehouseSheet = new WerehouseSheet();

                $user = $this->entityManager->getRepository(User::class)->find($userId);

                $werehouseSheet->setUser($user);
                $werehouseSheet->setCreatedDate(new \DateTime());
                $this->entityManager->persist($werehouseSheet);
                $this->entityManager->flush();
            }else{
                $werehouseSheet=$werehouseSheet[0];
            }

            return $this->redirect()->toRoute('werehouse-admin',['action'=>'inventory-check','id'=>$werehouseSheet->getId()]);
        }else{
            $werehouseSheet = $this->werehouseManager->getAllSheet();
            return new ViewModel([
                'userInfo'=>$this->userInfo,
                'werehouseSheet'=>$werehouseSheet
            ]);
        }
    }

    public function deleteCheckAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $cId = $request->getPost("id");
                $werehouseCheck = $this->werehouseManager->getCheckById($cId);

                if($werehouseCheck->getIsUpdate()==1)
                    throw new Exception("Số liệu đã cập nhật. Không thể xoá sản phẩm khỏi phiếu kiểm kho!");

                $this->entityManager->remove($werehouseCheck);
                $this->entityManager->flush();
                $result['msg']='Đã xoá sản phẩm '. $werehouseCheck->getProduct()->getName(). ' ra khỏi phiếu kiểm kho!';
                $result['status']=1;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    public function updateCheckAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $cId = $request->getPost("id");
                $werehouseCheck = $this->werehouseManager->getCheckById($cId);

                if($werehouseCheck->getIsUpdate()==1)
                    throw new Exception("Số liệu đã cập nhật!");

                $product = $werehouseCheck->getProduct();
                $user = $werehouseCheck->getWerehouseSheet()->getUser();

                //tao moi ban ghi inventory
                $productInventory = new ProductInventory();
                $productInventory->setProduct($product);
                $productInventory->setAfterChange($werehouseCheck->getActualInventory());
                $productInventory->setBeforeChange($werehouseCheck->getBookInventory());
                $productInventory->setCreatedDate(new \DateTime());
                $productInventory->setNote('Kiểm kho');
                $productInventory->setUser($user);
                $this->entityManager->persist($productInventory);
//                $this->entityManager->flush();

                //tao moi ban ghi product activity
                $productActivity = new ProductActivity();
                $productActivity->setUser($user);
                $productActivity->setProduct($product);
                $productActivity->setNote('Kiểm kho');
                $productActivity->setCreatedDate(new \DateTime());
                $productActivity->setUrl("/admin/werehouse/inventory-check/".$werehouseCheck->getWerehouseSheet()->getId().".html");
                $change = $werehouseCheck->getActualInventory()-$werehouseCheck->getBookInventory();
                $productActivity->setChange($change);
                $this->entityManager->persist($productActivity);

                //set ton kho moi cho san pham
                $product->setInventory($werehouseCheck->getActualInventory());
                $product->setInventoryCheck(new \DateTime());
                $this->entityManager->persist($product);
//                $this->entityManager->flush();

                //chuyen trang thai da cap nhat du lieu
                $werehouseCheck->setIsUpdate(1);
                $this->entityManager->persist($werehouseCheck);
                $this->entityManager->flush();

                $result['msg']='Đã cập nhật tồn kho cho sản phẩm '. $werehouseCheck->getProduct()->getName();
                $result['status']=1;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    public function updateAllCheckAction(){
        $request = $this->getRequest();
        $sId = $this->params()->fromRoute('id',0);;
        if($request->isPost()){
            try{
                $werehouseSheet = $this->werehouseManager->getSheetById($sId);

//                $userId= $this->userInfo->getId();
//                $user = $this->entityManager->getRepository(User::class)->find($userId);

                foreach ($werehouseSheet->getWerehouseCheck() as $werehouseCheck){
//                    $werehouseCheck = $checkItem->getWerehouseCheck();
                    //chi update nhung item chua update
                    if($werehouseCheck->getIsUpdate()==0){
                        $product = $werehouseCheck->getProduct();
                        $user = $werehouseSheet->getUser();
                        //tao moi ban ghi inventory
                        $productInventory = new ProductInventory();
                        $productInventory->setProduct($product);
                        $productInventory->setAfterChange($werehouseCheck->getActualInventory());
                        $productInventory->setBeforeChange($werehouseCheck->getBookInventory());
                        $productInventory->setCreatedDate(new \DateTime());
                        $productInventory->setNote('Kiểm kho');
                        $productInventory->setUser($user);
                        $this->entityManager->persist($productInventory);

                        //tao moi ban ghi product activity
                        $productActivity = new ProductActivity();
                        $productActivity->setUser($user);
                        $productActivity->setProduct($product);
                        $productActivity->setNote('Kiểm kho');
                        $productActivity->setCreatedDate(new \DateTime());
                        $productActivity->setUrl("/admin/werehouse/inventory-check/".$werehouseSheet->getId().".html");
                        $change = $werehouseCheck->getActualInventory()-$werehouseCheck->getBookInventory();
                        $productActivity->setChange($change);
                        $this->entityManager->persist($productActivity);
//                        $this->entityManager->flush();

                        $product->setInventory($werehouseCheck->getActualInventory());
                        $product->setInventoryCheck(new \DateTime());
                        $this->entityManager->persist($product);
//                        $this->entityManager->flush();

                        $werehouseCheck->setIsUpdate(1);
                        $this->entityManager->persist($werehouseCheck);
                        $this->entityManager->flush();
                    }
                }

                $result['msg']='Đã cập nhật tồn kho cho tất cả sản phẩm trong phiếu kiểm kho!';
                $result['status']=1;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    public function payConfirmAction(){
        $orderID = $this->params()->fromRoute('id',0);
        $request = $this->getRequest();

        if($request->isPost()){
            try{
                $money = $request->getPost("m");
                $werehouseOrder = $this->werehouseManager->getOrderById($orderID);

                $werehouseOrder->setPay($money);
                $werehouseOrder->setPayDate(new \DateTime());
                $this->entityManager->persist($werehouseOrder);
                $this->entityManager->flush();
                $result['status']=1;
                $msg = 'Đã xác nhận thanh toán ' . Common::formatMoney($money) . 'đ cho đơn hàng ' . $werehouseOrder->getCodeOrder();
                $result['msg']= $msg;
                $this->flashMessenger()->addSuccessMessage($msg);
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);

        }else{
            $result['msg']='Cannot access!';
            $result['status']=0;
            return new JsonModel($result);
        }
    }

    public function deleteDraftOrderAction(){
        $orderID = $this->params()->fromRoute('id',0);
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $werehouseOrder = $this->werehouseManager->getOrderById($orderID);

                if($werehouseOrder->getStatus()==1)
                    throw new Exception("Đơn đã nhập kho. không thể xoá");

                $this->entityManager->remove($werehouseOrder);
                $msg = 'Đã xoá đơn nháp [' . $werehouseOrder->getCodeOrder() . ']: NCC ' . $werehouseOrder->getSupplier()->getName();
                $this->entityManager->flush();
                $result['msg']= $msg;
                $result['status']=1;
                $this->flashMessenger()->addSuccessMessage($msg);
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    /**
     * back product to supplier
     * @return JsonModel
     */
    public function backSupplierAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $werehouseId = $request->getPost("id");
                $werehouse = $this->werehouseManager->getById($werehouseId);
                $werehouseOrder = $werehouse->getWerehouseOrder();
                $product = $werehouse->getProduct();

                if($werehouseOrder->getStatus()!=1)
                    throw new Exception("Chỉ những đơn đã nhập kho mới có thể trả lại NCC!");

                //cap nhat lai ton kho
                $qty=$werehouse->getQuantity();
                $boxUnit = $werehouse->getBoxUnit();

                $totalProduct = $qty*$boxUnit;
                $inventory=$product->getInventory();

                $inventoryAfterChange = $inventory - $totalProduct;

                if($inventoryAfterChange <0)
                    throw new Exception('Tồn kho không đủ để trả lại NCC!');

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                //luu lich su thay doi ton kho
                /*$productInventory = new ProductInventory();
                $productInventory->setProduct($product);
                $productInventory->setAfterChange($inventoryAfterChange);
                $productInventory->setBeforeChange($product->getInventory());
                $productInventory->setCreatedDate(new \DateTime());
                $productInventory->setNote('Trả NCC');

                $productInventory->setUser($user);
                $this->entityManager->persist($productInventory);*/
//                $this->entityManager->flush();

                //tao moi ban ghi product activity
                $productActivity = new ProductActivity();
                $productActivity->setUser($user);
                $productActivity->setProduct($product);
                $productActivity->setNote('Trả NCC: '.$werehouseOrder->getSupplier()->getName());
                $productActivity->setCreatedDate(new \DateTime());
                $productActivity->setUrl("/admin/werehouse/view/".$werehouseOrder->getId().".html");
                $change = 0-$totalProduct;
                $productActivity->setChange($change);
                $this->entityManager->persist($productActivity);

                //cap nhat ton kho
                $product->setInventory($inventoryAfterChange);
                $this->entityManager->persist($product);
//                $this->entityManager->flush();

                //xoa san pham khoi don nhap
                $werehouseOrder->removeWerehouse($werehouse);
                $this->entityManager->remove($werehouse);
//
                //cap nhat total order
                $werehouseOrder->setTotalPrice($werehouseOrder->getTotalPrice());
                $this->entityManager->persist($werehouseOrder);
                $this->entityManager->flush();

                $msg = "Đã trả lại NCC sản phẩm: " . $product->getName();
                $result['msg']= $msg;
                $result['status']=1;
                $this->flashMessenger()->addSuccessMessage($msg);
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result['status']=0;
            $result['msg']="Không thể thực hiện xoá dữ liệu!";
        }
        return new JsonModel($result);
    }

    /**
     * return ajax
     */
    public function uploadInvoiceAction(){

        $werehouseOrderId = $this->params()->fromRoute('id',0);

        if($werehouseOrderId<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }
        $werehouseOrder = $this->werehouseManager->getOrderById($werehouseOrderId);
        $request = $this->getRequest();

        if($request->isPost()) {
            $imageUpload = new ImageUpload('file', $request->getFiles()->toArray(), 'invoice/werehouse/');
            $fileUrl = $imageUpload->upload();
            if($fileUrl){
                $fileUrl = '/img/'.$fileUrl;
                $werehouseOrderInvoice = new WerehouseOrderInvoice();
                $werehouseOrderInvoice->setPath($fileUrl);
                $werehouseOrderInvoice->setCreatedDate(new \DateTime());
                $werehouseOrderInvoice->setWerehouseOrder($werehouseOrder);

                //user upload by
                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);
                $werehouseOrderInvoice->setUser($user);

                $werehouseOrder->addInvoice($werehouseOrderInvoice);
                $this->entityManager->persist($werehouseOrder);
                $this->entityManager->flush();
                $result=[
                    'status' => '1',
                    'message'=>'',
                    'id'=>$werehouseOrderInvoice->getId()
                ];

            }else
                $result=[
                    'status' => '0',
                    'message'=>'Không thể upload file!'
                ];
        }else{
            $result=[
                'status' => '0',
                'message'=>'Phương thức gửi file không đúng!'
            ];
        }
        return new JsonModel($result);
    }


    public function deleteInvoiceAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $werehouseOrderInvoiceId = $request->getPost("id");
            if($werehouseOrderInvoiceId){

                $woi = $this->entityManager->getRepository(WerehouseOrderInvoice::class)->find($werehouseOrderInvoiceId);

                //remove file on server
                if (file_exists(ROOT_PATH.$woi->getPath())) {
                    unlink(ROOT_PATH.$woi->getPath());
                }

                //remove file in data
                $this->entityManager->remove($woi);
                $this->entityManager->flush();

                $result=[
                    'status' => '1',
                    'message'=>''
                ];

            }else
                $result=[
                    'status' => '0',
                    'message'=>'Không thể upload file!'
                ];
        }else{
            $result=[
                'status' => '0',
                'message'=>'Phương thức gửi file không đúng!'
            ];
        }
        return new JsonModel($result);
    }
}