<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace GroceryCat\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @orm\Table(name="grocery_cat_analytic")
 */

class GroceryCatAnalytic
{

    public function __construct() {

    }


    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="GroceryCat\Entity\GroceryCat", inversedBy="grocery" )
     * @orm\JoinColumn(name="grocery_cat_id", referencedColumnName="id")
     */
    private $groceryCat;

    /** @orm\Column(type="integer", name="order_owner_id") */
    private $order_owner_id;
    /** @orm\Column(type="integer", name="order_owner_created") */
    private $order_owner_created;
    /** @orm\Column(type="integer", name="order_admin_created") */
    private $order_admin_created;
    /** @orm\Column(type="integer", name="order_customer_created") */
    private $order_customer_created;
    /** @orm\Column(type="integer", name="total_order_value") */
    private $total_order_value;
    /** @orm\Column(type="integer", name="customer_number") */
    private $customer_number;

    /** @orm\Column(type="datetime", name="start_time_moning") */
    private $start_time_moning;
    /** @orm\Column(type="datetime", name="end_time_moning") */
    private $end_time_moning;
    /** @orm\Column(type="integer", name="moning_checkined") */
    private $moning_checkined;
    /** @orm\Column(type="datetime", name="start_time_afternoon") */
    private $start_time_afternoon;
    /** @orm\Column(type="datetime", name="end_time_afternoon") */
    private $end_time_afternoon;
    /** @orm\Column(type="integer", name="afternoon_checkined") */
    private $afternoon_checkined;
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
     * @return GroceryCat
     */
    public function getGroceryCat()
    {
        return $this->groceryCat;
    }

    /**
     * @param mixed $groceryCat
     */
    public function setGroceryCat($groceryCat)
    {
        $this->groceryCat = $groceryCat;
    }

    /**
     * @return mixed
     */
    public function getOrderOwnerId()
    {
        return $this->order_owner_id;
    }

    /**
     * @param mixed $order_owner_id
     */
    public function setOrderOwnerId($order_owner_id)
    {
        $this->order_owner_id = $order_owner_id;
    }

    /**
     * @return mixed
     */
    public function getOrderOwnerCreated()
    {
        return $this->order_owner_created;
    }

    /**
     * @param mixed $order_owner_created
     */
    public function setOrderOwnerCreated($order_owner_created)
    {
        $this->order_owner_created = $order_owner_created;
    }

    /**
     * @return mixed
     */
    public function getOrderAdminCreated()
    {
        return $this->order_admin_created;
    }

    /**
     * @param mixed $order_admin_created
     */
    public function setOrderAdminCreated($order_admin_created)
    {
        $this->order_admin_created = $order_admin_created;
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
    public function getTotalOrderValue()
    {
        return $this->total_order_value;
    }

    /**
     * @param mixed $total_order_value
     */
    public function setTotalOrderValue($total_order_value)
    {
        $this->total_order_value = $total_order_value;
    }

    /**
     * @return mixed
     */
    public function getCustomerNumber()
    {
        return $this->customer_number;
    }

    /**
     * @param mixed $customer_number
     */
    public function setCustomerNumber($customer_number)
    {
        $this->customer_number = $customer_number;
    }

    /**
     * @return mixed
     */
    public function getStartTimeMoning()
    {
        return $this->start_time_moning;
    }

    /**
     * @param mixed $start_time_moning
     */
    public function setStartTimeMoning($start_time_moning)
    {
        $this->start_time_moning = $start_time_moning;
    }

    /**
     * @return mixed
     */
    public function getEndTimeMoning()
    {
        return $this->end_time_moning;
    }

    /**
     * @param mixed $end_time_moning
     */
    public function setEndTimeMoning($end_time_moning)
    {
        $this->end_time_moning = $end_time_moning;
    }

    /**
     * @return mixed
     */
    public function getMoningCheckined()
    {
        return $this->moning_checkined;
    }

    /**
     * @param mixed $moning_checkined
     */
    public function setMoningCheckined($moning_checkined)
    {
        $this->moning_checkined = $moning_checkined;
    }

    /**
     * @return mixed
     */
    public function getStartTimeAfternoon()
    {
        return $this->start_time_afternoon;
    }

    /**
     * @param mixed $start_time_afternoon
     */
    public function setStartTimeAfternoon($start_time_afternoon)
    {
        $this->start_time_afternoon = $start_time_afternoon;
    }

    /**
     * @return mixed
     */
    public function getEndTimeAfternoon()
    {
        return $this->end_time_afternoon;
    }

    /**
     * @param mixed $end_time_afternoon
     */
    public function setEndTimeAfternoon($end_time_afternoon)
    {
        $this->end_time_afternoon = $end_time_afternoon;
    }

    /**
     * @return mixed
     */
    public function getAfternoonCheckined()
    {
        return $this->afternoon_checkined;
    }

    /**
     * @param mixed $afternoon_checkined
     */
    public function setAfternoonCheckined($afternoon_checkined)
    {
        $this->afternoon_checkined = $afternoon_checkined;
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
     * tra ve so h tren tuyen
     * @return float|int
     */
    public function getTotalDiffTimeOnRoute(){
        $diff=0;
        if($this->getStartTimeMoning() && $this->getEndTimeMoning())
            $diff=floor((strtotime($this->getEndTimeMoning()->format('Y-m-d H:i:s')) - strtotime($this->getStartTimeMoning()->format('Y-m-d H:i:s')))/60);
        if($this->getStartTimeAfternoon() && $this->getEndTimeAfternoon())
            $diff+=floor((strtotime($this->getEndTimeAfternoon()->format('Y-m-d H:i:s')) - strtotime($this->getStartTimeAfternoon()->format('Y-m-d H:i:s')))/60);
        return round($diff/60,1);
    }

    public function getTotalOrderNumber(){
        return $this->getOrderAdminCreated()+$this->getOrderCustomerCreated()+$this->getOrderOwnerCreated();
    }
}