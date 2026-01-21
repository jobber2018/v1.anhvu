<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Report\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @orm\Table(name="cost_revenue")
 */

class CostRevenue
{

    public function __construct() {
    }
    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="integer", name="cost") */
    private $cost;

    /** @orm\Column(type="integer", name="revenue") */
    private $revenue;

    /** @orm\Column(type="integer", name="discount") */
    private $discount;

    /** @orm\Column(type="integer", name="order_completed") */
    private $order_completed;

    /** @orm\Column(type="integer", name="order_created") */
    private $order_created;

    /** @orm\Column(type="integer", name="order_customer_created") */
    private $order_customer_created;

    /** @orm\Column(type="integer", name="order_delivered") */
    private $order_delivered;

    /** @orm\Column(type="integer", name="order_delivered_number") */
    private $order_delivered_number;

    /** @orm\Column(type="integer", name="receivable") */
    private $receivable;

    /** @orm\Column(type="integer", name="return_ncc") */
    private $return_ncc;

    /** @orm\Column(type="integer", name="warehouse_value") */
    private $warehouse_value;

    /** @orm\Column(type="date", name="`date`") */
    private $date;

    /** @orm\Column(type="string", name="`order_discount_id`") */
    private $order_discount_id;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $user;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

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
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     * @param mixed $revenue
     */
    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;
    }

    /**
     * @return mixed
     */
    public function getOrderCompleted()
    {
        return $this->order_completed;
    }

    /**
     * @param mixed $order_completed
     */
    public function setOrderCompleted($order_completed)
    {
        $this->order_completed = $order_completed;
    }

    /**
     * @return mixed
     */
    public function getOrderCreated()
    {
        return $this->order_created;
    }

    /**
     * @param mixed $order_created
     */
    public function setOrderCreated($order_created)
    {
        $this->order_created = $order_created;
    }

    /**
     * @return mixed
     */
    public function getOrderDelivered()
    {
        return $this->order_delivered;
    }

    /**
     * @param mixed $order_delivered
     */
    public function setOrderDelivered($order_delivered)
    {
        $this->order_delivered = $order_delivered;
    }

    /**
     * @return mixed
     */
    public function getOrderDeliveredNumber()
    {
        return $this->order_delivered_number;
    }

    /**
     * @param mixed $order_delivered_number
     */
    public function setOrderDeliveredNumber($order_delivered_number)
    {
        $this->order_delivered_number = $order_delivered_number;
    }

    /**
     * @return mixed
     */
    public function getReceivable()
    {
        return $this->receivable;
    }

    /**
     * @param mixed $receivable
     */
    public function setReceivable($receivable)
    {
        $this->receivable = $receivable;
    }

    /**
     * @return mixed
     */
    public function getReturnNcc()
    {
        return $this->return_ncc;
    }

    /**
     * @param mixed $return_ncc
     */
    public function setReturnNcc($return_ncc)
    {
        $this->return_ncc = $return_ncc;
    }

    /**
     * @return mixed
     */
    public function getWarehouseValue()
    {
        return $this->warehouse_value;
    }

    /**
     * @param mixed $warehouse_value
     */
    public function setWarehouseValue($warehouse_value)
    {
        $this->warehouse_value = $warehouse_value;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
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
    public function getOrderCustomerCreated()
    {
        return $this->order_customer_created;
    }

    /**
     * @param mixed $order_customer_created
     */
    public function setOrderCustomerCreated($order_customer_created)
    {
        $this->order_customer_created = $order_customer_created;
    }

    /**
     * @return mixed
     */
    public function getOrderDiscountId()
    {
        return $this->order_discount_id;
    }

    /**
     * @param mixed $order_discount_id
     */
    public function setOrderDiscountId($order_discount_id)
    {
        $this->order_discount_id = $order_discount_id;
    }

}