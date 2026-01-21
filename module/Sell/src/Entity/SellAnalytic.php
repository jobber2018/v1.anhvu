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

/**
 * @orm\Entity
 * @orm\Table(name="sell_analytic")
 */

class SellAnalytic
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="integer", name="diff") */
    private $diff;

    /** @orm\Column(type="datetime", name="created_order") */
    private $created_order;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /**
     * @orm\ManyToOne(targetEntity="Grocery\Entity\Grocery", inversedBy="sellOrder" )
     * @orm\JoinColumn(name="grocery_id", referencedColumnName="id")
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
    public function getDiff()
    {
        return $this->diff;
    }

    /**
     * @param mixed $diff
     */
    public function setDiff($diff)
    {
        $this->diff = $diff;
    }

    /**
     * @return mixed
     */
    public function getCreatedOrder()
    {
        return $this->created_order;
    }

    /**
     * @param mixed $created_order
     */
    public function setCreatedOrder($created_order)
    {
        $this->created_order = $created_order;
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

}