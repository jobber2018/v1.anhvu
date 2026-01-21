<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Html\Entity;

use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @orm\Table(name="html")
 */

class Html
{

    /**
     * @orm\Id
     * @orm\Column(type="string")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $key;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="string", name="content") */
    private $content;

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
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
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

}