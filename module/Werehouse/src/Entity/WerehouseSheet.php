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
 * @orm\Table(name="warehouse_sheet")
 */

class WerehouseSheet
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $user;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /**
     * @orm\OneToMany(targetEntity="Werehouse\Entity\WerehouseCheck", mappedBy="werehouseSheet", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="sheet_id")
     * @orm\OrderBy({"id" = "DESC"})
     */
    protected $werehouseCheck;

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
     * @return WerehouseCheck
     */
    public function getWerehouseCheck()
    {
        return $this->werehouseCheck;
    }

    /**
     * @param mixed $werehouseCheck
     */
    public function setWerehouseCheck($werehouseCheck)
    {
        $this->werehouseCheck = $werehouseCheck;
    }

    public function isProductAlreadyExist($p_proId)
    {
        foreach ($this->getWerehouseCheck() as $werehouseCheckItem){
            if($werehouseCheckItem->getProduct()->getId()==$p_proId) return 1;
        }
        return 0;
    }
}