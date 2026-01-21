<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Grocery\Controller;

use Admin\Service\AdminManager;
use Grocery\Entity\GroceryCrm;
use Grocery\Form\GroceryForm;
use Grocery\Service\GroceryManager;
use Doctrine\ORM\EntityManager;
use GroceryCat\Service\GroceryCatManager;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeAdminController;
use Users\Entity\User;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $groceryManager;

    public function __construct(EntityManager $entityManager, GroceryManager $groceryManager)
    {
        $this->entityManager = $entityManager;
        $this->groceryManager = $groceryManager;
    }

    /**
     * @return ViewModel
     */
    public function indexAction(){
        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCat = $groceryCatManager->getAll();

        return new ViewModel(["groceryCatList"=>$groceryCat]);
    }

    /**
     * @return ViewModel
     */
    public function listAction(){
        $grocery = $this->groceryManager->getAll();
//        echo count($grocery);
        return new ViewModel(["groceryList"=>$grocery]);
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

        return new ViewModel(["groceryDetail"=>$groceryDetail,"groceryCat"=>$groceryCat]);
    }

    /**
     * @return ViewModel
     */
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

                $this->userInfo;
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

            return $this->redirect()->toRoute('grocery-admin',['action'=>'detail','id'=>$grocery->getId()]);
        }

        return new ViewModel(["groceryDetail"=>$grocery]);
    }
    public function editAction(){
        $groceryId = $this->params()->fromRoute('id',0);

        $grocery = $this->groceryManager->getById($groceryId);
        $form =new GroceryForm("edit");

        $userId= $this->userInfo->getId();

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
                $grocery->setZaloConnect($data["zalo_connect"]);
                $grocery->setIsApproach($data["approach"]);
                $grocery->setMobile(Common::verifyMobile($data["mobile"]));
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage('Cập nhật thành công '. $grocery->getGroceryName());
                return $this->redirect()->toRoute('grocery-admin',['action'=>'detail','id'=>$grocery->getId()]);
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
    public function catListAction(){
        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCatList = $groceryCatManager->getAll();
        return new ViewModel([
            'groceryCatList'=>$groceryCatList
        ]);
    }

    public function catDetailAction(){
        $groceryCatID = $this->params()->fromRoute('id', 0);
        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCat = $groceryCatManager->getById($groceryCatID);

        $sellManager = new SellManager($this->entityManager);
        $orderAnalytic = $sellManager->getOrderAnalytic();

        return new ViewModel([
            'groceryCat'=>$groceryCat,
            'runDate'=>$this->getDate($groceryCat->getDay()),
            'orderAnalytic'=>$orderAnalytic
        ]);
    }

    public function catMapAction(){
        $groceryCatID = $this->params()->fromRoute('id', 0);
        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCat = $groceryCatManager->getById($groceryCatID);

        $configManage = new ConfigManager();

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariable('groceryCat',$groceryCat);
        $view->setVariable('runDate',$this->getDate($groceryCat->getDay()));
        $view->setVariable('geoKey',$configManage->getGeoKey());
        return $view;
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
    public function mapAction(){

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


    public function mapLocationAction(){

        $request = $this->getRequest();
        if($request->isPost()){
            $lat=$request->getPost('lat');
            $lng=$request->getPost('lng');

            $groceryList = $this->groceryManager->getGroceryLocation($lat,$lng);
            $result=array();
            foreach ($groceryList as $groceryitem){
                $o["name"]=$groceryitem->getGroceryName();
                $o["user"]=$groceryitem->getGroceryCat()->getUser()->getUsername();
                $o["id"]=$groceryitem->getId();
                $o["lat"]=$groceryitem->getLat();
                $o["lng"]=$groceryitem->getLng();
                $result[]=$o;
            }
            return new JsonModel($result);
        }else{
            $configManage = new ConfigManager();
            $view = new ViewModel([
                'geoKey'=>$configManage->getGeoKey()
            ]);
            $view->setTerminal(true);
            return $view;
        }
    }
}