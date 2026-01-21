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
use Werehouse\Entity\Werehouse;

/**
 * @orm\Entity
 * @orm\Table(name="product")
 */

class Product
{

    public function __construct() {
        $this->price = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="string", name="weight") */
    private $weight;

    /** @orm\Column(type="integer", name="box_unit") */
    private $box_unit;

    /** @orm\Column(type="integer", name="inventory") */
    private $inventory;

    /** @orm\Column(type="integer", name="average_price") */
    private $average_price;

    /** @orm\Column(type="string", name="img") */
    private $img;

    /** @orm\Column(type="string", name="code") */
    private $code;

    /** @orm\Column(type="string", name="pack_code") */
    private $pack_code;

    /** @orm\Column(type="string", name="code_1") */
    private $code_1;

    /** @orm\Column(type="string", name="code_2") */
    private $code_2;

    /** @orm\Column(type="string", name="code_3") */
    private $code_3;

    /** @orm\Column(type="string", name="active") */
    private $active;

    /** @orm\Column(type="integer", name="`sort`") */
    private $sort;
    /** @orm\Column(type="integer", name="`sort_price_table`") */
    private $sort_price_table;

    /** @orm\Column(type="integer", name="is_del") */
    private $is_del;

    /** @orm\Column(type="integer", name="norm") */
    private $norm;

    /** @orm\Column(type="integer", name="norm_input") */
    private $norm_input;

    /** @orm\Column(type="string", name="note_order") */
    private $note_order;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\ProductCat", inversedBy="product" )
     * @orm\JoinColumn(name="product_cat_id", referencedColumnName="id")
     */
    private $productCat;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\ProductUnit", inversedBy="product" )
     * @orm\JoinColumn(name="product_unit_id", referencedColumnName="id")
     */
    private $unit;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\ProductPrice", mappedBy="product", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="product_id")
     * @orm\OrderBy({"created_date" = "DESC"})
     */
    protected $price;

    /**
     * @orm\OneToMany(targetEntity="Werehouse\Entity\Werehouse", mappedBy="product")
     * @orm\JoinColumn(name="id", referencedColumnName="product_id")
     * @orm\OrderBy({"id" = "DESC"})
     */
    private $werehouse;

    /**
     * @orm\OneToMany(targetEntity="Sell\Entity\Sell", mappedBy="product")
     * @orm\JoinColumn(name="id", referencedColumnName="product_id")
     * @orm\OrderBy({"id" = "DESC"})
     */
    private $sell;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\ProductInventory", mappedBy="product", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="product_id")
     * @orm\OrderBy({"created_date" = "DESC"})
     */
    protected $inventoryHistory;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\ProductActivity", mappedBy="product", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="product_id")
     * @orm\OrderBy({"created_date" = "DESC"})
     */
    protected $activity;

    /** @orm\Column(type="datetime", name="inventory_check") */
    private $inventory_check;

    /** @orm\Column(type="integer", name="exchange_unit") */
    private $exchange_unit;

    /** @orm\Column(type="string", name="pack_sale_type") */
    private $pack_sale_type;
    /** @orm\Column(type="integer", name="pack_sale_value") */
    private $pack_sale_value;
    /** @orm\Column(type="string", name="unit_sale_type") */
    private $unit_sale_type;
    /** @orm\Column(type="integer", name="unit_sale_value") */
    private $unit_sale_value;
    /** @orm\Column(type="string", name="label_name") */
    private $label_name;
    /** @orm\Column(type="string", name="group_id") */
    private $group_id;

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
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
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

    /**
     * @return mixed
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * @param mixed $inventory
     */
    public function setInventory($inventory)
    {
        $this->inventory = $inventory;
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
    public function getProductCat()
    {
        return $this->productCat;
    }

    /**
     * @param mixed $productCat
     */
    public function setProductCat($productCat)
    {
        $this->productCat = $productCat;
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
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return mixed
     */
    public function getWerehouse()
    {
        return $this->werehouse;
    }

    /**
     * @param mixed $werehouse
     */
    public function setWerehouse($werehouse)
    {
        $this->werehouse = $werehouse;
    }

    /**
     * @return mixed
     */
    public function getSell()
    {
        return $this->sell;
    }

    /**
     * @param mixed $sell
     */
    public function setSell($sell)
    {
        $this->sell = $sell;
    }

    /**
     * @return ProductPrice
     */
    public function getActivePrice(){
        foreach ($this->getPrice() as $item){
            if($item->getActive()==1) return $item;
        }
    }

    public function getActivePriceValue(){
        return $this->getActivePrice()->getPrice();
    }

    public function addPrice(ProductPrice $price)
    {
        foreach ($this->getPrice() as $item){
            $item->setActive(0);
        }

        if (!$this->price->contains($price)) {
            $this->price->add($price);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAveragePrice()
    {
        return $this->average_price;
    }

    public function calAveragePrice()
    {
        $werehouse = $this->getWerehouse();
        $totalPrice=0;
        $averagePrice=0;
        $totalProductUnit=0;
        foreach ($werehouse as $werehouseItem){
            $werehouseOrder = $werehouseItem->getWerehouseOrder();
            if($werehouseOrder->getStatus()==1){
                $boxPrice = $werehouseItem->getPrice();

                $boxQuantity = $werehouseItem->getQuantity();
                //unit tai thoi diem nhap
                $boxUnit = $werehouseItem->getBoxUnit();
                //tong tien nhap
                $totalPrice = $totalPrice+$boxPrice*$boxQuantity;
                //tong san pham nhap (tinh ra don vi tip, chai, tui...)
                $totalProductUnit = $totalProductUnit+$boxQuantity*$boxUnit;
            }
        }

        if($totalProductUnit>0){
            $averagePrice = round($totalPrice/$totalProductUnit);
        }
        return $averagePrice;
    }

    /**
     * @param mixed $average_price
     */
    public function setAveragePrice($average_price)
    {
        $this->average_price = $average_price;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getCode1()
    {
        return $this->code_1;
    }

    /**
     * @param mixed $code_1
     */
    public function setCode1($code_1)
    {
        $this->code_1 = $code_1;
    }

    /**
     * @return mixed
     */
    public function getCode2()
    {
        return $this->code_2;
    }

    /**
     * @param mixed $code_2
     */
    public function setCode2($code_2)
    {
        $this->code_2 = $code_2;
    }

    /**
     * @return mixed
     */
    public function getCode3()
    {
        return $this->code_3;
    }

    /**
     * @param mixed $code_3
     */
    public function setCode3($code_3)
    {
        $this->code_3 = $code_3;
    }

    public function getSubCode()
    {
        if($this->code) return substr($this->code,-6);
        else return '';

    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return mixed
     */
    public function getSortPriceTable()
    {
        return $this->sort_price_table;
    }

    /**
     * @param mixed $sort_price_table
     */
    public function setSortPriceTable($sort_price_table)
    {
        $this->sort_price_table = $sort_price_table;
    }

    /**
     * @return mixed
     */
    public function getIsDel()
    {
        return $this->is_del;
    }

    /**
     * @param mixed $is_del
     */
    public function setIsDel($is_del)
    {
        $this->is_del = $is_del;
    }

    /**
     * @return mixed
     */
    public function getNorm()
    {
        return $this->norm;
    }

    /**
     * @param mixed $norm
     */
    public function setNorm($norm)
    {
        $this->norm = $norm;
    }

    /**
     * @return mixed
     */
    public function getNormInput()
    {
        return $this->norm_input;
    }

    /**
     * @param mixed $norm_input
     */
    public function setNormInput($norm_input)
    {
        $this->norm_input = $norm_input;
    }

    /**
     * @return mixed
     */
    public function getNoteOrder()
    {
        return $this->note_order;
    }

    /**
     * @param mixed $note_order
     */
    public function setNoteOrder($note_order)
    {
        $this->note_order = $note_order;
    }

    /**
     * @return mixed
     */
    public function getInventoryHistory()
    {
        return $this->inventoryHistory;
    }

    /**
     * @param mixed $inventoryHistory
     */
    public function setInventoryHistory($inventoryHistory)
    {
        $this->inventoryHistory = $inventoryHistory;
    }

    public function getLastInputPrice(){
        $id=0;
        $resultItem = new Werehouse();
        foreach ($this->getWerehouse() as $werehouseItem){
            if($werehouseItem->getId()>$id) $resultItem=$werehouseItem;
        }
        return $resultItem;
    }

    /**
     * @return mixed
     */
    public function getInventoryCheck()
    {
        return $this->inventory_check;
    }

    /**
     * @param mixed $inventory_check
     */
    public function setInventoryCheck($inventory_check)
    {
        $this->inventory_check = $inventory_check;
    }

    /**
     * @return mixed
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param mixed $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
    }

    /**
     * @return mixed
     */
    public function getExchangeUnit()
    {
        return $this->exchange_unit;
    }

    /**
     * @param mixed $exchange_unit
     */
    public function setExchangeUnit($exchange_unit)
    {
        $this->exchange_unit = $exchange_unit;
    }

    /**
     * @param $p_qty
     * @return integer
     */
    public function validateQty($p_qty)
    {
        $exchangeUnit = $this->getExchangeUnit();
        $qty=$p_qty/$exchangeUnit;

        if($qty < 1) $qty=$exchangeUnit;
        else $qty = round($qty)*$exchangeUnit;

        if($qty > $this->getInventory()) $qty = $this->getInventory();

        return $qty;
    }

    /**
     * @return mixed
     */
    public function getPackSaleType()
    {
        return $this->pack_sale_type;
    }

    /**
     * @param mixed $pack_sale_type
     */
    public function setPackSaleType($pack_sale_type)
    {
        $this->pack_sale_type = $pack_sale_type;
    }

    /**
     * @return mixed
     */
    public function getPackSaleValue()
    {
        return $this->pack_sale_value;
    }

    public function getPackSaleValueString()
    {
        if($this->getPackSaleType()=='percent')
            return $this->getPackSaleValue().'%';
        elseif($this->getPackSaleType()=='fixed')
            return $this->getPackSaleValue().'đ';
        else return '-/-';
    }

    /**
     * @param mixed $pack_sale_value
     */
    public function setPackSaleValue($pack_sale_value)
    {
        $this->pack_sale_value = $pack_sale_value;
    }

    /**
     * so tien tien chiet khau theo thung
     * @return mixed
     */
    public function getPackPriceSale()
    {
        $packSaleValue=0;
        if($this->getPackSaleType()=='percent')
            $packSaleValue= ($this->getPackSaleValue()*$this->getPackPrice())/100;
        else if($this->getPackSaleType()=='fixed')
            $packSaleValue= $this->getPackSaleValue();
        return $packSaleValue;
    }

    /**
     * gia thung sau khi tru chieu khau
     * @return mixed
     */
    public function getPackPriceAfterSale()
    {
        $packPriceSale=$this->getPackPriceSale();
        if($packPriceSale>0)
            return $this->getPackPrice()-$packPriceSale;
        else return $this->getPackPrice();
    }
    /**
     * Gia thung chua chiet khau
     * @return float|int
     */
    public function getPackPrice()
    {
        return $this->getActivePriceValue()*$this->getBoxUnit();
    }
    /**
     * @return mixed
     */
    public function getUnitSaleType()
    {
        return $this->unit_sale_type;
    }

    /**
     * @param mixed $unit_sale_type
     */
    public function setUnitSaleType($unit_sale_type)
    {
        $this->unit_sale_type = $unit_sale_type;
    }

    /**
     * @return mixed
     */
    public function getUnitSaleValue()
    {
        return $this->unit_sale_value;
    }


    public function getUnitSaleValueString()
    {
        if($this->getUnitSaleType()=='percent')
            return $this->getUnitSaleValue().'%';
        elseif($this->getUnitSaleType()=='fiexd')
            echo $this->getUnitSaleValue().'đ';
        else return '-/-';
    }
    /**
     * so tien chiet khau cua san pham
     * @return mixed
     */
    public function getUnitPriceSale()
    {
        $unitSaleValue=0;
        if($this->getUnitSaleType()=='percent')
            $unitSaleValue= ($this->getUnitSaleValue()*$this->getActivePriceValue())/100;
        else if($this->getUnitSaleType()=='fixed')
            $unitSaleValue= $this->getUnitSaleValue();
        return $unitSaleValue;
    }
    /**
     * so tien sau giam gia cua 1 san pham
     * @return mixed
     */
    public function getUnitPriceAfterSale()
    {
        //so tien giam gia
        $unitPriceSale=$this->getUnitPriceSale();
        if($unitPriceSale>0)
            return $this->getActivePriceValue()-$unitPriceSale;
        else return $this->getActivePriceValue();
    }
    /**
     * @param mixed $unit_sale_value
     */
    public function setUnitSaleValue($unit_sale_value)
    {
        $this->unit_sale_value = $unit_sale_value;
    }

    /**
     * @return mixed
     */
    public function getLabelName()
    {
        return $this->label_name;
    }

    /**
     * @param mixed $label_name
     */
    public function setLabelName($label_name)
    {
        $this->label_name = $label_name;
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
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * @param mixed $group_id
     */
    public function setGroupId($group_id)
    {
        $this->group_id = $group_id;
    }

    /**
     * @return mixed
     */
    public function getPackCode()
    {
        return $this->pack_code;
    }

    /**
     * @param mixed $pack_code
     */
    public function setPackCode($pack_code)
    {
        $this->pack_code = $pack_code;
    }
}