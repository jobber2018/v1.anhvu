<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Api\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @orm\Table(name="zalo_app_address")
 */

class ZaloAppAddress
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="string", name="`name`") */
    private $name;

    /** @orm\Column(type="integer", name="`default`") */
    private $default;

    /** @orm\Column(type="string", name="phone") */
    private $phone;

    /** @orm\Column(type="string", name="street") */
    private $street;

    /** @orm\Column(type="string", name="ward") */
    private $ward;

    /** @orm\Column(type="string", name="distric") */
    private $distric;

    /** @orm\Column(type="string", name="province") */
    private $province;

    /** @orm\Column(type="string", name="wdp") */
    private $wdp;

    /**
     * @orm\ManyToOne(targetEntity="Api\Entity\ZaloApp", inversedBy="zaloAddress" )
     * @orm\JoinColumn(name="zalo_app_id", referencedColumnName="id")
     */
    private $zaloapp;

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
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getWard()
    {
        return $this->ward;
    }

    /**
     * @param mixed $ward
     */
    public function setWard($ward)
    {
        $this->ward = $ward;
    }

    /**
     * @return mixed
     */
    public function getDistric()
    {
        return $this->distric;
    }

    /**
     * @param mixed $distric
     */
    public function setDistric($distric)
    {
        $this->distric = $distric;
    }

    /**
     * @return mixed
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * @param mixed $province
     */
    public function setProvince($province)
    {
        $this->province = $province;
    }

    /**
     * @return mixed
     */
    public function getZaloapp()
    {
        return $this->zaloapp;
    }

    /**
     * @param ZaloApp $zaloapp
     */
    public function setZaloapp(ZaloApp $zaloapp)
    {
        $this->zaloapp = $zaloapp;
    }

    /**
     * @return mixed
     */
    public function getWdp()
    {
        return $this->wdp;
    }

    /**
     * @param mixed $wdp
     */
    public function setWdp($wdp)
    {
        $this->wdp = $wdp;
    }

}