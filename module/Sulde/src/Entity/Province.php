<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/21/19 9:25 PM
 *
 */


namespace Sulde\Entity;

use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @orm\Table(name="province")
 */
class Province
{
    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue
     */
    private $id;


    /** @orm\Column(type="string", name="code") */
    private $code;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="integer", name="active") */
    private $active;


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

}