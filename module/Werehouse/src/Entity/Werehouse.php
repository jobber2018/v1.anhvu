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
 * @orm\Table(name="werehouse")
 */

class Werehouse
{

    public function __construct() {
//        $this->werehouseOrder = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="integer", name="price") */
    private $price;


    /** @orm\Column(type="integer", name="box_unit") */
    private $box_unit;

    /** @orm\Column(type="string", name="quantity") */
    private $quantity;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\Product", inversedBy="warehouse" )
     * @orm\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @orm\ManyToOne(targetEntity="Werehouse\Entity\WerehouseOrder", inversedBy="werehouse")
     * @orm\JoinColumn(name="werehouse_order_id", referencedColumnName="id")
     */
    private $werehouseOrder;


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
     * @return WerehouseOrder
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
    public function getBoxUnit()
    {
        return $this->box_unit;
    }

    /**
     * @param mixed $box_unit
     */
    public function setBoxUnit($box_unit)
    {
        $this->box_unit = $box_unit;
    }

}