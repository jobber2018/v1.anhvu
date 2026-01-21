<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-25
 * Time: 11:42
 */

namespace Sulde\Service\Common;


class SessionManager
{
    private $userInfo;

    public function __construct()
    {
        $this->userInfo=$_SESSION['userInfo'];
    }

    public function getUserId(){
        if($this->userInfo)
            return $this->userInfo->getId();
        else return null;
    }

    public function getUser(){
        return $this->userInfo;
    }
}