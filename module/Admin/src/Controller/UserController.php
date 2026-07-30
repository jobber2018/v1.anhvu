<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Service\AdminManager;
use DateTime;
use Doctrine\ORM\EntityManager;
use Grocery\Service\GroceryManager;
use Hotels\Service\HotelManage;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\SuldeUserController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UserController extends SuldeUserController
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {

//        $dateNow=date("Y-m-d");

        $uid=$this->userInfo->getId();
        $sellManager = new SellManager($this->entityManager);
        $totalRevenue=0;
        $unpaid = 0;

        $dateNow=strtotime(date("Y-m-d"));
        //get all order
        $orderNew = array();
        $orderUnpaid = array();
        $orderDelivery = array();
        $orderCompleted = array();
        $orderDraft = array();
        $orderCancel = array();
        $orderNumberNew=0;
        $sellOrder = $sellManager->getSellOrderStatusById($uid);
        foreach ($sellOrder as $k=>$order){

            if($order->getStatus()==3){
                if(strtotime($order->getPayDate()->format('Y-m-d'))==$dateNow){
                    $totalRevenue+= $order->getTotalAmountToPaid();
                    $orderCompleted[]=$order;
                }
            }
            if($order->getStatus()==1 || $order->getStatus()==11|| $order->getStatus()==111){
                $orderNew[]=$order;
                //ngay hien tai
                if(strtotime($order->getCreatedDate()->format('Y-m-d'))==$dateNow){
                    $orderNumberNew++;
                };
            }else if($order->getStatus()==31 || $order->getStatus()==21)
                $orderUnpaid[]=$order;
            else if($order->getStatus()==2)
                $orderDelivery[]=$order;
            else if($order->getStatus()==-1 || $order->getStatus()==-2)
                $orderDraft[]=$order;
            else if($order->getStatus()==0)
                $orderCancel[]=$order;
        }

        //phan tich don hang theo khach hang
        //$orderAnalytic = $sellManager->getOrderAnalytic();

        $today = date('Y-m-d');
        //echo $today.'=';
        $back3Week=date("Y-m-d",strtotime(date("Y-m-d", strtotime($today)) . " -3 week"));
        //echo $back3Week;
        $groceryManager = new GroceryManager($this->entityManager);
        $uid = $this->userInfo->getId();
//        $uid=12;
//        $groceryInOut = $groceryManager->getCheckInByUserAndDate($uid, $back3Week, $today);
/*
        $inOut=array();
        foreach ($groceryInOut as $k=>$item){
            $inOut[Common::formatDate($item->getCreatedDate())]
        }*/

        return new ViewModel([
            'totalRevenue' => $totalRevenue,
            'unpaid' => $unpaid,
            'orderNumberNew'=>$orderNumberNew,
            'orderNew'=>$orderNew,
            'orderUnpaid'=>$orderUnpaid,
            'orderCompleted'=>$orderCompleted,
            'orderDelivery'=>$orderDelivery,
            'orderDraft'=>$orderDraft,
            'orderCancel'=>$orderCancel,
            //'orderAnalytic'=>$orderAnalytic,
            'uId'=>$uid,
//            'groceryInOut'=>$groceryInOut
        ]);
    }

    public function reportAction()
    {
        $fDate = $this->params()->fromQuery('fd',0);
        $tDate = $this->params()->fromQuery('td',0);

        if($fDate && $tDate){
            $toDate=$tDate;
            $fromDate=$fDate;
            $strReportDate='Từ: '.$fromDate . ' đến '. $toDate;
        }else{
            $toDate=date("Y-m-d");
            $fromDate=date("Y-m-d");
            $strReportDate='Ngày '.$fromDate;
        }

        $uid=$this->userInfo->getId();
        $sellManager = new SellManager($this->entityManager);
        $sellOrder = $sellManager->getSellOrderByDateByUser($uid,$fromDate,$toDate);

        $totalRevenue=0;
        $orderNumber=count($sellOrder);
        $arr=array();
        $arrLine =array();
        foreach ($sellOrder as $k=>$order){
            $totalAmountToPaid = $order->getTotalAmountToPaid();
            $totalRevenue+= $totalAmountToPaid;
            $arr= array_merge_recursive(
                $arr,
                $order->getRevenueByProductCat()
            );

            $payDate = $order->getPayDate()->format('Y-m-d');
            $totalRevenueDay=$totalAmountToPaid;

            $arrLine[$payDate]['revenue']= @$arrLine[$payDate]['revenue']+$totalRevenueDay;

        }
        $arrRevenueByProductCat=$this->groupRevenueByProductCat($arr);

        return new ViewModel([
            'totalRevenue' => $totalRevenue,
            'orderNumber'=>$orderNumber,
            'arrRevenueByProductCat'=>$arrRevenueByProductCat,
            'arrLine'=>$arrLine,
            'strReportDate'=>$strReportDate
        ]);
    }

    public function groupRevenueByProductCat($arr){
        $arrCat=array();
        foreach ($arr as $k=>$v){
            if(@$arrCat[$v["id"]]){
                $arrCat[$v["id"]]["revenue"]=$arrCat[$v["id"]]["revenue"]+$v["revenue"];
            }else{
                $arrCat[$v["id"]]=$v;
            }
        }
        return $arrCat;
    }

    public function activityAction(){
        try{
            $adminManager = new AdminManager($this->entityManager);
            $activity = $adminManager->getActivityDateNow();
            $arr=[];
            foreach ($activity as $activityItem){
                $item["id"]=$activityItem->getId();
//                $item["type"]=$activityItem->getTitle();
                $item["title"]=$activityItem->getTitle();
                $item["seen"]=$this->checkUserSeen($activityItem->getSeen());
                $item["created_date"]=Common::formatDateTime($activityItem->getCreatedDate());
                $item["username"]=$activityItem->getUser()->getFullname();
                $arr[]=$item;
            }
            $result["success"]=1;
            $result["data"]=$arr;
            return new JsonModel($result);

        }catch (\Exception $e){
            $result["success"]=0;
            $result["message"]=$e->getMessage();
            return new JsonModel($result);
        }
    }

    public function messageAction(){
        try{
            $adminManager = new AdminManager($this->entityManager);
            $messageCrm = $adminManager->getMessage();
            $arr=[];
            foreach ($messageCrm as $messageItem){
                $grocery = $messageItem->getGrocery();
                $item["id"]=$messageItem->getId();
                $item["grocery_name"]=$grocery->getGroceryName();
                $item["grocery_id"]=$grocery->getId();
                $item["note"]=$messageItem->getNote();
                $item["created_date"]=Common::formatDateTime($messageItem->getCreatedDate());
                $item["username"]=$messageItem->getUser()->getFullname();
                $arr[]=$item;

            }
            $result["success"]=1;
            $result["data"]=$arr;
            return new JsonModel($result);

        }catch (\Exception $e){
            $result["success"]=0;
            $result["message"]=$e->getMessage();
            return new JsonModel($result);
        }
    }

    private function checkUserSeen($p_seenString){
        $arrUserSeen = explode(",", $p_seenString);
        $uid=$this->userInfo->getId();
        foreach ($arrUserSeen as $k=>$v){
            if($v==$uid) return 1;
        }
        return 0;
    }
    public function activityReadAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            $activityId = $request->getPost("id");
            try {
                $adminManager = new AdminManager($this->entityManager);
                $activity = $adminManager->getActivityId($activityId);
                $seen = $this->userInfo->getId().','.$activity->getSeen();
                $activity->setSeen($seen);//read
                $this->entityManager->persist($activity);
                $this->entityManager->flush();
                $result["success"] = 1;
            } catch (\Exception $e) {
                $result["success"] = 0;
                $result["message"] = $e->getMessage();

            }
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
                        'user_id' => $order['user_id'] ?? null,
                        'user_name' => $order['user_name'] ?? null,
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
            return new JsonModel($this->topRiskCustomers($customers,20));
        }
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
                    $customerRiskScore,
                'user_id' =>
                    $customer['user_id'] ?? null,
                'user_name' =>
                    $customer['user_name'] ?? null,
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
