<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Werehouse\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;
use Product\Entity\Product;

/**
 * @orm\Entity
 * @orm\Table(name="werehouse_order_invoice")
 */

class WerehouseOrderInvoice
{

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * MANY-TO-ONE BIDIRECTIONAL, OWNING SIDE
     * @orm\ManyToOne(targetEntity="Werehouse\Entity\WerehouseOrder", inversedBy="invoice")
     * @orm\JoinColumn(name="werehouse_order_id", referencedColumnName="id")
     */
    private $werehouseOrder;

    /** @orm\Column(type="string", name="path") */
    private $path;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="upload_by", referencedColumnName="id")
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
    public function getWerehouseOrder()
    {
        return $this->werehouseOrder;
    }

    /**
     * @param mixed $werehouseOrder
     */
    public function setWerehouseOrder($werehouseOrder)
    {
        $this->werehouseOrder = $werehouseOrder;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
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

}