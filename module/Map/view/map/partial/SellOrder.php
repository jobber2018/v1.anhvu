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

/**
 * @orm\Entity
 * @orm\Table(name="sell_order")
 */

class SellOrder
{

    public function __construct() {
        $this->sell = new ArrayCollection();
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

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $user;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

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


    /** @orm\Column(type="datetime", name="pay_date") */
    private $pay_date;

    /** @orm\Column(type="integer", name="pay_method") */
    private $pay_method;

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
        return $this->total_price;
    }

    /**
     * @param mixed $total_price
     */
    public function setTotalPrice($total_price)
    {
        $this->total_price = $total_price;
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
     * @param ArrayCollection $sell
     */
    public function addSell(Sell $sell)
    {
        if (!$this->sell->contains($sell)) {
            $this->sell->add($sell);
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

    public function getProfit(){
//        if($this->getStatus()!=3) return 0;
        $totalSellPrice=0;
        $totalPriceInput=0;
        foreach ($this->getSell() as $key=>$sell){
            $price=$sell->getPrice()->getPrice();
            $averagePrice=$sell->getProduct()->getAveragePrice();
            $quantity=$sell->getQuantity();
            $totalSellPrice+=$price*$quantity;
            $totalPriceInput+=$averagePrice*$quantity;
        }
        return $totalSellPrice-$totalPriceInput;
    }

    public function getRevenueByProductCat(){
        foreach ($this->getSell() as $key=>$sell){
            $productCatId = $sell->getproduct()->getProductCat()->getId();
            $productCatName = $sell->getProduct()->getProductCat()->getName();
            $arr['id']= $productCatId;
            $arr['name']= $productCatName;
            $arr['revenue']=$sell->getPrice()->getPrice()*$sell->getQuantity();
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

}