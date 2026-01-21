<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Sell\Controller;


use Admin\Service\AdminManager;
use Grocery\Service\GroceryManager;
use Product\Service\ProductManager;
use Sell\Entity\Sell;
use Sell\Entity\SellOrder;
use Sell\Service\SellManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeFrontController;
use Users\Entity\User;
use Zend\View\Model\ViewModel;

class IndexController extends SuldeFrontController
{

    private $entityManager;
    private $sellManager;

    public function __construct(EntityManager $entityManager, SellManager $sellManager)
    {
        $this->entityManager = $entityManager;
        $this->sellManager = $sellManager;
    }
    public function indexAction()
    {
        return $this->redirect()->toRoute('sell-staff');
        $sellOrder = $this->sellManager->getSellOrder(2);
//        $sellOrder = $this->sellManager->getSellOrderDelivery('1,2');
        return new ViewModel([
            'sellOrder'=>$sellOrder
        ]);
    }

    public function detailOrderAction(){
        return $this->redirect()->toRoute('sell-staff');
        $sellOrderID = $this->params()->fromRoute('id', 0);

        $sellOrder = $this->sellManager->getSellOrderById($sellOrderID);

        return new ViewModel(['sellOrder'=>$sellOrder,'grocery'=>$sellOrder->getGrocery()]);
    }

    public function confirmDeliveryAction(){
        return $this->redirect()->toRoute('sell-staff');
        $sellOrderID = $this->params()->fromRoute('id', 0);
        $sellOrder = $this->sellManager->getSellOrderById($sellOrderID);
        $request = $this->getRequest();
        if($request->isPost()){
            //cap nhat status=21 (giao hanh thanh cong)
            $request = $this->getRequest();
            $payMethod = $request->getPost("pay");//1= chuyen khoan, 2= tien mat
            $note = $request->getPost("note", "");

            $imageUpload = new ImageUpload('imageFile', $request->getFiles()->toArray(), 'invoice/');
            $fileUrl = $imageUpload->upload();
            if($fileUrl)
                $sellOrder->setImg('/img/'.$fileUrl);

            $userId= $this->userInfo->getId();
            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if(strlen($note)>0){
                $uName=$user->getFullname();
                if($sellOrder->getNote())
                    $note = $sellOrder->getNote()."\n" .$uName ."|".date("Y-m-d h:i:s")."|".$note;
                else
                    $note = $uName."|".date("Y-m-d h:i:s")."|".$note;

                $sellOrder->setNote($note);
            }

            $sellOrder->setStatus(21);

            $sellOrder->setPayMethod($payMethod);

            $sellOrder->setDeliveredDate(new \DateTime());
            $sellOrder->setDeliveredBy($user);

            $this->entityManager->flush();
            $groceryName = $sellOrder->getGrocery()->getGroceryName();

            $adminManager = new AdminManager($this->entityManager);
            $msg = '<i class="fa fa-truck"></i>Đã giao hàng ' . $groceryName;
            $data["title"]= $msg;
            $data["msg"]=$msg;
            $data["uid"]=$this->userInfo->getId();
            $adminManager->addActivity($data);

            $this->flashMessenger()->addSuccessMessage('Đã xác nhận giao hàng ' . $groceryName);
            return $this->redirect()->toRoute('sell-front');
        }else{
            return new ViewModel(['sellOrder'=>$sellOrder,'grocery'=>$sellOrder->getGrocery()]);
        }
    }

    public function mapAction(){
        return $this->redirect()->toRoute('sell-staff');
        $sellOrder = $this->sellManager->getSellOrderDelivery();
        $configManage = new ConfigManager();

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariable('sellOrder',$sellOrder);
        $view->setVariable('geoKey',$configManage->getGeoKey());
        return $view;
    }

    public function addOrderAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $name = $request->getPost("txtName");
                $mobile = Common::verifyMobile($request->getPost("txtMobile"));

                //tim grocery theo mobile
                $groceryManager = new GroceryManager($this->entityManager);
                $grocery = $groceryManager->getByMobile($mobile);
//                $grocery = $groceryMobile[0];

                //neu khong tim thay grocery => gan grocery default
                $note='';
                if(!$grocery){
                    $grocery = $groceryManager->getById(1);
                    $note = "Customer|".date("Y-m-d h:i:s")."|".$mobile.' Name: '.$name;
                }

                $user = $grocery->getGroceryCat()->getUser();

                //lay san pham trong gio hang
                $productIds=array();
                foreach ($_SESSION['my_card'] as $key=>$card){
                    $productIds[]=$key;
                }
                $productManager = new ProductManager($this->entityManager);
                $products = $productManager->getMyCard($productIds);

                $sellOrder = new SellOrder();

//                $totalPrice=0;

                //foreach ($data as $key=>$item) {
                foreach ($products as $productItem){
                    $opt = $_SESSION['my_card'][$productItem->getId()]['opt'];
                    $quantity = $_SESSION['my_card'][$productItem->getId()]['qty'];

                    //tien chiet khau san pham
                    $discount=$productItem->getUnitPriceSale();

                    //quy doi so luong sang unit
                    //neu mua theo thung => tinh ra so luong unit
                    if($opt==1){
                        $quantity = $productItem->getBoxUnit()*$quantity;
                        //tien chiet khau theo thung
                        $discount=$productItem->getPackPriceSale();
                    }


                    $price = $productItem->getActivePrice();

//                    $totalPrice += $quantity * $price->getPrice();

                    $sell = new Sell();
                    $sell->setPrice($price);
                    $sell->setProduct($productItem);
                    $sell->setQuantity($quantity);
                    $sell->setPackUnit($productItem->getBoxUnit());
                    $sell->setCost($productItem->getAveragePrice());
                    $sell->setDiscount($discount);

                    $sell->setSellOrder($sellOrder);
                    $sellOrder->addSell($sell);
                }

                $sellOrder->setUser($user);
//                $sellOrder->setTotalPrice($totalPrice);
                $sellOrder->setGrocery($grocery);
                $sellOrder->setCreatedDate(new \DateTime());
                $sellOrder->setStatus(-2);
                $sellOrder->setMethod(-1);//khach hang tao don
                $sellOrder->setSource("web");
                if(strlen($note)>0)
                    $sellOrder->setNote($note);

                $this->entityManager->persist($sellOrder);
                $this->entityManager->flush();

                /*$adminManager = new AdminManager($this->entityManager);
                $msg = '<i class="fa fa-shopping-cart"></i>Tạo đơn ' . $sellOrder->getGrocery()->getGroceryName();
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);*/

                $this->flashMessenger()->addSuccessMessage('Cảm ơn bạn đã đặt hàng, chúng tôi sẽ cố gắng giao hàng sớm nhất có thể!');
                $result['status']=1;
                $_SESSION['my_card']=null;
                return $this->redirect()->toRoute('sell-front',['action'=>'add-order']);

            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
                $this->flashMessenger()->addErrorMessage('Đã có lỗi trong quá trình tạo đơn hàng, Vui lòng thử lại!'. $e->getMessage());
            }
            //return new JsonModel($result);
        }
    }
}