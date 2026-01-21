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
 * @orm\Table(name="warehouse_check")
 */

class WerehouseCheck
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="integer", name="actual_inventory") */
    private $actual_inventory;

    /** @orm\Column(type="integer", name="book_inventory") */
    private $book_inventory;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /**
     * @orm\ManyToOne(targetEntity="Werehouse\Entity\WerehouseSheet", inversedBy="werehouseCheck")
     * @orm\JoinColumn(name="sheet_id", referencedColumnName="id")
     */
    private $werehouseSheet;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\Product", inversedBy="warehouse" )
     * @orm\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /** @orm\Column(type="integer", name="is_update") */
    private $is_update;


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
    public function getActualInventory()
    {
        return $this->actual_inventory;
    }

    /**
     * @param mixed $actual_inventory
     */
    public function setActualInventory($actual_inventory)
    {
        $this->actual_inventory = $actual_inventory;
    }

    /**
     * @return mixed
     */
    public function getBookInventory()
    {
        return $this->book_inventory;
    }

    /**
     * @param mixed $book_inventory
     */
    public function setBookInventory($book_inventory)
    {
        $this->book_inventory = $book_inventory;
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
     * @return WerehouseSheet
     */
    public function getWerehouseSheet()
    {
        return $this->werehouseSheet;
    }

    /**
     * @param mixed $werehouseSheet
     */
    public function setWerehouseSheet($werehouseSheet)
    {
        $this->werehouseSheet = $werehouseSheet;
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
     * @return mixed
     */
    public function getIsUpdate()
    {
        return $this->is_update;
    }

    /**
     * @param mixed $is_update
     */
    public function setIsUpdate($is_update)
    {
        $this->is_update = $is_update;
    }

}