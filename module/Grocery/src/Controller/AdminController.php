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
use Sulde\Service\Common\Define;
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
        $request = $this->getRequest();
        if($request->isPost()) {
            $keyword = $this->params()->fromPost('search')['value'];
            $length = $this->params()->fromPost('length', Define::ITEM_PAGE_COUNT);
            $start = $this->params()->fromPost('start', 0);
            $draw = $this->params()->fromPost('draw', 1);
            
            $grocerys = $this->groceryManager->search($keyword, $length, $start);
            $groceryResults = array();
            foreach ($grocerys as $groceryItem) {
                $tmp['id'] = $groceryItem->getId();
                $tmp['name'] = $groceryItem->getGroceryName();
                $tmp['mobile'] = $groceryItem->getMobile();
                $tmp['address'] = $groceryItem->getAddress();
                $tmp['owner_name'] = $groceryItem->getOwnerName();
                $tmp['staff_in_charge'] = $groceryItem->getGroceryCat() ? $groceryItem->getGroceryCat()->getUser()->getFullName() : '';
                $tmp['check_in_date'] = Common::formatDateTime($groceryItem->getCheckInDate());
                $tmp['pay_total']=$groceryItem->getPayTotal()/1000;
                $groceryResults[]=$tmp;
            }
            $result['start']=$start;
            $result['recordsTotal']=count($grocerys);
            $result['recordsFiltered']=count($grocerys);
            $result['data']=$groceryResults;
            return new JsonModel($result);
        }else{            
            return new ViewModel();
        }        
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

    /**
     * Phân tích sản phẩm bán chạy, tần suất mua hàng, chu kỳ mua hàng, rủi ro hết hàng
     * @return JsonModel
     */
    public function analyzeProductAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            $groceryId=$request->getPost('gid');
            $grocery = $this->groceryManager->getById($groceryId);
            $orders = $grocery->getSellOrder();

            $orderData = [];

            foreach ($orders as $order) {     
                if($order->getStatus() != Define::_ORDER_CANCEL_STATUS) {
                    $products = [];
                    foreach ($order->getSell() as $sell){
                        if($sell->getProduct()->getActive()==1){
                            $product['id']=$sell->getProduct()->getId();                    
                            $product['name']=$sell->getProduct()->getName().' | '.$sell->getProduct()->getWeight();;
                            $product['quantity']=$sell->getQuantity();
                            $products[] = $product;
                        }                    
                    }

                    $orderData[] = [                    
                        'date' => $order->getCreatedDate()->format('Y-m-d'),
                        'products' => $products
                    ];
                }                
            }

            $result = $this->analyzeProducts($orderData);
            return new JsonModel($result);
        }
    }

    function analyzeProducts($orders)
    {        
        $products = [];
        $today = strtotime(date('Y-m-d'));

        foreach ($orders as $order) {

            $date = strtotime($order['date']);

            foreach ($order['products'] as $product) {

                $id = $product['id'];

                if (!isset($products[$id])) {

                    $products[$id] = [
                        'id' => $id,
                        'name' => $product['name'],

                        'frequency' => 0,
                        'quantity' => 0,

                        'last_date' => $date,
                        'purchase_dates' => []
                    ];
                }

                $products[$id]['frequency']++;

                $products[$id]['quantity']
                    += $product['quantity'];

                if ($date > $products[$id]['last_date']) {
                    $products[$id]['last_date'] = $date;
                }

                $products[$id]['purchase_dates'][] = $date;
            }
        }

        $maxFreq = max(array_column($products,'frequency'));
        $maxQty  = max(array_column($products,'quantity'));

        $result = [];

        foreach ($products as $product) {

            // ===================
            // RECENCY
            // ===================

            $daysFromLast =
                floor(
                    ($today-$product['last_date'])
                    /86400
                );

            $rScore =
                1/(1+$daysFromLast);

            // ===================
            // FREQUENCY
            // ===================

            $fScore =
                $product['frequency']
                /$maxFreq;

            // ===================
            // MONETARY
            // ===================

            $mScore =
                $product['quantity']
                /$maxQty;

            // ===================
            // PURCHASE CYCLE
            // ===================

            sort($product['purchase_dates']);

            $cycles = [];

            for(
                $i=1;
                $i<count($product['purchase_dates']);
                $i++
            ){

                $cycles[] =
                    (
                        $product['purchase_dates'][$i]
                        -
                        $product['purchase_dates'][$i-1]
                    )/86400;
            }

            $avgCycle = 0;

            if(count($cycles)>0){
                $avgCycle =
                    array_sum($cycles)
                    /count($cycles);
            }

            // ===================
            // STABILITY
            // ===================

            $stability = 0;

            if(count($cycles)>1){

                $variance = 0;

                foreach($cycles as $c){
                    $variance +=
                        pow(
                            $c-$avgCycle,
                            2
                        );
                }

                $variance /=
                    count($cycles);

                $std =
                    sqrt($variance);

                $stability =
                    max(
                        0,
                        1-($std/$avgCycle)
                    );
            }

            // ===================
            // NEXT ORDER
            // ===================

            $nextOrderIn = null;
            $overdue = 0;

            if($avgCycle>0){

                $expected =
                    $product['last_date']
                    +
                    ($avgCycle*86400);

                $nextOrderIn =
                    floor(
                        ($expected-$today)
                        /86400
                    );

                if($nextOrderIn<0){
                    $overdue =
                        abs($nextOrderIn);
                }
            }

            // ===================
            // RISK
            // ===================

            $risk = 'LOW';

            if($avgCycle>0){

                if(
                    $overdue
                    >
                    ($avgCycle*0.5)
                ){
                    $risk='HIGH';
                }
                elseif(
                    $overdue>0
                ){
                    $risk='MEDIUM';
                }
            }

            // ===================
            // FINAL SCORE
            // ===================

            $score =
                (
                    $fScore*0.30
                )+
                (
                    $mScore*0.20
                )+
                (
                    $rScore*0.10
                )+
                (
                    $stability*0.40
                );

            $result[] = [

                'id' =>
                    $product['id'],

                'name' =>
                    $product['name'],

                'R_days' =>
                    $daysFromLast,

                'F_orders' =>
                    $product['frequency'],

                'M_quantity' =>
                    $product['quantity'],

                'avg_cycle_days' =>
                    round($avgCycle,1),

                'stability' =>
                    round(
                        $stability*100,
                        1
                    ),

                'next_order_in' =>
                    $nextOrderIn,

                'overdue_days' =>
                    $overdue,

                'risk' =>
                    $risk,

                'score' =>
                    round(
                        $score*100,
                        2
                    )
            ];
        }

        usort(
            $result,
            function($a,$b){
                return
                    $b['score']
                    <=>
                    $a['score'];
            }
        );

        return $result;
    }
}