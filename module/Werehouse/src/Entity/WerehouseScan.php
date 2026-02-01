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
 * @orm\Table(name="warehouse_scan")
 */

class WerehouseScan
{

    public function __construct() {
        $this->werehouseScanDetail = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="string", name="created_by") */
    private $createdBy;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /**
     * @orm\ManyToOne(targetEntity="Supplier\Entity\Supplier", inversedBy="werehouseOrder" )
     * @orm\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;

    /**
     * @orm\OneToMany(targetEntity="Werehouse\Entity\WerehouseScanDetail", mappedBy="werehouseScan", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="werehouse_scan_id")
     */
    protected $werehouseScanDetail;

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
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy): void
    {
        $this->createdBy = $createdBy;
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
    public function setCreatedDate($created_date): void
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
    public function setSupplier($supplier): void
    {
        $this->supplier = $supplier;
    }

    public function getWerehouseScanDetail()
    {
        return $this->werehouseScanDetail;
    }

    public function setWerehouseScanDetail(ArrayCollection $werehouseScanDetail): void
    {
        $this->werehouseScanDetail = $werehouseScanDetail;
    }


    public function addWerehouseScanDetail(WerehouseScanDetail $werehouseScanDetail)
    {
        if (!$this->werehouseScanDetail->contains($werehouseScanDetail)) {
            $this->werehouseScanDetail->add($werehouseScanDetail);
        }
        return $this;
    }
}