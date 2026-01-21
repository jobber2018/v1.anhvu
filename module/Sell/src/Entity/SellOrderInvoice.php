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
use Product\Entity\Product;

/**
 * @orm\Entity
 * @orm\Table(name="sell_order_invoice")
 */

class SellOrderInvoice
{

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * MANY-TO-ONE BIDIRECTIONAL, OWNING SIDE
     * @orm\ManyToOne(targetEntity="Sell\Entity\SellOrder", inversedBy="invoice")
     * @orm\JoinColumn(name="sell_order_id", referencedColumnName="id")
     */
    private $sellOrder;

    /** @orm\Column(type="string", name="path") */
    private $path;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="upload_by", referencedColumnName="id")
     */
    private $user;

    /** @orm\Column(type="datetime", name="upload_date") */
    private $upload_date;

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
    public function getUploadDate()
    {
        return $this->upload_date;
    }

    /**
     * @param mixed $upload_date
     */
    public function setUploadDate($upload_date)
    {
        $this->upload_date = $upload_date;
    }

    /**
     * @return mixed
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

}