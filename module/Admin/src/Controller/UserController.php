<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Admin\Service\AdminManager;
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
        $orderAnalytic = $sellManager->getOrderAnalytic();

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
            'orderAnalytic'=>$orderAnalytic,
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
}
