<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Grocery\Controller;

use Admin\Service\AdminManager;
use DateTime;
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


    public function topRiskCustomersAction(){
        $request = $this->getRequest();
        if($request->isPost()){
        $customers=[];
        $sellManager = new SellManager($this->entityManager);

        // Ngày cuối cùng của tháng hiện tại
        $lastDayCurrentMonth = new DateTime('last day of this month');
        $lastDayCurrentMonth=$lastDayCurrentMonth->format('Y-m-d');

        // Ngày đầu tiên của 6 tháng trước
        $date6MonthsAgo = new DateTime('first day of 11 months ago');
        $date6MonthsAgo=$date6MonthsAgo->format('Y-m-d');

        //echo $date6MonthsAgo .'->'. $lastDayCurrentMonth;

        $orders = $sellManager->getOrderAnalytic($date6MonthsAgo, $lastDayCurrentMonth);

        foreach ($orders as $order){
            $customerName = $order['customer_name'];
            $customerId=$order['customer_id'];
            if (!isset($customers[$customerId])) {
                $customers[$customerId] = [
                    'id' => $customerId,
                    'name' => $customerName,
                    'vip' => $order['vip'],
                    'credit' => $order['credit'],
                    'price_sensitive' => $order['price_sensitive'],
                    'orders'=>[]
                ];
            }
            $customers[$customerId]['orders'][]=array(
                'date'=>$order['created_date']->format('Y-m-d'),
                'amount'=>$order['amount']
            );
            unset($sellOrder, $grocery);
        }
        $this->entityManager->clear();
        //print_r($customers);
            /*$grocerys = $this->groceryManager->getVipCustomers();
            foreach ($grocerys as $grocery) {
                $orders = $grocery->getSellOrder();
                $orderData=[];
                if($orders){
                    foreach ($orders as $orderItem) {
                        $order=[
                            'date'=>$orderItem->getCreatedDate()->format('Y-m-d'),
                            'amount'=>$orderItem->getTotalAmountToPaid()
                        ];
                        $orderData[]=$order;
                    }

                    $customer=[
                        'id' => $grocery->getId(),
                        'name' => $grocery->getGroceryName(),
                        'vip' => $grocery->getVip(),
                        'credit' => $grocery->getCredit(),
                        'price_sensitive' => $grocery->getPriceSensitive(),
                        'orders'=>$orderData
                    ];

                    $customers[]=$customer;
                }
            }*/
            return new JsonModel($this->topRiskCustomers($customers,20));
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


    function topRiskCustomers($customers, $limit = 20)
    {
        $today = strtotime(date('Y-m-d'));

        $result = [];

        foreach ($customers as $customer) {

            $orders = $customer['orders'] ?? [];

            if (count($orders) < 10) {
                continue;
            }

            // ========================
            // sort order by date
            // ========================

            usort($orders, function ($a, $b) {
                return strtotime($a['date'])
                    <=>
                    strtotime($b['date']);
            });

            $dates = [];
            $totalRevenue = 0;

            foreach ($orders as $order) {

                $dates[] =
                    strtotime($order['date']);

                $totalRevenue +=
                    ($order['amount'] ?? 0);
            }

            // ========================
            // CHU KỲ NHẬP
            // ========================

            $cycles = [];

            for (
                $i = 1;
                $i < count($dates);
                $i++
            ) {

                $cycles[] =
                    ($dates[$i]
                        - $dates[$i - 1])
                    / 86400;
            }

            if (count($cycles) == 0) {
                continue;
            }

            $avgCycle =
                array_sum($cycles)
                / count($cycles);

            /* ========================
            stability: là chỉ số từ 0–100%, phản ánh mức độ đều đặn của chu kỳ nhập hàng.
            Theo kinh nghiệm với ngành tạp hóa, mình sẽ chia như sau:
            Stability | Đánh giá | Ý nghĩa
            >85%      | Rất ổn định |Có thể dự đoán chính xác ngày nhập
            70–85%    | Ổn định | Dùng để cảnh báo mất khách
            50–70%    | Trung bình | Chỉ nên tham khảo
            30–50%    | Không ổn định | Cảnh báo dễ bị nhiễu
            <30%      | Rất thất thường | Gần như không dùng được để dự báo
            ========================**/

            $stability = 0;

            if (count($cycles) > 1) {

                $variance = 0;

                foreach ($cycles as $cycle) {

                    $variance +=
                        pow(
                            $cycle - $avgCycle,
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
                        1 - ($std / $avgCycle)
                    );
            }

            // ========================
            // last order
            // ========================

            $lastDate =
                end($dates);

            $lastOrderDays =
                floor(
                    ($today - $lastDate)
                    / 86400
                );

            // ========================
            // overdue: thời gian quá chu kỳ nhập
            // ========================

            $overdue =
                max(
                    0,
                    $lastOrderDays
                    - $avgCycle
                );

            // ========================
            // risk
            // ========================

            $risk = 'LOW';

            if (
                $overdue/$avgCycle
                >
                2
            ) {

                $risk = 'HIGH';

            } elseif (
                $overdue > 0
            ) {

                $risk = 'MEDIUM';
            }

            // ========================
            // customer risk score
            // ========================

            $customerRiskScore = 0;

            // overdue
            $customerRiskScore +=
                min(
                    30,
                    (
                        $overdue
                        /
                        max(1, $avgCycle)
                    ) * 30
                );

            // stability
            $customerRiskScore +=
                ($stability * 30);

            // revenue
            if (
                $totalRevenue >= 150000000
            ) {
                $customerRiskScore += 30;

            } elseif (
                $totalRevenue >= 100000000
            ) {
                $customerRiskScore += 20;
            }elseif (
                $totalRevenue >= 50000000
            ) {
                $customerRiskScore += 10;
            }

            if ($overdue / $avgCycle > 2)
                $customerRiskScore+=10;

            // vip
            if (
                $customer['vip']
                ?? false
            ) {
                $customerRiskScore += 10;
            }

            // no credit
            if (
                !($customer['credit']
                    ?? false)
            ) {
                $customerRiskScore += 10;
            }

            // price sensitive
            if (
                $customer['price_sensitive']
                ?? false
            ) {
                $customerRiskScore += 10;
            }

            $customerRiskScore =
                min(
                    100,
                    round(
                        $customerRiskScore
                    )
                );

            // ========================
            // bỏ khách an toàn
            // ========================

            if (
                $customerRiskScore < 30
            ) {
                continue;
            }

            // ========================
            // bỏ khách overdue_days < avg_cycle_days
            // ========================

            if (
                $overdue < $avgCycle
            ) {
                continue;
            }

            // ========================
            // output
            // ========================

            $result[] = [

                'customer_id' =>
                    $customer['id'],

                'customer_name' =>
                    $customer['name'],

                'vip' =>
                    $customer['vip']
                    ?? false,

                'credit' =>
                    $customer['credit']
                    ?? false,

                'price_sensitive' =>
                    $customer['price_sensitive']
                    ?? false,

                'orders' =>
                    count($orders),

                'revenue' =>
                    $totalRevenue,

                'avg_cycle_days' =>
                    round(
                        $avgCycle,
                        1
                    ),

                'stability' =>
                    round(
                        $stability * 100,
                        1
                    ),

                'last_order_days' =>
                    $lastOrderDays,

                'overdue_days' =>
                    round(
                        $overdue,
                        1
                    ),

                'risk' =>
                    $risk,

                'customer_risk_score' =>
                    $customerRiskScore
            ];
        }

        // ========================
        // sort risk desc
        // ========================

        usort(
            $result,
            function ($a, $b) {

                return
                    $b['customer_risk_score']
                    <=>
                    $a['customer_risk_score'];
            }
        );

        // ========================
        // top N
        // ========================

        $result =
            array_slice(
                $result,
                0,
                $limit
            );

        return [

            'date' =>
                date('Y-m-d'),

            'total' =>
                count($result),

            'customers' =>
                $result
        ];
    }
}