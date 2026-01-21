<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Product\Controller;


use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Exception;
use Product\Entity\Product;
use Product\Form\ProductForm;
use Product\Service\ProductManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeFrontController;
use Sulde\Service\SuldeUserController;
use Users\Entity\User;
use Zend\Paginator\Paginator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UserController extends SuldeUserController
{

    private $entityManager;
    private $productManager;

    public function __construct(EntityManager $entityManager, ProductManager $productManager)
    {
        $this->entityManager = $entityManager;
        $this->productManager = $productManager;
    }

    public function indexAction()
    {
        $product = $this->productManager->getAllForPrice();

        return new ViewModel(['product'=>$product]);
    }

    public function exportPriceAction()
    {
//        $product = $this->productManager->getProductSortTable();
        $product = $this->productManager->getAllForPrice();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('product',$product);
        return $viewModel;
    }

    public function exportPriceSortAction()
    {
        $product = $this->productManager->getAllForPrice();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('product',$product);
        return $viewModel;
    }

    public function sortAction()
    {
        $request = $this->getRequest();

        $result["success"]=0;
        if($request->isPost()){
            try{
                $data = $request->getPost("d");
                foreach ($data as $key=>$item) {
                    $obj = json_decode(json_encode($item));
                    $productId = $obj->id;
                    $sort = @$obj->index;
                    $product = $this->productManager->getById($productId);
                    $product->setSort($sort);
                    $this->entityManager->flush();
                }
                $result["success"]=1;
                $result["msg"]="Ok";
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result["msg"]="not post";
        }
        return new JsonModel($result);
    }
}