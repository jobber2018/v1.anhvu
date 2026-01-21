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

/**
 * @orm\Entity
 * @orm\Table(name="warehouse_order")
 */

class WerehouseOrder
{

    public function __construct() {
        $this->werehouse = new ArrayCollection();
        $this->invoice = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="integer", name="total_price") */
    private $total_price;

    /** @orm\Column(type="integer", name="status") */
    private $status;


    /** @orm\Column(type="integer", name="pay") */
    private $pay;
    /** @orm\Column(type="datetime", name="pay_date") */
    private $pay_date;
    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $user;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /**
     * @orm\ManyToOne(targetEntity="Supplier\Entity\Supplier", inversedBy="werehouseOrder" )
     * @orm\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;

    /**
     * @orm\OneToMany(targetEntity="Werehouse\Entity\Werehouse", mappedBy="werehouseOrder", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="werehouse_order_id")
     */
    protected $werehouse;

    /**
     * @orm\OneToMany(targetEntity="Werehouse\Entity\WerehouseOrderInvoice", mappedBy="werehouseOrder", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="werehouse_order_id")
     */
    protected $invoice;

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
    public function getTotalPrice()
    {
        $totalPrice=0;
        foreach ($this->getWerehouse() as $werehouseItem){
            $totalPrice = $totalPrice+ ($werehouseItem->getPrice()*$werehouseItem->getQuantity());
        }
        return round($totalPrice);
//        return $this->total_price;
    }

    /**
     * @param mixed $total_price
     */
    public function setTotalPrice($total_price)
    {
        $this->total_price = $total_price;
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
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param mixed $supplier
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
    }


    /**
     * @return ArrayCollection
     */
    public function getWerehouse()
    {
        return $this->werehouse;
    }

    public function addWerehouse(Werehouse $werehouse)
    {
        if (!$this->werehouse->contains($werehouse)) {
            $this->werehouse->add($werehouse);
        }
        return $this;
    }
    public function removeWerehouse(Werehouse $werehouse)
    {
        if ($this->werehouse->contains($werehouse)) {
            $this->werehouse->removeElement($werehouse);
        }
        return $this;
    }


    /**
     * @return ArrayCollection
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    public function addInvoice(WerehouseOrderInvoice $invoice)
    {
        if (!$this->invoice->contains($invoice)) {
            $this->invoice->add($invoice);
        }
        return $this;
    }
    public function removeInvoice(WerehouseOrderInvoice $invoice)
    {
        if ($this->invoice->contains($invoice)) {
            $this->invoice->removeElement($invoice);
        }
        return $this;
    }


    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param $werehouseId
     * @return Werehouse
     */
    public function getWerehouseById($werehouseId)
    {
        foreach ($this->getWerehouse() as $werehouse){
            if($werehouse->getId()==$werehouseId)
                return $werehouse;
        }
        return null;
    }

    public function getCodeOrder(){
        return "ONI".$this->getId();
    }
    /**
     * @return mixed
     */
    public function getPay()
    {
        return $this->pay;
    }

    /**
     * @param mixed $pay
     */
    public function setPay($pay)
    {
        $this->pay = $pay;
    }

    /**
     * @return mixed
     */
    public function getPayDate()
    {
        return $this->pay_date;
    }

    /**
     * @param mixed $pay_date
     */
    public function setPayDate($pay_date)
    {
        $this->pay_date = $pay_date;
    }

}