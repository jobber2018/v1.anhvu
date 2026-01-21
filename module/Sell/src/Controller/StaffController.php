<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-15
 * Time: 15:55
 */

namespace Sell\Controller;


use Admin\Service\AdminManager;
use Doctrine\ORM\EntityManager;
use Google\Service\AdMob\Date;
use Product\Service\ProductManager;
use Report\Entity\CostRevenue;
use Report\Service\CostRevenueManager;
use Sell\Entity\DeliveryCar;
use Sell\Entity\SellOrder;
use Sell\Entity\SellOrderActivity;
use Sell\Entity\SellOrderInvoice;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeAdminController;
use Users\Entity\User;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class StaffController extends SuldeAdminController
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
        $container = new \Zend\Session\Container();
        $carLicense=@$container->getManager()->getStorage()->toArray()['car_license'];
        $sellOrder=$this->sellManager->getSellOrderByCar($carLicense);

//        $sellOrder = $this->sellManager->getSellOrder(2);
        return new ViewModel([
            'sellOrder'=>$sellOrder
            ,'carLicense'=>$carLicense
        ]);
    }
    public function deliveryODetailAction(){
        $sellOrderID = $this->params()->fromRoute('id', 0);

        $sellOrder = $this->sellManager->getSellOrderById($sellOrderID);
        if($sellOrder->getStatus()!=Define::_ORDER_DELIVERING_STATUS)
            return $this->redirect()->toRoute('sell-staff',['action'=>'select-delivery-car']);
        return new ViewModel(['sellOrder'=>$sellOrder,'grocery'=>$sellOrder->getGrocery()]);
    }

    public function deliveryConfirmAction()
    {
        $sellOrderID = $this->params()->fromRoute('id', 0);
        $sellOrder = $this->sellManager->getSellOrderById($sellOrderID);
        $request = $this->getRequest();
        if($request->isPost()){
            //cap nhat status=21 (giao hang thanh cong)
            $request = $this->getRequest();

            $payMethod = $request->getPost("pay");//1= chuyen khoan, 2= tien mat
            $note = $request->getPost("note", "");

//            $files = $request->getFiles()->toArray();
//            print_r($files);

            //upload all image invoice
//
//            if($files){
//                foreach ($files as $value){
//                    $extension = @pathinfo($value[$this->fileName]['name'])['extension'];
//                    $fileName = @pathinfo($this->fileData[$this->fileName]['name'])['filename'];
//                    $dirname = @pathinfo($this->fileData[$this->fileName]['name'])['dirname'];
//                }
//            }


            /*$imageUpload = new ImageUpload('imageFile', $files, 'invoice/');
            $fileUrl = $imageUpload->upload();
            if($fileUrl)
                $sellOrder->setImg('/img/'.$fileUrl);

            $imageUpload = new ImageUpload('imageFile1', $files, 'invoice/');
            $fileUrl = $imageUpload->upload();
            if($fileUrl)
                $sellOrder->setImg1('/img/'.$fileUrl);

            $imageUpload = new ImageUpload('imgf', $files, 'invoice/');
            $fileUrl = $imageUpload->upload();
            if($fileUrl)
                $sellOrder->setImg2('/img/'.$fileUrl);

            $imageUpload = new ImageUpload('imageFile3', $files, 'invoice/');
            $fileUrl = $imageUpload->upload();
            if($fileUrl)
                $sellOrder->setImg3('/img/'.$fileUrl);

            $imageUpload = new ImageUpload('imageFile4', $files, 'invoice/');
            $fileUrl = $imageUpload->upload();
            if($fileUrl)
                $sellOrder->setImg4('/img/'.$fileUrl);*/

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

            $costRevenueManager = new CostRevenueManager($this->entityManager);
            $currentDate=date("Y-m-d");
            $costRevenues = $costRevenueManager->getDate($currentDate);

            $totalRevenue= $sellOrder->getTotalAmountToPaid();

            if($costRevenues){
                $costRevenue=$costRevenues[0];
                $costRevenue->setOrderDelivered($costRevenue->getOrderDelivered()+$totalRevenue);
                $costRevenue->setOrderDeliveredNumber($costRevenue->getOrderDeliveredNumber()+1);
            }else{
                $costRevenue=new CostRevenue();
                $costRevenue->setOrderDelivered($totalRevenue);
                $costRevenue->setOrderDeliveredNumber(1);
                $costRevenue->setDate(new \DateTime());
                $costRevenue->setUser($user);
                $costRevenue->setCreatedDate(new \DateTime());
            }
            $this->entityManager->persist($costRevenue);

            //insert sell order activity
            $sellOrderActivity = new SellOrderActivity();
            $sellOrderActivity->setSellOrder($sellOrder);
            $sellOrderActivity->setActionBy($this->userInfo->getUsername());
            $sellOrderActivity->setActionTime(new \DateTime());
            $action='Đã giao hàng';
            $sellOrderActivity->setAction($action);
            $sellOrderActivity->setActionIcon('fa-thumbs-up');
            $this->entityManager->persist($sellOrderActivity);
            $this->entityManager->flush();

            $groceryName = $sellOrder->getGrocery()->getGroceryName();

            $adminManager = new AdminManager($this->entityManager);
            $msg = '<i class="fa fa-truck"></i>Đã giao hàng ' . $groceryName;
            $data["title"]= $msg;
            $data["msg"]=$msg;
            $data["uid"]=$this->userInfo->getId();
            $adminManager->addActivity($data);

            $this->flashMessenger()->addSuccessMessage('Đã xác nhận giao hàng ' . $groceryName);


//            return $this->redirect()->toRoute('sell-staff');
            return $this->redirect()->toRoute('sell-staff',['action'=>'select-delivery-car']);

        }else{
            return new ViewModel(['sellOrder'=>$sellOrder,'grocery'=>$sellOrder->getGrocery()]);
        }
    }

    public function deliveryMapAction(){
        $container = new \Zend\Session\Container();
        $carLicense=$container->getManager()->getStorage()->toArray()['car_license'];
        $sellOrder=$this->sellManager->getSellOrderByCar($carLicense);
//        $sellOrder = $this->sellManager->getSellOrderDelivery();
        $configManage = new ConfigManager();

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariable('sellOrder',$sellOrder);
        $view->setVariable('geoKey',$configManage->getGeoKey());
        return $view;
    }

    /**
     * return ajax
     */
    public function uploadInvoiceAction(){

        $sellOrderId = $this->params()->fromRoute('id',0);

        if($sellOrderId<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }
        $sellOrder = $this->sellManager->getSellOrderById($sellOrderId);
        $request = $this->getRequest();

        if($request->isPost()) {
            $imageUpload = new ImageUpload('file', $request->getFiles()->toArray(), 'invoice/sell/');
            $fileUrl = $imageUpload->upload();
            if($fileUrl){
                $fileUrl = '/img/'.$fileUrl;
                $sellOrderInvoice = new SellOrderInvoice();
                $sellOrderInvoice->setPath($fileUrl);
                $sellOrderInvoice->setUploadDate(new \DateTime());
                $sellOrderInvoice->setSellOrder($sellOrder);

                //user upload by
                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);
                $sellOrderInvoice->setUser($user);

                $sellOrder->addInvoice($sellOrderInvoice);
                $this->entityManager->persist($sellOrder);
                $this->entityManager->flush();
                $result=[
                    'status' => '1',
                    'message'=>'',
                    'id'=>$sellOrderInvoice->getId()
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
            $sellOrderInvoiceId = $request->getPost("id");
            if($sellOrderInvoiceId){

                $soi = $this->entityManager->getRepository(SellOrderInvoice::class)->find($sellOrderInvoiceId);

                $sellOrder = $soi->getSellOrder();
                if($sellOrder->getStatus()==Define::_ORDER_PAID_STATUS || $sellOrder->getStatus()==Define::_ORDER_WAIT_FOR_PAY_STATUS){
                    $result=[
                        'status' => '0',
                        'message'=>'Không thể xoá ảnh đơn hàng ở trạng thái này!'
                    ];
                }else{
                    //remove file on server
                    if (file_exists(ROOT_PATH.$soi->getPath())) {
                        unlink(ROOT_PATH.$soi->getPath());
                    }

                    //remove file in data
                    $this->entityManager->remove($soi);
                    $this->entityManager->flush();

                    $result=[
                        'status' => '1',
                        'message'=>''
                    ];
                }
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

    public function invoiceImageAction(){
        $sellOrderId = $this->getRequest()->getQuery("id");

        if($sellOrderId){
            $sellOrder = $this->sellManager->getSellOrderById($sellOrderId);
            $img = array();
            if(count($sellOrder->getInvoice())){

                foreach ($sellOrder->getInvoice() as $invoice)
                    $img[]=$invoice->getPath();

                $result=[
                    'status' => '1',
                    'data'=>$img,
                    'message'=>''
                ];
            }else{
                $result=[
                    'status' => '0',
                    'message'=>'Không tìm thấy ảnh đơn hàng!'
                ];
            }
        }else{
            $result=[
                'status' => '0',
                'message'=>'Không tìm thấy đơn hàng!'
            ];
        }
        return new JsonModel($result);
    }

    public function selectDeliveryCarAction()
    {

        $userId = $this->userInfo->getId();
        $defaultCar=$this->userInfo->getPositionOption();//phu se thi truong nay se co du lieu la bien so xe

        //neu da xac dinh duoc xe thi den trang danh sach don dang giao
        if(@$_SESSION['car_license']){
            return $this->redirect()->toRoute('sell-staff');
        }

        //chua xac dinh duoc xe
        //neu la phu xe
        if($defaultCar){
            $_SESSION['car_license']=$defaultCar;
            return $this->redirect()->toRoute('sell-staff');
        }

        //không phai lai xe
        if($this->userInfo->getPosition()!='driver'){
            return $this->redirect()->toRoute('sell-staff',['action'=>'delivering']);
        }

        $currentDate = (new \DateTime())->format('Y-m-d');

        $deliveryCar = $this->sellManager->getUserByLicensePlate($this->userInfo->getId(), $currentDate);
        //user da duoc gan xe
        if($deliveryCar){
            $_SESSION['car_license']=$deliveryCar[0]->getLicensePlate();
            return $this->redirect()->toRoute('sell-staff');
        }
        //lay thong tin xe da chon trong ngay
        $deliveryCarByDate = $this->sellManager->getCarDeliveryByDate($currentDate);

        $carSelected=[];//danh sach xe da chon
        foreach ($deliveryCarByDate as $deliveryCarByDateItem)
            $carSelected[]=$deliveryCarByDateItem->getLicensePlate();

        //lay xe chua duoc chon
        $carLicense = $this->randomCar($carSelected);
        $_SESSION['car_license']=$carLicense;

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        $deliveryCar = new DeliveryCar();
        $deliveryCar->setUser($user);
        $deliveryCar->setLicensePlate($carLicense);
        $deliveryCar->setCreatedDate(new \DateTime());
        $this->entityManager->persist($deliveryCar);
        $this->entityManager->flush();
        return $this->redirect()->toRoute('sell-staff');
    }

    public function deliveringAction()
    {
        $sellOrder = $this->sellManager->getSellOrder(2);
        return new ViewModel([
            'sellOrder'=>$sellOrder
        ]);
    }

    public function deliveryMapAllAction(){
//        $container = new \Zend\Session\Container();
//        $carLicense=$container->getManager()->getStorage()->toArray()['car_license'];
//        $sellOrder=$this->sellManager->getSellOrderByCar($carLicense);
        $sellOrder = $this->sellManager->getSellOrderDelivery();
        $configManage = new ConfigManager();

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariable('sellOrder',$sellOrder);
        $view->setVariable('geoKey',$configManage->getGeoKey());
        return $view;
    }

    private function randomCar($p_arrayCarSelected)
    {
        $carList[0]='30M71302';
        $carList[1]='30M61300';
        $carList[2]='29H23118';

        $newCarList=array_diff($carList,$p_arrayCarSelected);

        $randomCar=array_rand($newCarList,1);
        return $newCarList[$randomCar];
    }

}