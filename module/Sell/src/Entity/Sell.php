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
use Product\Entity\Product;

/**
 * @orm\Entity
 * @orm\Table(name="sell")
 */

class Sell
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="integer", name="quantity") */
    private $quantity;

    /** @orm\Column(type="integer", name="`return`") */
    private $return;

    /** @orm\Column(type="string", name="return_note") */
    private $return_note;

    /** @orm\Column(type="string", name="return_causes") */
    private $return_causes;

    /** @orm\Column(type="datetime", name="return_date") */
    private $return_date;

    /** @orm\Column(type="string", name="return_by") */
    private $return_by;

    /** @orm\Column(type="integer", name="`discount`") */
    private $discount;

    /** @orm\Column(type="integer", name="`cost`") */
    private $cost;

    /** @orm\Column(type="integer", name="`pack_unit`") */
    private $pack_unit;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\Product", inversedBy="sell" )
     * @orm\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @orm\ManyToOne(targetEntity="Sell\Entity\SellOrder", inversedBy="sell" )
     * @orm\JoinColumn(name="sell_order_id", referencedColumnName="id")
     */
    private $sellOrder;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\ProductPrice", inversedBy="sell")
     * @orm\JoinColumn(name="price_id", referencedColumnName="id")
     */
    protected $price;

    /** @orm\Column(type="integer", name="check_qty") */
    private $check_qty;

    /** @orm\Column(type="integer", name="approved_qty") */
    private $approved_qty;

    /** @orm\Column(type="datetime", name="check_date") */
    private $check_date;

    /** @orm\Column(type="string", name="check_by") */
    private $check_by;

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
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return SellOrder
     */
    public function getSellOrder()
    {
        return $this->sellOrder;
    }

    /**
     * @param mixed $sellOrder
     */
    public function setSellOrder($sellOrder)
    {
        $this->sellOrder = $sellOrder;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getPriceValue()
    {
        return $this->price->getPrice();
    }
    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @param mixed $return
     */
    public function setReturn($return)
    {
        $this->return = $return;
    }

    /**
     * @return float|int|string
     */
    public function getDiscount()
    {
        return (is_numeric($this->discount))?$this->discount:0;
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
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param mixed $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return mixed
     */
    public function getPackUnit()
    {
        return $this->pack_unit;
    }

    /**
     * @param mixed $pack_unit
     */
    public function setPackUnit($pack_unit)
    {
        $this->pack_unit = $pack_unit;
    }


    /**
     * @return mixed
     */
    public function getReturnNote()
    {
        return $this->return_note;
    }

    /**
     * @param mixed $return_note
     */
    public function setReturnNote($return_note)
    {
        $this->return_note = $return_note;
    }

    /**
     * @return mixed
     */
    public function getReturnCauses()
    {
        return $this->return_causes;
    }

    /**
     * @param mixed $return_causes
     */
    public function setReturnCauses($return_causes)
    {
        $this->return_causes = $return_causes;
    }

    /**
     * @return mixed
     */
    public function getReturnDate()
    {
        return $this->return_date;
    }

    /**
     * @param mixed $return_date
     */
    public function setReturnDate($return_date)
    {
        $this->return_date = $return_date;
    }

    /**
     * @return mixed
     */
    public function getReturnBy()
    {
        return $this->return_by;
    }

    /**
     * @param mixed $return_by
     */
    public function setReturnBy($return_by)
    {
        $this->return_by = $return_by;
    }

    /**
     * @return mixed
     */
    public function getCheckQty()
    {
        return ($this->check_qty)?$this->check_qty:0;
    }

    /**
     * @param mixed $check_qty
     */
    public function setCheckQty($check_qty)
    {
        $this->check_qty = $check_qty;
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
    public function getApprovedQty()
    {
        return $this->approved_qty;
    }

    /**
     * @param mixed $approved_qty
     */
    public function setApprovedQty($approved_qty)
    {
        $this->approved_qty = $approved_qty;
    }

    /**
     * Kiem tra xem khach mua theo thung hay mua le
     * neu mua theo thung => tra lai so luong thung khach mua
     * @return int
     */
    public function isPack()
    {
        $qty = $this->getQuantity();
        $packUnit=$this->getPackUnit();
        //so luong mua chia quy cach ma khong du => mua theo thung
        if($qty%$packUnit==0){
            return $qty/$packUnit;
        }
        return 0;
    }


}