<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-15
 * Time: 15:55
 */

namespace Admin\Controller;


use Admin\Service\AdminManager;
use Doctrine\ORM\EntityManager;
use Sell\Entity\SellOrder;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\SuldeAdminController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AdminController extends SuldeAdminController
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
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
//            $fromDate=date("Y-m-d", strtotime("first day of this month"));
        }

        $sellManager = new SellManager($this->entityManager);
        $sellOrder = $sellManager->getSellOrderByDate($fromDate,$toDate);
        $totalProfit=0;
        $totalRevenue=0;
        //$totalRevenueDay=0;
        //$totalProfitDay=0;
        $orderNumber=count($sellOrder);
        $arr=array();
        $arrLine =array();
        foreach ($sellOrder as $k=>$order){
            $totalRevenue+= $order->getTotalAmountToPaid();
            $totalProfit+=$order->getProfit();
            $arr= array_merge_recursive(
                $arr,
                $order->getRevenueByProductCat()
            );

//            $payDate = $order->getPayDate()->format('Y-m-d');
//            $totalRevenueDay=$order->getTotalPrice();
//            $totalProfitDay=$order->getProfit();

//            $arrLine[$payDate]['revenue']= @$arrLine[$payDate]['revenue']+$totalRevenueDay;
//            $arrLine[$payDate]['profit']= @$arrLine[$payDate]['profit']+$totalProfitDay;

        }
        $arrRevenueByProductCat=$this->groupRevenueByProductCat($arr);

        //order analytic
        $orderAnalytic = $sellManager->getOrderAnalytic();
//        print_r($orderAnalytic);
        //$firstDayOfPreviousMonth=date("Y-n-j", strtotime("first day of previous month"));
        //echo date("Y-n-j", strtotime("last day of previous month"));
        //echo '/';
        //echo date("Y-n-j", strtotime("last day of this month"));
        //echo date("Y-n-j", strtotime("first day of this month"));

        //$sellOrder = $sellManager->getSellOrderByDate($firstDayOfPreviousMonth);

        return new ViewModel([
            'totalRevenue' => $totalRevenue,
            'totalProfit'=>$totalProfit,
            'orderNumber'=>$orderNumber,
            'arrRevenueByProductCat'=>$arrRevenueByProductCat,
//            'arrLine'=>$arrLine,
            'strReportDate'=>$strReportDate,
            'orderAnalytic'=>$orderAnalytic
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
}