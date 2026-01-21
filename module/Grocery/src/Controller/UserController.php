<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Grocery\Controller;


use Admin\Service\AdminManager;
use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Exception;
use Grocery\Entity\Grocery;
use Grocery\Entity\GroceryCrm;
use Grocery\Entity\GroceryFeeling;
use Grocery\Entity\GroceryInOut;
use Grocery\Form\GroceryForm;
use Grocery\Service\GroceryManager;
use Doctrine\ORM\EntityManager;
use GroceryCat\Service\GroceryCatManager;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeFrontController;
use Sulde\Service\SuldeUserController;
use Users\Entity\User;
use Zend\Paginator\Paginator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UserController extends SuldeUserController
{

    private $entityManager;
    private $groceryManager;

    public function __construct(EntityManager $entityManager, GroceryManager $groceryManager)
    {
        $this->entityManager = $entityManager;
        $this->groceryManager = $groceryManager;
    }

    public function indexAction()
    {
//        $this->userInfo;
        $userId= $this->userInfo->getId();
        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCatList = $groceryCatManager->getList($userId);
        $groceryCatData=array();
        foreach ($groceryCatList as $groceryCatItem) {
            $groceryCatData[$groceryCatItem->getId()]["name"] = $groceryCatItem->getName();
            $groceryCatData[$groceryCatItem->getId()]["grocery"]=$groceryCatItem->getGroceryActiveCount();
            $groceryCatData[$groceryCatItem->getId()]["date"]=$this->getDate($groceryCatItem->getDay());
            $groceryCatData[$groceryCatItem->getId()]["day"]=$groceryCatItem->getDay();
        }
//        var_dump($groceryCatData);

        return new ViewModel(["groceryCatData"=>$groceryCatData]);
    }

    public function listAction()
    {
        $groceryCatId = $this->params()->fromRoute('id',0);
//        $clat = $this->params()->fromQuery('lat',0);
//        $clng = $this->params()->fromQuery('lng',0);
//        $textSearch = $this->params()->fromQuery('kw',"");

//        $this->userInfo;
        $userId= $this->userInfo->getId();

        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCat = $groceryCatManager->getById($groceryCatId);

        //show only tuyen cua nv dang login.
        if($groceryCat->getUser()->getId()!=$userId && $this->userInfo->getRole()!='admin'){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        //$groceryListByCat = $this->groceryManager->getListByCat($groceryCatId);
//        $groceryListByCat = $this->groceryManager->getListByCatPosition($groceryCatId,$clat,$clng,$textSearch);
        $groceryListByCat = $this->groceryManager->getListByCat($groceryCatId);

        $sellManager = new SellManager($this->entityManager);
        $orderAnalytic = $sellManager->getOrderAnalytic();

        return new ViewModel([
            "groceryList"=>$groceryListByCat,
            "groceryCat"=>$groceryCat,
            'orderAnalytic'=>$orderAnalytic
        ]);
    }

    public function listAllAction()
    {
        $userId= $this->userInfo->getId();

        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCat = $groceryCatManager->getList($userId);

        return new ViewModel(["groceryCat"=>$groceryCat]);
    }

    public function listDraftOrderAction()
    {
        $userId= $this->userInfo->getId();

        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCat = $groceryCatManager->getList($userId);

        return new ViewModel(["groceryCat"=>$groceryCat]);
    }

    public function addAction(){
        $groceryCatId = $this->params()->fromRoute('id',0);
        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCat = $groceryCatManager->getById($groceryCatId);

        $form =new GroceryForm("add");

        $request = $this->getRequest();
        if($request->isPost()){
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($data);

//            if($form->isValid()){
                try{
                    $mobile = Common::verifyMobile($data['mobile']);
                    $groceryViaMobile = $this->groceryManager->getByMobile($mobile);

                    if($groceryViaMobile){
                        $groceryId = $groceryViaMobile->getGroceryCat()->getId();
                        if($groceryId >1){
                            $msgErr='<b>'.$mobile. '</b> của khách <b>'.$groceryViaMobile->getGroceryName().'</b>, '.$groceryViaMobile->getGroceryCat()->getUser()->getFullname(). ' đang chăm sóc. Bạn không thể tạo khách hàng này!';
                            throw new Exception($msgErr);
                        }elseif ($groceryId==1){
                            $groceryViaMobile->setGroceryCat($groceryCat);
                            $this->entityManager->flush();
                            $this->flashMessenger()->addSuccessMessage($groceryViaMobile->getGroceryName().' Đã được thêm vào tuyến của bạn!');
                            return $this->redirect()->toRoute('grocery-user',['action'=>'list','id'=>$groceryViaMobile->getGroceryCat()->getId()]);
                        }

                    }

                    $grocery = new Grocery();
                    $imageUpload = new ImageUpload('imageFile', $request->getFiles()->toArray(), 'grocery/');
                    $fileUrl = $imageUpload->upload();
                    if($fileUrl)
                        $grocery->setImg('/img/'.$fileUrl);

                    $grocery->setGroceryCat($groceryCat);
                    $grocery->setAddress($data["address"]);
                    $grocery->setGroceryName(ucfirst($data["grocery_name"]));
                    $grocery->setMobile($mobile);
                    $grocery->setOwnerName($data["owner_name"]);
                    $grocery->setDeliveryNote($data["delivery_note"]);

                    $grocery->setActive(1);
                    $grocery->setPayTotal(0);
                    $this->entityManager->persist($grocery);
                    $this->entityManager->flush();

                    $adminManager = new AdminManager($this->entityManager);
                    $msg = '<i class="fa fa-user"></i>Thêm mới cửa hàng '.$grocery->getGroceryName();
                    $data["title"]=$msg;
                    $data["msg"]=$msg;
                    $data["uid"]=$this->userInfo->getId();
                    $adminManager->addActivity($data);

                    return $this->redirect()->toRoute('grocery-user',['action'=>'map-position','id'=>$grocery->getId()]);
                }catch (\Exception $e){
                    $message = $e->getMessage();
                    $this->flashMessenger()->addErrorMessage($message);
                }
//            }else{
//                $form->setData($data);
//                print_r($data);
//                print_r($form->isValid());
//            }
        }
//        echo $message;
        return new ViewModel(['form'=>$form,'groceryCat'=>$groceryCat]);
    }

    public function mapPositionAction(){
        $groceryId = $this->params()->fromRoute('id',0);

        $grocery = $this->groceryManager->getById($groceryId);

        $form =new GroceryForm("edit");

        //echo $grocery->getGroceryName();
        $request = $this->getRequest();

        if($request->isPost()){
            $data = [
                'grocery_name'=> $grocery->getGroceryName(),
                'owner_name' => $grocery->getOwnerName(),
                'address'=>$grocery->getAddress(),
                'mobile'=>$grocery->getMobile()
            ];
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $data
            );
            $form->setData($data);

//            if($form->isValid()){
                $grocery->setLat($data["lat"]);
                $grocery->setLng($data["lng"]);
                $this->entityManager->flush();

                //add in/out grocery
                $groceryInOut = new GroceryInOut();
                $groceryInOut->setType("upd");
                $groceryInOut->setGrocery($grocery);
                $groceryInOut->setCreatedDate(new DateTime);
                $groceryInOut->setLat($data["lat"]);
                $groceryInOut->setLng($data["lng"]);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);
                $groceryInOut->setUser($user);
                $this->entityManager->persist($groceryInOut);
                $this->entityManager->flush();

                //add activity
                $adminManager = new AdminManager($this->entityManager);
                $msg = '<i class="fa fa-map-marker"></i>Cập nhật vị trí cho ' . $grocery->getGroceryName();
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);

                $this->flashMessenger()->addSuccessMessage('Vị trí đã được cập nhật '. $grocery->getGroceryName());
                return $this->redirect()->toRoute('grocery-user',['action'=>'list','id'=>$grocery->getGroceryCat()->getId()]);
//            }else{
//                $form->setData($data);
//            }
        }

        $configManage = new ConfigManager();
        return new ViewModel(['form'=>$form,'geoKey'=>$configManage->getGeoKey(),'grocery'=>$grocery]);
    }

    public function updatePositionAction()
    {
        $groceryId = $this->params()->fromRoute('id',0);

        $grocery = $this->groceryManager->getById($groceryId);


        //echo $grocery->getGroceryName();
        $request = $this->getRequest();

        if($request->isPost()){
            $data=$request->getPost()->toArray();

            $grocery->setLat($data["lat"]);
            $grocery->setLng($data["lng"]);
            $this->entityManager->flush();

            //add in/out grocery
            /*$groceryInOut = new GroceryInOut();
            $groceryInOut->setType("upd");
            $groceryInOut->setGrocery($grocery);
            $groceryInOut->setCreatedDate(new DateTime);
            $groceryInOut->setLat($data["lat"]);
            $groceryInOut->setLng($data["lng"]);

            $userId= $this->userInfo->getId();
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            $groceryInOut->setUser($user);
            $this->entityManager->persist($groceryInOut);
            $this->entityManager->flush();*/

            $this->flashMessenger()->addSuccessMessage('Đã cập nhật vị trí cho '. $grocery->getGroceryName());
            $result['status']=1;
            return new JsonModel($result);
        }

        $configManage = new ConfigManager();
        $form =new GroceryForm("edit");
        return new ViewModel(['form'=>$form,'geoKey'=>$configManage->getGeoKey(),'grocery'=>$grocery]);
    }
    public function panelAction(){
        $groceryId = $this->params()->fromRoute('id',0);
        $grocery = $this->groceryManager->getById($groceryId);
        return new ViewModel(["grocery"=>$grocery]);
    }

    public function checkInAction(){
        $groceryId = $this->params()->fromRoute('id',0);
        $clat = $this->params()->fromQuery('lat',0);
        $clng = $this->params()->fromQuery('lng',0);

        $errMessage='';
        try{

            $grocery = $this->groceryManager->getById($groceryId);

            if($clat==0 || $clng==0){
                throw new Exception('Không thể check in do không xác định được vị trí của bạn.');
            }

            $distanceArr=$this->groceryManager->getByLocality($clat,$clng,$grocery->getId());

            $distance=$distanceArr['distance'];
            $dateNow = new DateTime;
            if(is_null($distance))
                throw new Exception('Cửa hàng không có vị trí trên bản đồ, vui lòng cập nhật vị trí cửa hàng.');
            //cho phep check in cach cua hang 25m
            if($distance>=0 & $distance<=0.025){

                $groceryInOut = new GroceryInOut();
                $groceryInOut->setType("in");
                $groceryInOut->setGrocery($grocery);
                $groceryInOut->setCreatedDate($dateNow);
                $groceryInOut->setLat($clat);
                $groceryInOut->setLng($clng);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);
                $groceryInOut->setUser($user);

                $grocery->setCheckInDate($dateNow);
                $grocery->setCheckOutDate(null);
                $grocery->setTimeInGrocery(null);


                $isCheckIn = $this->groceryManager->getCheckInOutLast($groceryId);

                if($isCheckIn){
                    $checkIndate = $isCheckIn->getCreatedDate()->format('Y-m-d H:i:s');
                    //check lech tinh theo giay
                    $distanceTime = strtotime($dateNow->format('Y-m-d H:i:s'))-strtotime($checkIndate);
                    //Khoang cach check in lan nay so voi lan truoc chua qua 30phut
                    if($distanceTime<1800 & $userId==$isCheckIn->getUser()->getId()){
                        $checkIndate=strtotime($checkIndate);
                    }else{
                        $checkIndate=strtotime($dateNow->format('Y-m-d H:i:s'));
                        $this->entityManager->persist($groceryInOut);
                        $this->entityManager->flush();
                    }
                }else{
                    $checkIndate=strtotime($dateNow->format('Y-m-d H:i:s'));
                    $this->entityManager->persist($groceryInOut);
                    $this->entityManager->flush();
                }

                $fullname = $this->userInfo->getFullname();
                $adminManager = new AdminManager($this->entityManager);
                $msg = '<i class="fa fa-map-marker"></i>Checkin cửa hàng ' . $grocery->getGroceryName();
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);

            }else{
                $checkIndate=strtotime($dateNow->format('Y-m-d H:i:s'));
                throw new Exception('Không thể check in do bạn đang ở quá xa cửa hàng.');
            };
        }catch (\Exception $e){
            $errMessage = $e->getMessage();
            $distance=-1;
        }

        return new ViewModel(["grocery"=>$grocery,'distance'=>$distance,'errMessage'=>$errMessage,'checkIndate'=>$checkIndate]);
    }

    public function checkOutAction(){
        $groceryId = $this->params()->fromRoute('id',0);
        $clat = $this->params()->fromQuery('lat',0);
        $clng = $this->params()->fromQuery('lng',0);
        $p_call = $this->params()->fromQuery('call',"");

        $grocery = $this->groceryManager->getById($groceryId);

        try{
            $result['status']=0;

            if($clat==0 || $clng==0){
                throw new Exception('Không thể check out do bạn đang ở quá xa cửa hàng.');
            }

            $distanceArr=$this->groceryManager->getByLocality($clat,$clng,$grocery->getId());

            //print_r($distanceArr);
            $distance=$distanceArr['distance'];
            //cho phep check out cach cua hang 100m
            if($distance>=0 & $distance<=0.1){
                $check_out_date = new DateTime;
                $groceryInOut = new GroceryInOut();
                $groceryInOut->setType("out");
                $groceryInOut->setGrocery($grocery);
                $groceryInOut->setCreatedDate($check_out_date);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);
                $groceryInOut->setUser($user);

                $grocery->setCheckOutDate($check_out_date);
                $grocery->setTimeInGrocery($this->differenceInMinutes($grocery->getCheckInDate(),$check_out_date));

                $this->entityManager->persist($groceryInOut);
                $this->entityManager->flush();

                $adminManager = new AdminManager($this->entityManager);
                $msg = '<i class="fa fa-sign-out"></i>Checkout cửa hàng ' . $grocery->getGroceryName();
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);

            }else{
                throw new Exception('Không thể check out do bạn đang ở quá xa cửa hàng.');
            };

            $result['status']=1;
        }catch (\Exception $e){
            $result['message']= $e->getMessage();
            $result['status']=0;
        }

        if($p_call=="ajax"){
            return new JsonModel($result);
        }else{
//            return $this->redirect()->toRoute('grocery-user',['action'=>'feeling','id'=>$grocery->getId()]);
            return $this->redirect()->toRoute('grocery-user',['action'=>'crm','id'=>$grocery->getId()]);
        }
    }

    /**
     * khong dung nua
     * @return \Zend\Http\Response|ViewModel
     * @throws Exception
     */
    public function feelingAction()
    {
        $groceryId = $this->params()->fromRoute('id', 0);

        if($groceryId==0){
            throw new Exception('Lỗi! khách hàng không có trong hệ thống!');
        }

        try{
            $grocery = $this->groceryManager->getById($groceryId);

            $request = $this->getRequest();
            if($request->isPost()){
                //update feeling
                $feeling = $request->getPost("feeling");
                $groceryFeeling = new GroceryFeeling();
                $groceryFeeling->setGrocery($grocery);
                $groceryFeeling->setFeeling($feeling);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                $groceryFeeling->setUser($user);
                $groceryFeeling->setCreatedDate(new \DateTime());
                $this->entityManager->persist($groceryFeeling);
                $this->entityManager->flush();

                if($feeling==3) $icon = '<img src="/img/icons/feeling-good.png" width="15px" />';
                elseif ($feeling==2) $icon = '<img src="/img/icons/feeling-nomal.png" width="15px" />';
                elseif ($feeling==1) $icon = '<img src="/img/icons/feeling-sad.png" width="15px" />';
                $adminManager = new AdminManager($this->entityManager);
                $msg = $grocery->getGroceryName() . ' '.$icon;
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);
                return $this->redirect()->toRoute('grocery-user',['action'=>'crm','id'=>$grocery->getId()]);
            }
        }
        catch (\Exception $e){
            throw new Exception($e->getMessage());
        }
        return new ViewModel(['grocery'=>$grocery]);
    }
    public function mapAction(){
        $groceryCatId = $this->params()->fromRoute('id',0);

        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCat = $groceryCatManager->getById($groceryCatId);
//        $userId= $this->userInfo->getId();
        //show only tuyen cua nv dang login.
        /*if($groceryCat->getUser()->getId()!=$userId){
            $this->getResponse()->setStatusCode('404');
            return;
        }*/

        $groceryListByCat = $this->groceryManager->getListByCat($groceryCatId);

        $configManage = new ConfigManager();

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariable('groceryList',$groceryListByCat);
        $view->setVariable('groceryCat',$groceryCat);
        $view->setVariable('geoKey',$configManage->getGeoKey());
        return $view;
        //return new ViewModel(["groceryList"=>$groceryListByCat,"groceryCat"=>$groceryCat,'geoKey'=>$configManage->getGeoKey()]);
    }

    public function mapAllAction(){

        $groceryListByCat = $this->groceryManager->getAll();
        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCat = $groceryCatManager->getAll();
        $configManage = new ConfigManager();
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariable('groceryList',$groceryListByCat);
        $view->setVariable('groceryCat',$groceryCat);
        $view->setVariable('geoKey',$configManage->getGeoKey());
        return $view;
    }
    public function editAction(){
        $groceryId = $this->params()->fromRoute('id',0);

        $grocery = $this->groceryManager->getById($groceryId);
        $form =new GroceryForm("edit");

        $userId= $this->userInfo->getId();

        if($grocery->getGroceryCat()->getUser()->getId()!=$userId && $this->userInfo->getRole()!='admin'){
            $this->getResponse()->setStatusCode('404');
            return;
        }

        $request = $this->getRequest();
        if($request->isPost()){
            $data = $request->getPost()->toArray();
            $form->setData($data);
            if($form->isValid()){
                $imageUpload = new ImageUpload('imageFile', $request->getFiles()->toArray(), 'grocery/');
                $fileUrl = $imageUpload->upload();
                if($fileUrl)
                    $grocery->setImg('/img/'.$fileUrl);
                $grocery->setGroceryName($data["grocery_name"]);
                $grocery->setOwnerName($data["owner_name"]);
                $grocery->setAddress($data["address"]);
                $grocery->setDeliveryNote($data["delivery_note"]);
                $grocery->setMobile(Common::verifyMobile($data["mobile"]));
                $grocery->setZaloConnect($data["zalo_connect"]);
                $grocery->setIsApproach($data["approach"]);
                $this->entityManager->flush();

                $adminManager = new AdminManager($this->entityManager);
                $msg = '<i class="fa fa-edit"></i>Sửa thông tin cửa hàng '.$grocery->getGroceryName();
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);

                $this->flashMessenger()->addSuccessMessage('Cập nhật thành công '. $grocery->getGroceryName());
                return $this->redirect()->toRoute('grocery-user',['action'=>'list','id'=>$grocery->getGroceryCat()->getId()]);
            }else{
                $form->setData($data);
            }
        }else{
            $data = [
                'grocery_name'=> $grocery->getGroceryName(),
                'owner_name' => $grocery->getOwnerName(),
                'address'=>$grocery->getAddress(),
                'mobile'=>$grocery->getMobile(),
                'delivery_note'=>$grocery->getDeliveryNote(),
                'zalo_connect'=>$grocery->getZaloConnect(),
                'approach'=>$grocery->getIsApproach()
            ];
            $form->setData($data);
        }
        return new ViewModel(['form'=>$form,'grocery'=>$grocery]);
    }


    /**
     * @return ViewModel
     */
    public function detailAction(){
        $groceryID = $this->params()->fromRoute('id', 0);
        $groceryDetail = $this->groceryManager->getById($groceryID);

        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $request = $this->getRequest();

        if($request->isPost()) {
            try {
                $routeId = $request->getPost("route");
                if($routeId){
                    $groceryCatItem = $groceryCatManager->getById($routeId);

                    //insert CRM change tuyen
                    $newRoute=$groceryCatItem->getName();
                    $oldRoute=$groceryDetail->getGroceryCat()->getName();
                    $note='Chuyển từ '.$oldRoute.' sang tuyến '.$newRoute;
                    $groceryCrm = new GroceryCrm();
                    $groceryCrm->setGrocery($groceryDetail);
                    $groceryCrm->setNote($note);

                    $user = $this->entityManager->getRepository(User::class)->find($this->userInfo->getId());

                    $groceryCrm->setUser($user);
                    $groceryCrm->setCreatedDate(new \DateTime());
                    $this->entityManager->persist($groceryCrm);

                    $groceryDetail->setGroceryCat($groceryCatItem);
                    $this->entityManager->persist($groceryDetail);
                    $this->entityManager->flush();
                }
            }catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('Err: '.$e->getMessage());
            }
        }

        $groceryCat = $groceryCatManager->getAll();

        return new ViewModel(["groceryDetail"=>$groceryDetail,"groceryCat"=>$groceryCat,"userInfo"=>$this->userInfo]);
    }

    public function crmAction(){
        $groceryID = $this->params()->fromRoute('id', 0);
        $grocery = $this->groceryManager->getById($groceryID);
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $note = $request->getPost("note");
                $groceryCrm = new GroceryCrm();
                $groceryCrm->setGrocery($grocery);
                $groceryCrm->setNote($note);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                $groceryCrm->setUser($user);
                $groceryCrm->setCreatedDate(new \DateTime());
                $this->entityManager->persist($groceryCrm);
                $this->entityManager->flush();

                $adminManager = new AdminManager($this->entityManager);
                $msg = '<i class="fa fa-bell"></i>'.$grocery->getGroceryName().": ".$note;
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);

                $this->flashMessenger()->addSuccessMessage('Đã thêm ghi chú cho khách hàng '.$grocery->getGroceryName());

            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('Err: '.$e->getMessage());
            }

            return $this->redirect()->toRoute('grocery-user',['action'=>'detail','id'=>$grocery->getId()]);
        }

        return new ViewModel(["groceryDetail"=>$grocery]);
    }

    public function rejectAction(){
        $groceryID = $this->params()->fromRoute('id', 0);
        $grocery = $this->groceryManager->getById($groceryID);

        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $groceryCatId=$grocery->getGroceryCat()->getId();

                $oldRoute=$grocery->getGroceryCat()->getName();
                $note='Chuyển từ '.$oldRoute.' sang tuyến Reject<br>';

                $note.= $request->getPost("note");
                $groceryCrm = new GroceryCrm();
                $groceryCrm->setGrocery($grocery);
                $groceryCrm->setNote($note);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                $groceryCrm->setUser($user);
                $groceryCrm->setCreatedDate(new \DateTime());
                $this->entityManager->persist($groceryCrm);

                //get grocery cat Reject (default)
                $groceryCatManager = new GroceryCatManager($this->entityManager);
                $groceryCat = $groceryCatManager->getById(1);
                $grocery->setGroceryCat($groceryCat);
                $this->entityManager->persist($grocery);

                $this->entityManager->flush();

                $adminManager = new AdminManager($this->entityManager);
                $msg = '<i class="fa fa-external-link-square"></i> Bỏ khách hàng '.$grocery->getGroceryName()." ra khỏi tuyến";
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);

                $this->flashMessenger()->addSuccessMessage('Đã bỏ khách hàng '.$grocery->getGroceryName().' ra khỏi tuyến');

            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('Err: '.$e->getMessage());
            }

            return $this->redirect()->toRoute('grocery-user',['action'=>'list','id'=>$groceryCatId]);
        }

        return new ViewModel(["groceryDetail"=>$grocery]);
    }

    public function orderAction(){
        $groceryID = $this->params()->fromRoute('id', 0);
        $grocery = $this->groceryManager->getById($groceryID);

        return new ViewModel(['grocery'=>$grocery]);
    }

    private function differenceInMinutes($startdate, $enddate)
    {
        $starttimestamp = strtotime($startdate->format('Y-m-d H:i:s'));
        $endtimestamp = strtotime($enddate->format('Y-m-d H:i:s'));
        $difference = abs($endtimestamp - $starttimestamp) / 60;
        return $difference;
    }

    private function getDate($p_dayNumber){
        $arrDay=ConfigManager::getDay();
        foreach ($arrDay as $key=>$value) {
            if($value==$p_dayNumber) $dayName=$key;
        }
        $date = strtotime("last Sunday");
        for ($x = 0; $x <= 6; $x++) {
            $date=strtotime("+ 1 days",$date);
            if($dayName==getdate($date)['weekday'])
                return date("d/m/Y",$date);
        }
    }

    public function MobileCheckAction(){
        $p_mobile = $this->getRequest()->getQuery("mobile");
        $mobile = Common::verifyMobile($p_mobile);

        $grocery = $this->groceryManager->getByMobile($mobile);

        //timf thay khach hang theo so dien thoai
        if($grocery){
            //kiem tra khach hang dang o tuyen nao
            //id=1 tuyen reject
            $groceryId = $grocery->getGroceryCat()->getId();
            if($groceryId >1){
                $result=[
                    'status' => 0,
                    'message'=>$mobile. ' của khách '.$grocery->getGroceryName().', '.$grocery->getGroceryCat()->getUser()->getFullname(). ' đang chăm sóc. Bạn không thể tạo khách hàng này!'
                ];
            }elseif ($groceryId==1){
                $result=[
                    'status' => 1,
                    'message'=>$mobile. ' của khách <b>'.$grocery->getGroceryName().'</b>, chưa ai chăm sóc. Bạn có thể tạo khách hàng này!'
                ];
            }
        }else{
            $result=[
                'status' => 1,
                'message'=>$mobile.' chưa có trên hệ thống, bạn có thể tạo của hàng này!'
            ];
        }

        return new JsonModel($result);
    }
}