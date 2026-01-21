<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Html\Service;


use Html\Entity\Html;

class HtmlManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }


    public function getByKey($key){
        $html = $this->entityManager->getRepository(Html::class)->find($key);
        return $html;
    }

}