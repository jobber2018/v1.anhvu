<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Sell\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;
use Grocery\Entity\Grocery;
use Sell\Entity\SellOrderInvoice;
use Sulde\Service\Common\Common;
use Users\Entity\User;

/**
 * @orm\Entity
 * @orm\Table(name="sell_order")
 */

class SellOrder
{

    public function __construct() {
        $this->sell = new ArrayCollection();
        $this->invoice = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="integer", name="total_price") */
    private $total_price;

    /** @orm\Column(type="integer", name="status") */
    private $status;

    /** @orm\Column(type="string", name="note") */
    private $note;

    /** @orm\Column(type="integer", name="method") */
    private $method;

    /** @orm\Column(type="integer", name="discount") */
    private $discount;

    /** @orm\Column(type="string", name="summary") */
    private $summary;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $user;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;
    /** @orm\Column(type="datetime", name="confirmed_date") */
    private $confirmed_date;

    /**
     * @orm\ManyToOne(targetEntity="Grocery\Entity\Grocery", inversedBy="sellOrder" )
     * @orm\JoinColumn(name="grocery_id", referencedColumnName="id")
     */
    private $grocery;

    /**
     * @orm\OneToMany(targetEntity="Sell\Entity\Sell", mappedBy="sellOrder", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="sell_order_id")
     */
    protected $sell;

    /**
     * @orm\OneToMany(targetEntity="Sell\Entity\SellOrderActivity", mappedBy="sellOrder", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="sell_order_id")
     * @orm\OrderBy({"action_time" = "DESC"})
     */
    protected $activity;

    /** @orm\Column(type="datetime", name="pay_date") */
    private $pay_date;

    /** @orm\Column(type="integer", name="pay_method") */
    private $pay_method;

    /** @orm\Column(type="datetime", name="delivered_date") */
    private $delivered_date;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="delivered_by", referencedColumnName="id")
     */
    private $deliveredBy;

    /** @orm\Column(type="datetime", name="canceled_date") */
    private $canceled_date;

    /** @orm\Column(type="string", name="canceled_by") */
    private $canceled_by;

    /** @orm\Column(type="integer", name="zalo_app_id") */
    private $zalo_app_id;

    /** @orm\Column(type="string", name="source") */
    private $source;

    /** @orm\Column(type="string", name="completed_by") */
    private $completed_by;
    /** @orm\Column(type="string", name="unpaid_by") */
    private $unpaid_by;

    /** @orm\Column(type="string", name="delivery_car") */
    private $delivery_car;

    /** @orm\Column(type="string", name="delivery_car_time") */
    private $delivery_car_time;

    /** @orm\Column(type="string", name="pack_number") */
    private $pack_number;

    /** @orm\Column(type="datetime", name="check_date") */
    private $check_date;

    /** @orm\Column(type="string", name="check_by") */
    private $check_by;

    /** @orm\Column(type="string", name="check_assigned") */
    private $check_assigned;

    /** @orm\Column(type="string", name="approval_check_by") */
    private $approval_check_by;
    /** @orm\Column(type="datetime", name="approval_check_date") */
    private $approval_check_date;

    /**
     * @orm\OneToMany(targetEntity="Sell\Entity\SellOrderInvoice", mappedBy="sellOrder", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="sell_order_id")
     */
    protected $invoice;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTotalPrice()
    {
        $totalPrice = 0;
        foreach ($this->getSell() as $sellItem){
            $totalPrice=$totalPrice+ ($sellItem->getPriceValue()*$sellItem->getQuantity());
        }
        return $totalPrice;
//        return $this->total_price;
    }

    /**
     * @param mixed $total_price
     */
    public function setTotalPrice($total_price)
    {
        $this->total_price = $total_price;
    }

    //So tien phai tra cho moi don hang sau khi tru chiet khau
    public function getTotalAmountToPaid()
    {
        $total_order_price=0;
        $total_order_product_discount=0;

        foreach ($this->getSell() as $sellItem){
            $priceValue = $sellItem->getPriceValue();
            $qty=$sellItem->getQuantity();
            $packUnit=$sellItem->getPackUnit();
            $discount=$sellItem->getDiscount();//discount tren 1 san pham hoac 1 thung
            //mua theo thung
            if($sellItem->isPack()){
                $qtySale=$sellItem->isPack();
                $price=$packUnit*$priceValue;//gia ban theo thung
            }else{
                $qtySale=$qty;
                $price=$priceValue;//gia ban le
            }

            $total_order_price+=$price*$qtySale;
            $total_order_product_discount+=$discount*$qtySale;
        }
        return $total_order_price-$total_order_product_discount-$this->getDiscount();
    }

    //kiem tra don hang co giam gia theo san pham khong?
    public function isProductDiscount()
    {
        foreach ($this->getSell() as $sellItem){
            if($sellItem->getDiscount()>0) return 1;
        }
        return 0;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * @param mixed $created_date
     */
    public function setCreatedDate($created_date)
    {
        $this->created_date = $created_date;
    }

    /**
     * @return Grocery
     */
    public function getGrocery()
    {
        return $this->grocery;
    }

    /**
     * @param mixed $grocery
     */
    public function setGrocery($grocery)
    {
        $this->grocery = $grocery;
    }

    /**
     * @return ArrayCollection
     */
    public function getSell()
    {
        return $this->sell;
    }

    /**
     * @param $sell
     */
    public function setSell($sell)
    {
        $this->sell=$sell;
    }

    /**
     * @return mixed
     */
    public function getConfirmedDate()
    {
        return $this->confirmed_date;
    }

    /**
     * @param mixed $confirmed_date
     */
    public function setConfirmedDate($confirmed_date)
    {
        $this->confirmed_date = $confirmed_date;
    }


    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param Sell $sell
     * @return $this
     */
    public function addSell(Sell $sell)
    {
        if (!$this->sell->contains($sell)) {
            $this->sell->add($sell);
//            $sell->setSellOrder(null);
        }
        return $this;
    }
    public function removeSell(Sell $sell)
    {
        if ($this->sell->contains($sell)) {
            $this->sell->removeElement($sell);
            // set the owning side to null (unless already changed)
            if ($sell->getSellOrder() === $this) {
                $sell->setSellOrder(null);
            }
        }
        return $this;
    }
    /**
     * @return mixed
     */
    public function getPayDate()
    {
        return $this->pay_date;
    }

    /**
     * @param mixed $pay_date
     */
    public function setPayDate($pay_date)
    {
        $this->pay_date = $pay_date;
    }

    public function getOrderCode(){
        $odn=$this->getId();
        if(strlen($odn)==1) $odn="ODN000".$odn;
        else if(strlen($odn)==2)$odn="ODN00".$odn;
        else if(strlen($odn)==3)$odn="ODN0".$odn;
        else $odn="ODN".$odn;
        return $odn;
    }

    /**
     * @param $p_sellId
     * @return Sell
     */
    public function getSellId($p_sellId)
    {
        foreach ($this->getSell() as $key=>$sell){
            if($p_sellId==$sell->getId())
                return $sell;
        }
        return 0;
    }

    //loi nhuan theo don hang
    public function getProfit(){

        $total_order_price=0;
        $total_order_product_discount=0;
        $total_cost=0;
        $order_discount=$this->getDiscount();

        foreach ($this->getSell() as $sellItem){
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
        }
        $revenue=$total_order_price-$total_order_product_discount-$order_discount;

        return $revenue-$total_cost;
    }

    public function getRevenueByProductCat(){
        $arrTmp=array();
        foreach ($this->getSell() as $sell){
            $productCatId = $sell->getproduct()->getProductCat()->getId();
            $productCatName = $sell->getProduct()->getProductCat()->getName();
            $arr['id']= $productCatId;
            $arr['name']= $productCatName;

            $priceValue = $sell->getPriceValue();
            $qty=$sell->getQuantity();
            $packUnit=$sell->getPackUnit();
            $discount=$sell->getDiscount();
            //mua theo thung
            if($sell->isPack()){
                $qtySale=$sell->isPack();
                $price=$packUnit*$priceValue;//gia ban theo thung
            }else{
                $qtySale=$qty;
                $price=$priceValue;//gia ban le
            }

            $arr['revenue']=($price-$discount)*$qtySale;
            $arrTmp[]=$arr;
        }
        return $arrTmp;
    }

    public function getReturnNumber(){
        $returnNumber=0;
        foreach ($this->getSell() as $key=>$sell){
            $returnNumber+=$sell->getReturn();
        }
        return $returnNumber;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return mixed
     */
    public function getPayMethod()
    {
        return $this->pay_method;
    }

    /**
     * @param mixed $pay_method
     */
    public function setPayMethod($pay_method)
    {
        $this->pay_method = $pay_method;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getDeliveredDate()
    {
        return $this->delivered_date;
    }

    /**
     * @param mixed $delivered_date
     */
    public function setDeliveredDate($delivered_date)
    {
        $this->delivered_date = $delivered_date;
    }

    /**
     * @return User
     */
    public function getDeliveredBy()
    {
        return $this->deliveredBy;
    }

    /**
     * @param mixed $deliveredBy
     */
    public function setDeliveredBy($deliveredBy)
    {
        $this->deliveredBy = $deliveredBy;
    }

    /**
     * @return mixed
     */
    public function getCanceledDate()
    {
        return $this->canceled_date;
    }

    /**
     * @param mixed $canceled_date
     */
    public function setCanceledDate($canceled_date)
    {
        $this->canceled_date = $canceled_date;
    }

    /**
     * @return mixed
     */
    public function getCanceledBy()
    {
        return $this->canceled_by;
    }

    /**
     * @param mixed $canceled_by
     */
    public function setCanceledBy($canceled_by)
    {
        $this->canceled_by = $canceled_by;
    }

    /**
     * @param \Product\Entity\Product $product
     * @return Sell
     */
    public function buildSell(\Product\Entity\Product $product)
    {
        foreach ($this->getSell() as $sell){
            if($sell->getProduct()->getId()==$product->getId())
                return $sell;
        }
        return new Sell();
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param mixed $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return mixed
     */
    public function getZaloAppId()
    {
        return $this->zalo_app_id;
    }

    /**
     * @param mixed $zalo_app_id
     */
    public function setZaloAppId($zalo_app_id)
    {
        $this->zalo_app_id = $zalo_app_id;
    }

    /**
     * @return mixed
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param mixed $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getCompletedBy()
    {
        return $this->completed_by;
    }

    /**
     * @param mixed $completed_by
     */
    public function setCompletedBy($completed_by)
    {
        $this->completed_by = $completed_by;
    }

    /**
     * @return mixed
     */
    public function getUnpaidBy()
    {
        return $this->unpaid_by;
    }

    /**
     * @param mixed $unpaid_by
     */
    public function setUnpaidBy($unpaid_by)
    {
        $this->unpaid_by = $unpaid_by;
    }

    /**
     * @return SellOrderActivity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param mixed $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
    }

    /**
     * @return ArrayCollection
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    public function addInvoice(SellOrderInvoice $invoice)
    {
        if (!$this->invoice->contains($invoice)) {
            $this->invoice->add($invoice);
        }
        return $this;
    }
    public function removeInvoice(SellOrderInvoice $invoice)
    {
        if ($this->invoice->contains($invoice)) {
            $this->invoice->removeElement($invoice);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryCar()
    {
        return $this->delivery_car;
    }

    /**
     * @param mixed $delivery_car
     */
    public function setDeliveryCar($delivery_car)
    {
        $this->delivery_car = $delivery_car;
    }

    /**
     * @return mixed
     */
    public function getDeliveryCarTime()
    {
        return $this->delivery_car_time;
    }

    /**
     * @param mixed $delivery_car_time
     */
    public function setDeliveryCarTime($delivery_car_time)
    {
        $this->delivery_car_time = $delivery_car_time;
    }

    public function getPayMethodName()
    {
        return ($this->getPayMethod()==2?'Tiền mặt':'Chuyển khoản');
    }

    /**
     * @return mixed
     */
    public function getPackNumber()
    {
        return $this->pack_number;
    }

    /**
     * @param mixed $pack_number
     */
    public function setPackNumber($pack_number)
    {
        $this->pack_number = $pack_number;
    }

    /**
     * @return mixed
     */
    public function getCheckDate()
    {
        return $this->check_date;
    }

    /**
     * @param mixed $check_date
     */
    public function setCheckDate($check_date)
    {
        $this->check_date = $check_date;
    }

    /**
     * @return mixed
     */
    public function getCheckBy()
    {
        return $this->check_by;
    }

    /**
     * @param mixed $check_by
     */
    public function setCheckBy($check_by)
    {
        $this->check_by = $check_by;
    }

    /**
     * @return mixed
     */
    public function getCheckAssigned()
    {
        return $this->check_assigned;
    }

    /**
     * @param mixed $check_assigned
     */
    public function setCheckAssigned($check_assigned)
    {
        $this->check_assigned = $check_assigned;
    }

    /**
     * @return mixed
     */
    public function getApprovalCheckBy()
    {
        return $this->approval_check_by;
    }

    /**
     * @param mixed $approval_check_by
     */
    public function setApprovalCheckBy($approval_check_by)
    {
        $this->approval_check_by = $approval_check_by;
    }

    /**
     * @return mixed
     */
    public function getApprovalCheckDate()
    {
        return $this->approval_check_date;
    }

    /**
     * @param mixed $approval_check_date
     */
    public function setApprovalCheckDate($approval_check_date)
    {
        $this->approval_check_date = $approval_check_date;
    }

    /**
     * Tach thong tin note tren don de hien thi tooltip
     * @return string
     */
    public function getNoteTooltip(){
        $noteView='';
        if($this->getNote()){
            $noteArr =explode("\n",$this->getNote());
            if(count($noteArr)){
                foreach ($noteArr as $value){
                    $arrNote = explode("|",$value);
                    $noteView.='<b>'.@$arrNote[0].'</b>:'.@$arrNote[2].'<br>';
                }
            }
        }
        return $noteView;
    }
    public function getProgressCheck(){
        $totalOrderQty=0;
        $totalCheckQty=0;
        foreach ($this->getSell() as $sellItem){
            $totalOrderQty+=$sellItem->getQuantity();//so luong dat don
            $totalCheckQty+=$sellItem->getCheckQty();//sl da kiem
        }

        return ($totalCheckQty/$totalOrderQty)*100;
    }
}