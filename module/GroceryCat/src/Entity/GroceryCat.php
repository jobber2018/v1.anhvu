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
 * @orm\Table(name="grocery_cat")
 */

class GroceryCat
{

    public function __construct() {
        $this->grocery = new ArrayCollection();
    }


    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="integer", name="day") */
    private $day;


    /** @orm\Column(type="string", name="polygon") */
    private $polygon;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @orm\OneToMany(targetEntity="Grocery\Entity\Grocery", mappedBy="groceryCat")
     * @orm\JoinColumn(name="id", referencedColumnName="grocery_cat_id")
     * @orm\OrderBy({"id" = "ASC"})
     */
    private $grocery;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param mixed $day
     */
    public function setDay($day)
    {
        $this->day = $day;
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
     * @return ArrayCollection
     */
    public function getGrocery()
    {
        return $this->grocery;
    }

    /**
     * @param ArrayCollection $grocery
     */
    public function setGrocery($grocery)
    {
        $this->grocery = $grocery;
    }

    public function getGroceryActiveCount(){
        $count=0;
        foreach ($this->grocery as $key=>$item) {
            if($item->getActive()==1) $count++;
        }
        return $count;
    }

    /**
     * @return mixed
     */
    public function getPolygon()
    {
        return $this->polygon;
    }

    /**
     * @param mixed $polygon
     */
    public function setPolygon($polygon)
    {
        $this->polygon = $polygon;
    }

    public function getTotalGroceryOrderNumber(){
        $numberGroceryOrder=0;
        foreach ($this->getGrocery() as $grocery){
            if($grocery->getPayTotal()>0) $numberGroceryOrder+=1;
        }
        return $numberGroceryOrder;
    }
    public function getTotalGroceryOrderMoney(){
        $moneyGroceryOrder=0;
        foreach ($this->getGrocery() as $grocery){
            if($grocery->getPayTotal()>0) $moneyGroceryOrder+=$grocery->getPayTotal();
        }
        return $moneyGroceryOrder;
    }
}