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
 * @orm\Table(name="sell_order_activity")
 */

class SellOrderActivity
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
     * @orm\ManyToOne(targetEntity="Sell\Entity\SellOrder", inversedBy="activity" )
     * @orm\JoinColumn(name="sell_order_id", referencedColumnName="id")
     */
    private $sellOrder;

    /** @orm\Column(type="string", name="action_by") */
    private $action_by;

    /** @orm\Column(type="string", name="action") */
    private $action;

    /** @orm\Column(type="datetime", name="action_time") */
    private $action_time;

    /** @orm\Column(type="string", name="action_icon") */
    private $action_icon;

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
    public function getActionBy()
    {
        return $this->action_by;
    }

    /**
     * @param mixed $action_by
     */
    public function setActionBy($action_by)
    {
        $this->action_by = $action_by;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getActionTime()
    {
        return $this->action_time;
    }

    /**
     * @param mixed $action_time
     */
    public function setActionTime($action_time)
    {
        $this->action_time = $action_time;
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
    public function getActionIcon()
    {
        return $this->action_icon;
    }

    /**
     * @param mixed $action_icon
     */
    public function setActionIcon($action_icon)
    {
        $this->action_icon = $action_icon;
    }

}