<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Doctrine\ORM\EntityManager;
use Exception;
use Hotels\Entity\Hotel;
use Hotels\Service\HotelManage;
use Landmarks\Entity\Landmarks;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\SuldeFrontController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends SuldeFrontController
{

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        $configManage = new ConfigManager();
        $view = new ViewModel([
            'geoKey'=>$configManage->getGeoKey()
        ]);
//        $view->setTerminal(true);
        $this->layout()->setTemplate('home-layout');
        $view->setVariable('p_lat',$this->params()->fromQuery('lat', ''));
        $view->setVariable('p_lng',$this->params()->fromQuery('lng', ''));
        $view->setVariable('p_hotelId',$this->params()->fromQuery('if', ''));
        $view->setVariable('userInfo',$this->userInfo);
        return $view;
    }

    public function markersAction(){

        $lat = $this->params()->fromRoute("lat");
        $lng = $this->params()->fromRoute("lng");

        //check lat & lng, neu khoong co can dungf geo server de lay

        $hotelManage = new HotelManage($this->entityManager);
        $query = $hotelManage->getHotelLocation($lat,$lng);
        $hotels = $query->getResult();
        $result=array();
        foreach ($hotels as $item){
            $o["name"]=$item["name"];
            $o["address"]=$item["address"];
            $o["price"]=$item["price"];
            $o["default_img"]=$item["default_img"];
            $o["promotion"]=$item["promotion"];
            $o["id"]=$item["id"];
            $o["lat"]=$item["lat"];
            $o["lng"]=$item["lng"];
            $o["type"]=$item["type"];
            $result[]=$o;
        }
        return new JsonModel($result);
    }

    public function hotelInfoAction(){
        try{
            $id = $this->params()->fromRoute("id");

            $view = new ViewModel();
            $view->setTerminal(true);
            if(!$id) throw new Exception("Id empty");
            $hotelManage = new HotelManage($this->entityManager);
            $hotel = $hotelManage->getById($id);
            $view->setVariable('hotel',$hotel);

            $view->setVariable('userInfo',$this->userInfo);

            //update view
            $hotel->setView($hotel->getView()+1);
            $this->entityManager->flush();

            return $view;
        }catch (\Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public function aliasLandmarksAction(){
        $result=array();
        $hotelManager = $this->entityManager->getRepository(Landmarks::class);
        $allHotel = $hotelManager->findAll();

        foreach ($allHotel as $hotelItem){
//            $hotel = $hotelManager->find($hotelItem->getId());
            $alias = Common::convertAlias($hotelItem->getName());
            $hotelItem->setAlias($alias);
            $this->entityManager->flush();
        }

        $result['status']=true;
        return new JsonModel($result);
    }
}
