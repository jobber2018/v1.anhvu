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
 * @orm\Table(name="warehouse_scan_detail")
 */

class WerehouseScanDetail
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


    /** @orm\Column(type="integer", name="pack_unit") */
    private $pack_unit;

    /** @orm\Column(type="string", name="qty") */
    private $qty;

    /** @orm\Column(type="string", name="product_barcode") */
    private $product_barcode;

    /** @orm\Column(type="string", name="pack_barcode") */
    private $pack_barcode;

    /** @orm\Column(type="integer", name="product_id") */
    private $product_id;

    /** @orm\Column(type="string", name="product_name") */
    private $product_name;

    /** @orm\Column(type="string", name="unit_name") */
    private $unit_name;
    /**
     * @orm\ManyToOne(targetEntity="Werehouse\Entity\WerehouseScan", inversedBy="werehouseScanDetail")
     * @orm\JoinColumn(name="warehouse_scan_id", referencedColumnName="id")
     */
    private $werehouseScan;

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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPackUnit()
    {
        return $this->pack_unit;
    }

    /**
     * @param mixed $pack_unit
     */
    public function setPackUnit($pack_unit): void
    {
        $this->pack_unit = $pack_unit;
    }

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param mixed $qty
     */
    public function setQty($qty): void
    {
        $this->qty = $qty;
    }

    /**
     * @return mixed
     */
    public function getProductBarcode()
    {
        return $this->product_barcode;
    }

    /**
     * @param mixed $product_barcode
     */
    public function setProductBarcode($product_barcode): void
    {
        $this->product_barcode = $product_barcode;
    }

    /**
     * @return mixed
     */
    public function getPackBarcode()
    {
        return $this->pack_barcode;
    }

    /**
     * @param mixed $pack_barcode
     */
    public function setPackBarcode($pack_barcode): void
    {
        $this->pack_barcode = $pack_barcode;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param mixed $product_id
     */
    public function setProductId($product_id): void
    {
        $this->product_id = $product_id;
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        return $this->product_name;
    }

    /**
     * @param mixed $product_name
     */
    public function setProductName($product_name): void
    {
        $this->product_name = $product_name;
    }

    /**
     * @return mixed
     */
    public function getUnitName()
    {
        return $this->unit_name;
    }

    /**
     * @param mixed $unit_name
     */
    public function setUnitName($unit_name): void
    {
        $this->unit_name = $unit_name;
    }


    /**
     * @return mixed
     */
    public function getWerehouseScan()
    {
        return $this->werehouseScan;
    }

    /**
     * @param mixed $werehouseScan
     */
    public function setWerehouseScan($werehouseScan): void
    {
        $this->werehouseScan = $werehouseScan;
    }

}