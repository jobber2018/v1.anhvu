<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Admin\Service;

use Admin\Entity\Activity;
use Doctrine\ORM\Query;
use Grocery\Entity\GroceryCrm;
use Users\Entity\User;

class AdminManager
{
    private $entityManager;
    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $p_data
     * @return Activity
     */
    public function addActivity($p_data){
        $activity = new Activity();
        $activity->setTitle($p_data["title"]);
        $activity->setSeen(0);
        $activity->setMsg($p_data["msg"]);
        $activity->setCreatedDate(new \DateTime());
        $user = $this->entityManager->getRepository(User::class)->find($p_data["uid"]);
        $activity->setUser($user);
        $this->entityManager->persist($activity);
        $this->entityManager->flush();
        return $activity;
    }

    /**
     * @return Activity
     */
    public function getActivityDateNow()
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $dateNow = date("Y-m-d");
        $queryBuilder->select('a')
            ->from(Activity::class, 'a')
            ->where("DATE_FORMAT(a.created_date,'%Y-%m-%d') >=:created_date")
            ->setParameter('created_date', $dateNow)
            ->orderBy('a.created_date', 'DESC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return GroceryCrm
     */
    public function getMessage()
    {
        $configuration = $this->entityManager->getConfiguration();
//        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('a')
            ->from(GroceryCrm::class, 'a')
            ->orderBy('a.created_date', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(50);
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_id
     * @return Activity
     */
    public function getActivityId($p_id)
    {
        $activity = $this->entityManager->getRepository(Activity::class)->find($p_id);
        return $activity;
    }
}