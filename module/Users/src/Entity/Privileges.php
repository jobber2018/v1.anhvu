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
 * @orm\Table(name="privileges")
 */

class Privileges
{

    public function __construct() {
        $this->roles = new ArrayCollection();
    }
    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue
     */
    private $id;

    /** @orm\Column(type="string", name="name") */
    private $name;


    /** @orm\Column(type="string", name="controller") */
    private $controller;

    /** @orm\Column(type="string", name="action") */
    private $action;

    /** @orm\Column(type="string", name="allow") */
    private $allow;

    /**
     * Many Groups have Many Roles.
     * @orm\ManyToMany(targetEntity="Users\Entity\Roles", mappedBy="privileges")
     */
    private $roles;

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
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param mixed $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getAllow()
    {
        return $this->allow;
    }

    /**
     * @param mixed $allow
     */
    public function setAllow($allow)
    {
        $this->allow = $allow;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        if($roles!==null)
            $this->roles->add($roles);
        $this->roles = $roles;
    }

    public function addRoles(Roles $roles)
    {
        if (!$this->roles->contains($roles)) {
            $this->roles->add($roles);
            $roles->addPrivileges($this);
        }
        return $this;
    }

    public function removeRoles(Roles $roles){
        if ($this->roles->contains($roles)) {
            $this->roles->removeElement($roles);
            $roles->removePrivileges($this);
        }
        return $this;
    }
}