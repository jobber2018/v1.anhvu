<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/15/19 11:12 AM
 *
 */


namespace Users\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;


/**
 * @orm\Entity
 * @orm\Table(name="roles")
 */

class Roles
{
    public function __construct() {
        $this->privileges = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue
     */
    private $id;

    /** @orm\Column(type="string", name="name") */
    private $name;


    /** @orm\Column(type="string", name="code") */
    private $code;

    /** @orm\Column(type="datetime", name = "created_date") */
    private $created_date;

    /**
     * Many Users have Many Groups.
     * @orm\ManyToMany(targetEntity="Users\Entity\Privileges", inversedBy="roles")
     * @orm\JoinTable(name="roles_privileges",
     *     joinColumns={@orm\JoinColumn(name="role_id", referencedColumnName="id")},
     *     inverseJoinColumns={@orm\JoinColumn(name="privilege_id", referencedColumnName="id")})
     */
    private $privileges;

    private $user;
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
    public function getCode()
    {
        return $this->code;
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
    public function getPrivileges()
    {
        return $this->privileges;
    }

    /**
     * @param mixed $privileges
     */
    public function setPrivileges($privileges)
    {
        if($privileges!==null)
            $this->privileges->add($privileges);
        $this->privileges = $privileges;
    }

    public function addPrivileges(Privileges $privileges)
    {
        if (!$this->privileges->contains($privileges)) {
            $this->privileges->add($privileges);
            $privileges->addRoles($this);
        }
        return $this;
    }
    
    public function removePrivileges(Privileges $privileges)
    {
        if ($this->privileges->contains($privileges)) {
            $this->privileges->removeElement($privileges);
            $privileges->removeRoles($this);
        }
        return $this;
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
}