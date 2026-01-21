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
 * @orm\Table(name="users")
 */

class User
{
    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue
     */
    private $id;

    private $privileges;

    /** @orm\Column(type="string", name="fullname") */
    private $fullname;

    /** @orm\Column(type="string", name ="username") */
    private $username;

    /** @orm\Column(type="string", name="mobile") */
    private $mobile;

    /** @orm\Column(type="string", name ="password") */
    private $password;

    /** @orm\Column(type="datetime", name = "birthday") */
    private $birthday;

    /** @orm\Column(type="string", name = "email") */
    private $email;

    /** @orm\Column(type="integer", name="balance") */
    private $balance;

    /** @orm\Column(type="datetime", name = "created_date") */
    private $created_date;

    /** @orm\Column(type="datetime", name = "login_date") */
    private $login_date;

    /** @orm\Column(type="integer", name = "status") */
    private $status;

    /** @orm\Column(type="string", name = "login_info") */
    private $login_info;

    /** @orm\Column(type="string", name = "device_id") */
    private $device_id;

    /** @orm\Column(type="string", name = "role") */
    private $role;

    /** @orm\Column(type="string", name = "avatar") */
    private $avatar;

    /** @orm\Column(type="string", name = "social_id") */
    private $social_id;

    /** @orm\Column(type="string", name = "token") */
    private $token;

    /** @orm\Column(type="datetime", name = "token_created_date") */
    private $token_created_date;

    /** @orm\Column(type="string", name = "position") */
    private $position;
    /** @orm\Column(type="string", name = "position_option") */
    private $position_option;
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
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * @param mixed $fullname
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param mixed $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        if(!$this->balance) return 0;
        return $this->balance ;
    }

    /**
     * @param mixed $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
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
    public function getLoginDate()
    {
        return $this->login_date;
    }

    /**
     * @param mixed $login_date
     */
    public function setLoginDate($login_date)
    {
        $this->login_date = $login_date;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getLoginInfo()
    {
        return $this->login_info;
    }

    /**
     * @param mixed $login_info
     */
    public function setLoginInfo($login_info)
    {
        $this->login_info = $login_info;
    }

    /**
     * @return mixed
     */
    public function getDeviceId()
    {
        return $this->device_id;
    }

    /**
     * @param mixed $device_id
     */
    public function setDeviceId($device_id)
    {
        $this->device_id = $device_id;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
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
    public function getSocialId()
    {
        return $this->social_id;
    }

    /**
     * @param mixed $social_id
     */
    public function setSocialId($social_id)
    {
        $this->social_id = $social_id;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getTokenCreatedDate()
    {
        return $this->token_created_date;
    }

    /**
     * @param mixed $token_created_date
     */
    public function setTokenCreatedDate($token_created_date)
    {
        $this->token_created_date = $token_created_date;
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
        $this->privileges = $privileges;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getPositionOption()
    {
        return $this->position_option;
    }

    /**
     * @param mixed $position_option
     */
    public function setPositionOption($position_option)
    {
        $this->position_option = $position_option;
    }
}