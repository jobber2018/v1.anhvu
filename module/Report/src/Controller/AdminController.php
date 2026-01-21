<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Report\Controller;

use DateTime;
use GroceryCat\Service\GroceryCatManager;
use Product\Service\ProductManager;
use Report\Entity\AccountingDiary;
use Report\Entity\Report;
use Report\Form\ReportForm;
use Report\Service\AccountingDiaryManager;
use Report\Service\CostRevenueManager;
use Report\Service\ReportManager;
use Doctrine\ORM\EntityManager;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\SuldeAdminController;
use Users\Entity\User;
use Werehouse\Service\WerehouseManager;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $reportManager;

    public function __construct(EntityManager $entityManager, ReportManager $reportManager)
    {
        $this->entityManager = $entityManager;
        $this->reportManager = $reportManager;
    }


    /**
     * @return ViewModel
     */
    public function indexAction(){
        return new ViewModel();
    }

    /**
     * @return ViewModel
     */
    public function timeAction(){
        $fDate = $this->params()->fromQuery('fd',0);
        $tDate = $this->params()->fromQuery('td',0);
        $recalculate = $this->params()->fromQuery('recalculate',0);

        if($fDate && $tDate){
            $toDate=$tDate;
            $fromDate=$fDate;
        }else{
            $toDate=date("Y-m-d");
            $fromDate=date("Y-m-d", strtotime("first day of this month"));
        }

        //list doanh thu theo ngay
        $costRevenueManager = new CostRevenueManager($this->entityManager);
        $costRevenue = $costRevenueManager->getByDate($fromDate, $toDate);

        $sellManager = new SellManager($this->entityManager);

        //tinh toan lai doanh thu/loi nhuan cac ngay theo lua chonj
        if($recalculate==1){
            try {
                //duyet ngay tinh doanh thu
                foreach ($costRevenue as $costRevenueItem){
                    $createdDate = $costRevenueItem->getDate();
                    //get order hoan thanh trong ngay dang duyet
                    $sellOrder = $sellManager->getSellOrderByDate($createdDate->format("Y-m-d"),$createdDate->format("Y-m-d"));
                    $total_revenue=0;
                    $total_order_price=0;
                    $total_order_product_discount=0;
                    $total_cost=0;
                    $total_order_discount=0;
                    $total_discount=0;
                    $order_discount_id=array();
                    //duyet don hang theo ngay
                    foreach ($sellOrder as $order){
                        $total_order_discount+=$order->getDiscount();
                        $is_product_discount=0;
                        //duyet chi tiet don hang
                        foreach ($order->getSell() as $sellItem){
                            $priceValue = $sellItem->getPriceValue();
                            $qty=$sellItem->getQuantity();
                            $packUnit=$sellItem->getPackUnit();
                            $discount=$sellItem->getDiscount();
                            $cost=$sellItem->getCost();//gia von tinh theo unit
                            //mua theo thung
                            if($sellItem->isPack()){
                                $qtySale=$sellItem->isPack();
                                $price=$packUnit*$priceValue;//gia ban theo thung
                                $cost=$packUnit*$cost;//gia von theo thung
                            }else{
                                $qtySale=$qty;
                                $price=$priceValue;//gia ban le
                            }
                            $total_cost+=$cost*$qtySale;
                            $total_order_price+=$price*$qtySale;
                            $total_order_product_discount+=$discount*$qtySale;

                            if($discount>0) $is_product_discount=1;
                        }
                        //id don hang discount
                        if($is_product_discount>0 || $order->getDiscount()>0)
                            $order_discount_id[]=$order->getId();
                    }

                    $total_discount+=($total_order_product_discount+$total_order_discount);
                    $total_revenue+=($total_order_price - $total_order_product_discount - $total_order_discount);
                    $total_order_completed = count($sellOrder);

                    $costRevenueItem->setRevenue($total_revenue);
                    $costRevenueItem->setCost($total_cost);
                    $costRevenueItem->setDiscount($total_discount);
                    $costRevenueItem->setOrderDiscountId(implode(",",$order_discount_id));
                    $costRevenueItem->setOrderCompleted($total_order_completed);
                    $this->entityManager->flush();

                }
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }

        //doanh thu ngay hien tai
        $sellOrder = $sellManager->getSellOrderByDate(date("Y-m-d"),date("Y-m-d"));
        $totalProfit=0;
        $totalRevenue=0;
        $orderNumber=count($sellOrder);
        $arr=array();
        foreach ($sellOrder as $order){
            $totalRevenue+= $order->getTotalAmountToPaid();
            $totalProfit+=$order->getProfit();
            $arr= array_merge_recursive(
                $arr,
                $order->getRevenueByProductCat()
            );
        }
        $arrRevenueByProductCat=$this->groupRevenueByProductCat($arr);

        return new ViewModel([
            'costRevenue'=>$costRevenue,
            'totalRevenue' => $totalRevenue,
            'totalProfit'=>$totalProfit,
            'orderNumber'=>$orderNumber,
            'arrRevenueByProductCat'=>$arrRevenueByProductCat,
            'toDate'=>$toDate,
            'fromDate'=>$fromDate
        ]);
    }

    /**
     * @return ViewModel
     */
    public function userAction(){
        $fDate = $this->params()->fromQuery('fd',0);
        $tDate = $this->params()->fromQuery('td',0);

        if($fDate && $tDate){
            $toDate=$tDate;
            $fromDate=$fDate;
        }else{
            $toDate=date("Y-m-d");
            $fromDate=date("Y-m-d", strtotime("first day of this month"));
        }

        $sellManager = new SellManager($this->entityManager);
        $sellOrder = $sellManager->getSellOrderByDate($fromDate,$toDate);
//        $totalProfit=0;
//        $totalRevenue=0;

        $arrUser=array();
        foreach ($sellOrder as $order){
            $uid = $order->getUser()->getId();
            $fullname = $order->getUser()->getFullname();
            $revenue = $order->getTotalAmountToPaid();
//            $profit = $order->getProfit();
//            $profit = 0;
            $arrUser[$uid]["id"]=$uid;
            $arrUser[$uid]["name"]=$fullname;

            $arrUser[$uid]["revenue"][$order->getMethod()]=@$arrUser[$uid]["revenue"][$order->getMethod()]+$revenue;

//            $arrUser[$uid]["profit"]=@$arrUser[$uid]["profit"]+$profit;
        }
//        print_r($arrUser);
        return new ViewModel([
            'arrUser'=>$arrUser,
            'fdate'=>$fromDate,
            'tdate'=>$toDate
        ]);
    }
    /**
     * @return ViewModel
     */
    public function productAction(){
        $productManager = new ProductManager($this->entityManager);
        $product = $productManager->getAll();
        return new ViewModel([
            'product'=>$product
        ]);
    }

    /**
     * @return ViewModel
     */
    public function financeAction(){
        $fDate = $this->params()->fromQuery('fd',0);
        $tDate = $this->params()->fromQuery('td',0);

        if($fDate && $tDate){
            $toDate=$tDate;
            $fromDate=$fDate;
        }else{
            $toDate=date("Y-m-d");
            $fromDate=date("Y-m-d", strtotime("first day of -5 month"));
        }
        $arrRevenueProfit=array();
        //doanh thu & loi nhuan 6 thang gan nhat
        $costRevenueManager = new CostRevenueManager($this->entityManager);
        $costRevenue = $costRevenueManager->getByDate($fromDate, $toDate);
        foreach ($costRevenue as $costRevenueItem) {
            $date = $costRevenueItem->getDate()->format('M/Y');
            $revenue=$costRevenueItem->getRevenue();
            $cost=$costRevenueItem->getCost();
            $profit=$revenue-$cost;
            @$arrRevenueProfit[$date]["revenue"]+=$revenue;
            @$arrRevenueProfit[$date]["profit"]+=$profit;
        }

        //chi phi 5 thang gan nhat
        //154:(Chi phí nhân công trực tiếp): tien luong
        //211:(TSCĐ hữu hình) -chi phi khau hao tai san
        //642:(Chi phí quản lý doanh nghiệp) - xăng xe, mạng ....
        //641:(Chi phí bán hàng) - Thuê nhà làm cơ sở bán hàng, kho chứa hàng bán
        $accountingDiaryManager = new AccountingDiaryManager($this->entityManager);
        $accountingDiaryList = $accountingDiaryManager->getByDate($fromDate,$toDate,1);

        //chi phi theo thang
        $arrExpense = array();
        foreach ($accountingDiaryList as $accountingDiary){
            $date = $accountingDiary->getDate()->format('M/Y');
            $account = $accountingDiary->getTk();
            $money = $accountingDiary->getMoney();
            @$arrExpense[$date][$account]+=$money;
            @$arrExpense[$date]['total']+=$money;
        }

        //add loi nhuan theo thang tinh duoc o tren

        foreach ($arrRevenueProfit as $k=>$revenueProfit){
            if(@$arrExpense[$k]){
                $arrRevenueProfit[$k]['expense']=$arrExpense[$k];
            }
        }

        return new ViewModel([
            'fdate'=>$fromDate,
            'tdate'=>$toDate,
            'arrRevenueProfit'=>$arrRevenueProfit
        ]);
    }
    /**
     * @return ViewModel
     */
    public function werehouseAction(){
        $fDate = $this->params()->fromQuery('fd',0);
        $tDate = $this->params()->fromQuery('td',0);

        if($fDate && $tDate){
            $toDate=$tDate;
            $fromDate=$fDate;
        }else{
            $toDate=date("Y-m-d");
            $fromDate=date("Y-m-d", strtotime("first day of this month"));
        }

        //tinh gia tri ton kho trong ky
        $productManager = new ProductManager($this->entityManager);
        $products = $productManager->getAll();

        $inventoryValue=0;
        foreach ($products as $product) {
            $averagePrice=$product->getAveragePrice();
            $inventory=$product->getInventory();
            $inventoryValue+=$averagePrice*$inventory;
        }

        //nhap trong ky
        $werehouseManager = new WerehouseManager($this->entityManager);
        $werehouseOrder = $werehouseManager->getOrderByDate($fromDate, $toDate);
        $totalPriceInput=0;
        foreach ($werehouseOrder as $werehouse){
            $totalPriceInput += $werehouse->getTotalPrice();
        }

        //xuat trong ky
        $sellManager = new SellManager($this->entityManager);
        $sellOrder = $sellManager->getSellOrderByDate($fromDate,$toDate);
        $totalPriceOutput=0;
        foreach ($sellOrder as $order){
            $totalPriceOutput+=$order->getTotalPrice();
        }

        return new ViewModel([
            'fdate'=>$fromDate,
            'tdate'=>$toDate,
            'totalPriceInput'=>$totalPriceInput,
            'totalPriceOutput'=>$totalPriceOutput,
            'inventoryValue'=>$inventoryValue
        ]);
    }


    /**
     * @return ViewModel
     */
    public function categoryAction(){
        $fDate = $this->params()->fromQuery('fd',0);
        $tDate = $this->params()->fromQuery('td',0);

        if($fDate && $tDate){
            $toDate=$tDate;
            $fromDate=$fDate;
        }else{
            $toDate=date("Y-m-d");
            $fromDate=date("Y-m-d", strtotime("first day of this month"));
        }

        $sellManager = new SellManager($this->entityManager);
        $sellOrder = $sellManager->getSellOrderByDate($fromDate,$toDate);

        $arrGrocery=array();
        foreach ($sellOrder as $k=>$order){
            $sells = $order->getSell();
            foreach ($sells as $sell){
                $price = $sell->getPrice()->getPrice();
                $quantity = $sell->getQuantity();
                $product= $sell->getProduct();
                $productCat = $product->getProductCat();
                $proUnit = $product->getUnit()->getName();
                $proCatId=$productCat->getId();
                $arrProCat[$proCatId]["name"]=$productCat->getName();
                $arrProCat[$proCatId]["price"]=@$arrProCat[$proCatId]["price"]+$price*$quantity;
                $arrProCat[$proCatId]["quantity"]=@$arrProCat[$proCatId]["quantity"]+$quantity;
                $arrProCat[$proCatId]["unit"]=$proUnit;
            }
        }
        return new ViewModel([
            'arrProCat'=>$arrProCat,
            'fdate'=>$fromDate,
            'tdate'=>$toDate
        ]);
    }
    /**
     * @return ViewModel
     */
    public function groceryAction(){
        $fDate = $this->params()->fromQuery('fd',0);
        $tDate = $this->params()->fromQuery('td',0);

        if($fDate && $tDate){
            $toDate=$tDate;
            $fromDate=$fDate;
        }else{
            $toDate=date("Y-m-d");
            $fromDate=date("Y-m-d", strtotime("first day of this month"));
        }

        $sellManager = new SellManager($this->entityManager);
//        $sellOrder = $sellManager->getSellOrderByDate($fromDate,$toDate);
        $sellOrder = $sellManager->getTopGrocery($fromDate,$toDate);

//        print_r($sellOrder);

        $arrGrocery=array();
        foreach ($sellOrder as $k=>$order){
            $gid = $order["id"];
            $name = $order["name"];
//            $totalReturn = ($order["total_return"])?$order["total_return"]:0;
            $totalDiscount = ($order["discount"])?$order["discount"]:0;
            $revenue = $order["total_price"]-$totalDiscount;
            $profit = 0;
            $arrGrocery[$gid]["id"]=$gid;
            $arrGrocery[$gid]["name"]=$name;
            $arrGrocery[$gid]["revenue"]=@$arrGrocery[$gid]["revenue"]+$revenue;
            $arrGrocery[$gid]["profit"]=@$arrGrocery[$gid]["profit"]+$profit;
        }

//        $arrGroceryTmp = krsort($arrGrocery);
//        print_r($arrGroceryTmp);
        return new ViewModel([
            'arrGrocery'=>$arrGrocery,
            'fdate'=>$fromDate,
            'tdate'=>$toDate
        ]);
    }


    /**
     * @return ViewModel
     */
    public function diaryAccountingAction(){
        $accId = $this->params()->fromRoute('id',0);
        $request = $this->getRequest();

        $accountingDiaryManager = new AccountingDiaryManager($this->entityManager);

        if($request->isPost()){
            try{
                $id = $request->getPost("id");
                $date = $request->getPost("date");
                $tk = $request->getPost("tk");
                $money = $request->getPost("money");
                $option = $request->getPost("op");
                $summary = $request->getPost("summary");
                $code = $request->getPost("code");

                $date=DateTime::createFromFormat('Y-m-d', $date);

                $user = $this->entityManager->getRepository(User::class)->find($this->userInfo->getId());

                if($id)
                    $accountingDiary = $accountingDiaryManager->getById($id);
                else{
                    $accountingDiary = new AccountingDiary();
                    $accountingDiary->setUser($user);
                    $accountingDiary->setCreatedDate(new \DateTime());
                }

                $accountingDiary->setDate($date);
                $accountingDiary->setTk($tk);
                $accountingDiary->setOp($option);
                $accountingDiary->setMoney($money);

                if($code)
                    $accountingDiary->setCode($code);

                $accountingDiary->setSummary($summary);

                $this->entityManager->persist($accountingDiary);
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage('Đã thêm giao dịch');
            }catch (\Exception $e){

            }
            return $this->redirect()->toRoute('report-admin',['action'=>'diary-accounting']);
        }

        $accountingDiaryList = $accountingDiaryManager->getAll();

        if($accId){
            $accountingDiaryEdit = $accountingDiaryManager->getById($accId);
        }else
            $accountingDiaryEdit = new AccountingDiary();

        return new ViewModel([
            "accountingDiaryList"=>$accountingDiaryList,
            "accountingDiaryEdit"=>$accountingDiaryEdit
        ]);
    }

    /**
     * @return ViewModel
     */
    public function routeAction(){
        $fDate = $this->params()->fromQuery('fd',0);
        $tDate = $this->params()->fromQuery('td',0);
        $routeId = $this->params()->fromRoute('id',0);

        if($fDate && $tDate){
            $toDate=$tDate;
            $fromDate=$fDate;
        }else{
            $toDate=date("Y-m-d");
            $fromDate=date("Y-m-d", strtotime("first day of this month"));
        }

        $groceryCatManager = new GroceryCatManager($this->entityManager);
        $groceryCatList = $groceryCatManager->getAll();

        if($routeId){
            $groceryCat = $groceryCatManager->getById($routeId);
            $groceryCatAnalytic = $groceryCatManager->getCatAnalyticFromToDateByRoute($routeId, $fromDate, $toDate);
        }else{
            $groceryCatAnalytic=$groceryCatManager->getCatAnalyticFromToDate($fromDate, $toDate);
        }

        return new ViewModel([
            "tDate"=>$toDate,
            "fDate"=>$fromDate,
            "groceryCatList"=>$groceryCatList,
            "groceryCatAnalytic"=>$groceryCatAnalytic,
            "groceryCat"=>@$groceryCat
        ]);
    }
    private function groupRevenueByProductCat($arr){
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


    public function zaloAppAction(){
        return new ViewModel();
    }

    public function installAppAction(){
        $zaloApp = $this->reportManager->getZaloInstall();
        $totalGroceryInstall=count($zaloApp);
        //echo $totalGroceryInstall.'<br>';
        $installNotOrder=array();
        $installAppByMonth=array();
        foreach ($zaloApp as $zaloAppItem){
            $grocery = $zaloAppItem->getGrocery();
            $tmp=array();
            $createdDate=$zaloAppItem->getCreatedDate();
            $tmp["id"]=$grocery->getId();
            $tmp["name"]=$grocery->getGroceryName();
            $tmp["address"]=$grocery->getAddress();
            $tmp["install_date"]=Common::formatDate($createdDate);
            $tmp["access_date"]=Common::formatDateTime($zaloAppItem->getAccessDate());

            //cai app nhung chua dat hang (ke ca dat qua admin)
            if(count($grocery->getSellOrder())==0){
                $installNotOrder[]=$tmp;
            }else{
                $customerCreateOrder=0;
                //kiem tra xem khach da dat online chua?
                foreach ($grocery->getSellOrder() as $sellOrderItem){
                    //khach tu len don qua app
                    if($sellOrderItem->getMethod()==-1){
                        $customerCreateOrder=1;
                    }
                }

                if($customerCreateOrder==0) $installNotOrder[]=$tmp;

                @$installAppByMonth[$createdDate->format('m/Y')]=@$installAppByMonth[$createdDate->format('m/Y')]+1;
            }
        }
//        print_r($installAppByMonth);
        return new ViewModel([
            'totalGroceryInstall'=>$totalGroceryInstall,
            'installNotOrder'=>$installNotOrder,
            'installAppByMonth'=>$installAppByMonth
        ]);
    }
}