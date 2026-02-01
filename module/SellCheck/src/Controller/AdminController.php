<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace SellCheck\Controller;

use Admin\Service\AdminManager;
use Doctrine\ORM\EntityManager;
use Product\Entity\ProductActivity;
use Sell\Entity\SellOrderActivity;
use Sell\Service\SellManager;
use SellCheck\Service\SellCheckManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\SuldeAdminController;
use Users\Entity\User;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $sellCheckManager;

    public function __construct(EntityManager $entityManager, SellCheckManager $sellCheckManager)
    {
        $this->entityManager = $entityManager;
        $this->sellCheckManager = $sellCheckManager;
    }

    /**
     * @return ViewModel
     */
    public function dashboardAction(){

        return new ViewModel();
    }
    /**
     * @return ViewModel
     */
    public function listAction(){
        $request = $this->getRequest();


        $sellManager = new SellManager($this->entityManager);
        $sellOrders = $sellManager->getSellOrderViaStatus(array(Define::_ORDER_WAITING_PACKING_STATUS, Define::_ORDER_PACKING_STATUS, Define::_ORDER_PACKED_STATUS));

        $orders=array();
        foreach($sellOrders as $sellOrder){
            $tmp["order_id"]=$sellOrder->getId();
            $tmp['order_code']=$sellOrder->getOrderCode();
            $tmp['total_paid_order']=$sellOrder->getTotalAmountToPaid();
            $tmp['status_name']=ConfigManager::getOrderStatus()[$sellOrder->getStatus()];
            $tmp['status_id']=$sellOrder->getStatus();
            $tmp['customer_name']=$sellOrder->getGrocery()->getGroceryName();
            $tmp['progress_check']=$sellOrder->getProgressCheck();

            $checkDate='';
            if($sellOrder->getCheckDate()) $checkDate=Common::formatDateTime($sellOrder->getCheckDate());

            $tmp['check_date']=$checkDate;
            $tmp['check_by']=$sellOrder->getCheckBy();
            $tmp['approval_check_by']=$sellOrder->getApprovalCheckBy();
            $tmp['approval_check_date']=Common::formatDateTime($sellOrder->getApprovalCheckDate());
            $tmp['pack_number']=$sellOrder->getPackNumber();
            $orders[]=$tmp;
        }

        if($request->isPost())
            return new JsonModel($orders);

        return new ViewModel(['orders' => $orders]);
    }

    /**
     * @return ViewModel
     */
    public function detailAction(){

        return new ViewModel();
    }

    /**
     * @return ViewModel
     */
    public function checkAction(){
        $request = $this->getRequest();
        $sellManager = new SellManager($this->entityManager);

        if($request->isPost()) {
            $sellID = $request->getPost('sellId', 0);
            $qty = $request->getPost('qty', 0);
            try {
                if(!$sellID||!$qty)
                    throw new \Exception("Dũ liệu không hợp lệ!");

                $sellItem = $sellManager->getSellById($sellID);
                $checkedQty=$sellItem->getCheckQty();
                if($checkedQty==0 ||$checkedQty=="")$checkedQty=0;
                if($checkedQty==$sellItem->getQuantity())
                    throw new \Exception($sellItem->getProduct()->getName()." đã kiểm đủ!");
                else{
                    $sellItem->setCheckQty($sellItem->getCheckQty()+$qty);
                    $sellItem->setCheckBy($this->userInfo->getUsername());
                    $sellItem->setCheckDate(new \DateTime());
                    $this->entityManager->flush();
                    $result["status"]=1;
                    $result['message']="Xac nhan kiem san pham!";
                }
            }catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }
            return new JsonModel($result);
        }
        else
        {
            $sellOrderID = $this->params()->fromRoute('id', 0);

            $sellOrder = $sellManager->getSellOrderById($sellOrderID);
            $sells = $sellOrder->getSell();
            $products=array();

            foreach ($sells as $sellItem){
                $product=$sellItem->getProduct();
                $orderQty=$sellItem->getQuantity();//so luong dat don
                $checkedQty=$sellItem->getCheckQty();//sl da kiem
                $packUnit=$sellItem->getPackUnit();//quy cach dong thung

                $tmp["sell_id"]=$sellItem->getId();
                $tmp["approved_qty"]=$sellItem->getApprovedQty();
                $tmp["pack_unit"]=$packUnit;
                $tmp["name"]=$product->getName();
                $tmp["exchange_unit"]=$product->getExchangeUnit();
                $tmp["weight"]=$product->getWeight();
                $tmp["unit"]=$product->getUnit()->getName();
                $tmp["order_qty"]=$orderQty;
                $tmp["check_qty"]=$checkedQty;
                $tmp["code"]=$product->getCode();
                $tmp["code_1"]=$product->getCode1();
                $tmp["code_2"]=$product->getCode2();
                $tmp["code_3"]=$product->getCode3();

                $tmp["pack_code"]=$product->getPackCode();

                if(!$product->getPackCode())
                    $tmp["pack_code"]='PACK_CODE_'.$product->getId();

                $tmp["description"]='';
                if($product->getExchangeUnit()>1)
                    $tmp["description"]='Kiểm 1 lần '.$product->getExchangeUnit().' '.$product->getUnit()->getName();

                $products[]=$tmp;
            }
            return new ViewModel(['sellOrder'=>$sellOrder,'products'=>$products]);
        }
    }

    /**
     * reset du lieu da kiem cua san pham
     * @return void|JsonModel
     */
    public function recheckProductAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $sellID = $request->getPost('sellId', 0);
            $sellManager = new SellManager($this->entityManager);
            try {
                if(!$sellID)
                    throw new \Exception("Dũ liệu không hợp lệ!");

                $sellItem = $sellManager->getSellById($sellID);

                $sellItem->setCheckQty(null);
                $sellItem->setCheckBy(null);
                $sellItem->setCheckDate(null);
                $sellItem->setQuantity($sellItem->getApprovedQty());

                $sellOrder=$sellItem->getSellOrder();
                $sellOrder->setApprovalCheckDate(null);
                $sellOrder->setApprovalCheckBy(null);
                $sellOrder->setStatus(Define::_ORDER_PACKING_STATUS);

                $this->entityManager->flush();
                $result["status"]=1;
                $result['message']="Đã reset dữ liệu kiểm của sản phẩm!";
            }catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    /**
     * reset du lieu kiem ca don hang
     * @return void|JsonModel
     */
    public function recheckOrderAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $orderID = $request->getPost('orderId', 0);
            $sellManager = new SellManager($this->entityManager);
            try {
                if(!$orderID)
                    throw new \Exception("Dũ liệu không hợp lệ!");

                $sellOrder = $sellManager->getSellOrderById($orderID);

                foreach ($sellOrder->getSell() as $sellItem){
                    $sellItem->setCheckQty(null);
                    $sellItem->setCheckBy(null);
                    $sellItem->setCheckDate(null);
                    $sellItem->setQuantity($sellItem->getApprovedQty());
                }
                $sellOrder->setPackNumber(null);
                $sellOrder->setCheckDate(null);
                $sellOrder->setApprovalCheckDate(null);
                $sellOrder->setApprovalCheckBy(null);
                $sellOrder->setStatus(Define::_ORDER_PACKING_STATUS);
                $this->entityManager->flush();
                $result["status"]=1;
                $result['message']="Đã reset dư liệu kiểm của đơn hàng!";
            }catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    /**
     * Xác nhận đã kiểm xong đơn hàng
     * @return void|JsonModel
     */
    public function confirmCheckAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $orderID = $request->getPost('orderId', 0);
            $packNumber = $request->getPost('packNumber', 0);
            $sellManager = new SellManager($this->entityManager);
            try {
                if(!$packNumber)
                    throw new \Exception("Nhập số thùng đã đóng gói!");
                if(!$orderID)
                    throw new \Exception("Không tìm thấy đơn hàng!");

                $sellOrder = $sellManager->getSellOrderById($orderID);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                $sellOrder->setStatus(Define::_ORDER_PACKED_STATUS);
                $sellOrder->setPackNumber($packNumber);
                $sellOrder->setCheckDate(new \DateTime());
                $sellOrder->setCheckBy($user->getUsername());

                $this->entityManager->flush();

                $msg="Đã kiểm xong đơn hàng: ".$sellOrder->getGrocery()->getGroceryName();
                $this->flashMessenger()->addSuccessMessage($msg);

                $result["status"]=1;
                $result['message']=$msg;
            }catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }
            return new JsonModel($result);
        }
    }
    public function approvalCheckAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $orderID = $request->getPost('orderId', 0);
            $packNumber = $request->getPost('packNumber', 0);
            $sellManager = new SellManager($this->entityManager);
            try {
                if(!$orderID || !$packNumber)
                    throw new \Exception("Dũ liệu không hợp lệ!");

                $sellOrder = $sellManager->getSellOrderById($orderID);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                foreach ($sellOrder->getSell() as $sellItem){
                    $orderQty=$sellItem->getQuantity();//so luong san pham khach dat
                    $checkQty=$sellItem->getCheckQty();
                    //xet nhung san pham kiem bi thieu
                    if($orderQty>$checkQty){
                        $returnQty = $orderQty-$checkQty;//so luong san pham tra lai
                        $sellItem->setQuantity($checkQty);//set so luong thuc te da kiem

                        //tinh toan lai ton kho cua san pham
                        $product=$sellItem->getProduct();
                        $inventory = $product->getInventory();
                        $product->setInventory($inventory + $returnQty);

                        //tao moi ban ghi product activity
                        $productActivity = new ProductActivity();
                        $productActivity->setUser($user);
                        $productActivity->setProduct($product);
                        $productActivity->setNote('Quét thiếu: '.$sellItem->getSellOrder()->getGrocery()->getGroceryName());
                        $productActivity->setCreatedDate(new \DateTime());
                        $productActivity->setChange($returnQty);
                        $this->entityManager->persist($productActivity);

                        //insert sell order activity
                        $sellOrderActivity = new SellOrderActivity();
                        $sellOrderActivity->setSellOrder($sellItem->getSellOrder());
                        $sellOrderActivity->setActionBy($user->getUsername());
                        $sellOrderActivity->setActionTime(new \DateTime());
                        $action='Quét thiếu: '.$returnQty.' '.$product->getName();
                        $sellOrderActivity->setActionIcon('fa-minus-square');
                        $sellOrderActivity->setAction($action);
                        $this->entityManager->persist($sellOrderActivity);
                    }
                }

//                $sellOrder->setStatus(Define::_ORDER_PACKED_STATUS);
//                $sellOrder->setPackNumber($packNumber);
                $sellOrder->setApprovalCheckDate(new \DateTime());
                $sellOrder->setApprovalCheckBy($user->getUsername());

                $this->entityManager->flush();

                $msg="Đã duyệt đơn hàng: ".$sellOrder->getGrocery()->getGroceryName();
                $this->flashMessenger()->addSuccessMessage($msg);

                $result["status"]=1;
                $result['message']=$msg;
            }catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }
            return new JsonModel($result);
        }
    }
}