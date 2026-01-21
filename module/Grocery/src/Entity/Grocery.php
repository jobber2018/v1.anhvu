<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Grocery\Entity;

use Api\Entity\ZaloApp;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;
use GroceryCat\Entity\GroceryCat;
use Sell\Entity\SellOrder;

/**
 * @orm\Entity
 * @orm\Table(name="grocery")
 */

class Grocery
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="string", name="grocery_name") */
    private $groceryName;

    /** @orm\Column(type="string", name="address") */
    private $address;

    /** @orm\Column(type="string", name="mobile") */
    private $mobile;

    /** @orm\Column(type="string", name="img") */
    private $img;

    /** @orm\Column(type="string", name="owner_name") */
    private $ownerName;

    /** @orm\Column(type="integer", name="pay_total") */
    private $pay_total;

    /** @orm\Column(type="string", name="delivery_note") */
    private $delivery_note;

    /** @orm\Column(type="string", name="is_approach") */
    private $is_approach;
    /** @orm\Column(type="string", name="zalo_connect") */
    private $zalo_connect;

    /**
     * @orm\ManyToOne(targetEntity="GroceryCat\Entity\GroceryCat", inversedBy="grocery" )
     * @orm\JoinColumn(name="grocery_cat_id", referencedColumnName="id")
     * @orm\OrderBy({"day" = "DESC"})
     */
    private $groceryCat;

    /**
     * @orm\OneToMany(targetEntity="Grocery\Entity\GroceryInOut", mappedBy="grocery")
     * @orm\JoinColumn(name="id", referencedColumnName="grocery_id")
     * @orm\OrderBy({"id" = "DESC"})
     */
    private $groceryInOut;

    /**
     * @orm\OneToMany(targetEntity="Sell\Entity\SellOrder", mappedBy="grocery")
     * @orm\JoinColumn(name="id", referencedColumnName="grocery_id")
     * @orm\OrderBy({"id" = "DESC"})
     */
    private $sellOrder;

    /**
     * @orm\OneToMany(targetEntity="Grocery\Entity\GroceryCrm", mappedBy="grocery")
     * @orm\JoinColumn(name="id", referencedColumnName="grocery_id")
     * @orm\OrderBy({"id" = "DESC"})
     */
    private $groceryCrm;

    /**
     * @orm\OneToMany(targetEntity="Grocery\Entity\GroceryFeeling", mappedBy="grocery")
     * @orm\JoinColumn(name="id", referencedColumnName="grocery_id")
     * @orm\OrderBy({"id" = "DESC"})
     */
    private $groceryFeeling;

    /** @orm\Column(type="string", name="lat") */
    private $lat;

    /** @orm\Column(type="string", name="lng") */
    private $lng;

    /** @orm\Column(type="datetime", name="check_in_date") */
    private $check_in_date;

    /** @orm\Column(type="datetime", name="check_out_date") */
    private $check_out_date;

    /** @orm\Column(type="integer", name="time_in_grocery") */
    private $time_in_grocery;

    /** @orm\Column(type="integer", name="active") */
    private $active;

    /**
     * @orm\OneToMany(targetEntity="Api\Entity\ZaloApp", mappedBy="grocery")
     * @orm\JoinColumn(name="id", referencedColumnName="grocery_id")
     */
    private $zalo_app;


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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getGroceryName()
    {
        return $this->groceryName;
    }

    /**
     * @param mixed $groceryName
     */
    public function setGroceryName($groceryName)
    {
        $this->groceryName = $groceryName;
    }

    /**
     * @return mixed
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * @param mixed $ownerName
     */
    public function setOwnerName($ownerName)
    {
        $this->ownerName = $ownerName;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param mixed $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param mixed $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return mixed
     */
    public function getCheckInDate()
    {
        return $this->check_in_date;
    }

    /**
     * @param mixed $check_in_date
     */
    public function setCheckInDate($check_in_date)
    {
        $this->check_in_date = $check_in_date;
    }

    /**
     * @return mixed
     */
    public function getCheckOutDate()
    {
        return $this->check_out_date;
    }

    /**
     * @param mixed $check_out_date
     */
    public function setCheckOutDate($check_out_date)
    {
        $this->check_out_date = $check_out_date;
    }

    /**
     * @return mixed
     */
    public function getTimeInGrocery()
    {
        return $this->time_in_grocery;
    }

    /**
     * @param mixed $time_in_grocery
     */
    public function setTimeInGrocery($time_in_grocery)
    {
        $this->time_in_grocery = $time_in_grocery;
    }

    /**
     * @return mixed
     */
    public function getGroceryInOut()
    {
        return $this->groceryInOut;
    }

    /**
     * @param mixed $groceryInOut
     */
    public function setGroceryInOut($groceryInOut)
    {
        $this->groceryInOut = $groceryInOut;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
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
    public function getImg()
    {
        return $this->img;
    }

    /**
     * @param mixed $img
     */
    public function setImg($img)
    {
        $this->img = $img;
    }

    /**
     * @return mixed
     */
    public function getPayTotal()
    {
        return $this->pay_total;
    }

    /**
     * @param mixed $pay_total
     */
    public function setPayTotal($pay_total)
    {
        $this->pay_total = $pay_total;
    }

    public function getCountOrderPay(){
        $order=0;
        foreach ($this->getSellOrder() as $orderItem){
            if($orderItem->getStatus()==3)
                $order++;
        }
        return $order;
    }

    /**
     * @return mixed
     */
    public function getGroceryCrm()
    {
        return $this->groceryCrm;
    }

    /**
     * @param mixed $groceryCrm
     */
    public function setGroceryCrm($groceryCrm)
    {
        $this->groceryCrm = $groceryCrm;
    }

    /**
     * @return mixed
     */
    public function getGroceryFeeling()
    {
        return $this->groceryFeeling;
    }

    /**
     * @param mixed $groceryFeeling
     */
    public function setGroceryFeeling($groceryFeeling)
    {
        $this->groceryFeeling = $groceryFeeling;
    }

    /**
     * @return mixed
     */
    public function getDeliveryNote()
    {
        return $this->delivery_note;
    }

    /**
     * @param mixed $delivery_note
     */
    public function setDeliveryNote($delivery_note)
    {
        $this->delivery_note = $delivery_note;
    }

    /**
     * @return mixed
     */
    public function getIsApproach()
    {
        return $this->is_approach;
    }

    /**
     * @return mixed
     */
    public function getStringIsApproach()
    {
        if($this->is_approach=="1")
            return "Online";
        elseif ($this->is_approach=="2")
            return "Online/Offline";
        return "Offline";
    }
    /**
     * @param mixed $is_approach
     */
    public function setIsApproach($is_approach)
    {
        $this->is_approach = $is_approach;
    }

    /**
     * @return mixed
     */
    public function getZaloConnect()
    {
        if($this->zalo_connect==1 || $this->getIsApproach()==1)
            return 1;
        else
            return 0;
    }

    /**
     * @param mixed $zalo_connect
     */
    public function setZaloConnect($zalo_connect)
    {
        $this->zalo_connect = $zalo_connect;
    }

    /**
     * @return ZaloApp
     */
    public function getZaloApp()
    {
        return $this->zalo_app;
    }

    /**
     * @param mixed $zalo_app
     */
    public function setZaloApp($zalo_app)
    {
        $this->zalo_app = $zalo_app;
    }

    /**
     * @return null|ZaloApp
     */
    public function getZaloAppItem(){
        if($this->getZaloApp()){
            foreach ($this->getZaloApp() as $zaloItem){
                return $zaloItem;
            }
        }
        return null;
    }
}