<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Product\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @orm\Table(name="product_inventory")
 */

class ProductInventory
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;


    /** @orm\Column(type="integer", name="after_change") */
    private $after_change;

    /** @orm\Column(type="integer", name="before_change") */
    private $before_change;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $user;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\Product", inversedBy="price")
     * @orm\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /** @orm\Column(type="string", name="note") */
    private $note;

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
    public function getAfterChange()
    {
        return $this->after_change;
    }

    /**
     * @param mixed $after_change
     */
    public function setAfterChange($after_change)
    {
        $this->after_change = $after_change;
    }

    /**
     * @return mixed
     */
    public function getBeforeChange()
    {
        return $this->before_change;
    }

    /**
     * @param mixed $before_change
     */
    public function setBeforeChange($before_change)
    {
        $this->before_change = $before_change;
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

}