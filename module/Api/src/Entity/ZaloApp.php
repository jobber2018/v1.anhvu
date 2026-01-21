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
 * @orm\Table(name="zalo_app")
 */

class ZaloApp
{

    public function __construct() {
        $this->zaloAddress = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="integer", name="grocery_id") */
    private $grocery_id;

    /** @orm\Column(type="integer", name="zalo_id") */
    private $zalo_id;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="string", name="phone") */
    private $phone;

    /** @orm\Column(type="string", name="avatar") */
    private $avatar;

    /** @orm\Column(type="string", name="source") */
    private $source;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /** @orm\Column(type="datetime", name="access_date") */
    private $access_date;


    /**
     * @orm\OneToMany(targetEntity="Api\Entity\ZaloAppAddress", mappedBy="zaloapp", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="zalo_app_id")
     */
    protected $zaloAddress;

    /**
     * @orm\ManyToOne(targetEntity="Grocery\Entity\Grocery", inversedBy="zalo_app" )
     * @orm\JoinColumn(name="grocery_id", referencedColumnName="id")
     */
    private $grocery;

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
    public function getGroceryId()
    {
        return $this->grocery_id;
    }

    /**
     * @param mixed $grocery_id
     */
    public function setGroceryId($grocery_id)
    {
        $this->grocery_id = $grocery_id;
    }

    /**
     * @return mixed
     */
    public function getZaloId()
    {
        return $this->zalo_id;
    }

    /**
     * @param mixed $zalo_id
     */
    public function setZaloId($zalo_id)
    {
        $this->zalo_id = $zalo_id;
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
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
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
    public function getAccessDate()
    {
        return $this->access_date;
    }

    /**
     * @param mixed $access_date
     */
    public function setAccessDate($access_date)
    {
        $this->access_date = $access_date;
    }

    /**
     * @return mixed
     */
    public function getZaloAppAddress()
    {
        return $this->zaloAddress;
    }

    /**
     * @param $zaloAddress
     * @return void
     */
    public function setZaloAppAddress($zaloAddress)
    {
        $this->zaloAddress = $zaloAddress;
    }

    public function addZaloAppAddress(ZaloAppAddress $zaloAddress)
    {
        if (!$this->zaloAddress->contains($zaloAddress)) {
            $this->zaloAddress[] = $zaloAddress;
            $zaloAddress->setZaloapp($this);
        }
        return $this;
    }

    public function removeZaloAppAddress(ZaloAppAddress $zaloAddress)
    {
        if ($this->zaloAddress->contains($zaloAddress)) {
            $this->zaloAddress->removeElement($zaloAddress);
            // set the owning side to null (unless already changed)
            if ($zaloAddress->getZaloapp() === $this) {
                $zaloAddress->setZaloapp(null);
            }
        }
        return $this;
    }

    /**
     * @return ZaloAppAddress|string|null
     */
    public function getDefaultAddress(){
        foreach ($this->getZaloAppAddress() as $address){
            if($address->getDefault()==1)
                return $address;
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getFullAddress(){
        $defaultAddress = $this->getDefaultAddress();
        if($defaultAddress)
            return $defaultAddress->getStreet().', '.$defaultAddress->getWdp();
        return null;
    }

    /**
     * @return mixed
     */
    public function getGrocery()
    {
        return $this->grocery;
    }

    /**
     * @param mixed $grocery
     */
    public function setGrocery($grocery)
    {
        $this->grocery = $grocery;
    }

}