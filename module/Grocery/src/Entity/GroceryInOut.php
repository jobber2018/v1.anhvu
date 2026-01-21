<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Grocery\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @orm\Table(name="grocery_in_out")
 */

class GroceryInOut
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="string", name="type") */
    private $type;

    /**
     * @orm\ManyToOne(targetEntity="Grocery\Entity\Grocery", inversedBy="groceryInOut" )
     * @orm\JoinColumn(name="grocery_id", referencedColumnName="id")
     */
    private $grocery;


    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /** @orm\Column(type="string", name="lat") */
    private $lat;

    /** @orm\Column(type="string", name="lng") */
    private $lng;

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param mixed $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param mixed $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    public function getLatLng(){
        $result='';
        if($this->getLat() && $this->getLng())
            $result = $this->getLat() .'/'. $this->getLng();
        return $result;
    }
}