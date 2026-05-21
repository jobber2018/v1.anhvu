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
                $data = json_decode($request->getPost("d"));
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

    public function activityAction()
    {
        $request = $this->getRequest();

        $result["success"]=0;
        if($request->isPost()){
            try{                
                $productId = $this->params()->fromRoute('id',0);
                $length = $this->params()->fromPost('length', Define::ITEM_PAGE_COUNT);
                $start = $this->params()->fromPost('start', 0);
                $draw = $this->params()->fromPost('draw', 1);

                $activity = $this->productManager->getActivity($productId, $length, $start);
                $data = array();
                foreach ($activity as $activityItem) {
                    $tmp = [
                        "id"=>$activityItem->getId(),
                        "change"=>$activityItem->getChange(),
                        "created_date"=>Common::formatDateTime($activityItem->getCreatedDate()),
                        "user"=>$activityItem->getUser()->getFullname(),
                        "note"=>$activityItem->getNote(),
                    ];
                    $data[] = $tmp;
                }
                $result["success"]=1;
                $result['start']=$start+1;
                $result['recordsTotal']=count($activity);
                $result['recordsFiltered']=count($activity);
                $result["data"]=$data;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result["msg"]="not post";
        }
        return new JsonModel($result);
    }
    public function inventoryHistoryAction()
    {
        $request = $this->getRequest();

        $result["success"]=0;
        if($request->isPost()){
            try{                
                $productId = $this->params()->fromRoute('id',0);
                $length = $this->params()->fromPost('length', Define::ITEM_PAGE_COUNT);
                $start = $this->params()->fromPost('start', 0);
                $draw = $this->params()->fromPost('draw', 1);

                $inventory = $this->productManager->getInventoryHistory($productId, $length, $start);
                $data = array();
                foreach ($inventory as $inventoryItem) {
                    $tmp = [
                        "id"=>$inventoryItem->getId(),
                        "before_change"=>$inventoryItem->getBeforeChange(),
                        "after_change"=>$inventoryItem->getAfterChange(),
                        "changed"=>$inventoryItem->getAfterChange()-$inventoryItem->getBeforeChange(),
                        "created_date"=>Common::formatDateTime($inventoryItem->getCreatedDate()),
                        "user"=>$inventoryItem->getUser()->getFullname(),
                        "note"=>$inventoryItem->getNote(),
                    ];
                    $data[] = $tmp;
                }
                $result["success"]=1;
                $result['start']=$start+1;
                $result['recordsTotal']=count($inventory);
                $result['recordsFiltered']=count($inventory);
                $result["data"]=$data;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result["msg"]="not post";
        }
        return new JsonModel($result);
    }

    public function byHistoryAction()
    {
        $request = $this->getRequest();

        $result["success"]=0;
        if($request->isPost()){
            try{                
                $productId = $this->params()->fromRoute('id',0);
                $length = $this->params()->fromPost('length', Define::ITEM_PAGE_COUNT);
                $start = $this->params()->fromPost('start', 0);
                $draw = $this->params()->fromPost('draw', 1);

                $werehouse = $this->productManager->getByHistory($productId, $length, $start);
                $data = array();
                foreach ($werehouse as $werehouseItem) {
                    $tmp = [
                        "order_id"=>$werehouseItem->getWerehouseOrder()->getId(),
                        "id"=>$werehouseItem->getId(),
                        "status"=>$werehouseItem->getWerehouseOrder()->getStatus(),
                        "supplier_name"=>$werehouseItem->getWerehouseOrder()->getSupplier()->getName(),
                        "price"=>Common::formatMoney($werehouseItem->getPrice()),
                        "created_date"=>Common::formatDateTime($werehouseItem->getWerehouseOrder()->getCreatedDate()),
                        "qty"=>$werehouseItem->getQuantity(),
                        "box_unit"=>$werehouseItem->getBoxUnit(),
                        "total_amount"=>Common::formatMoney($werehouseItem->getPrice()*$werehouseItem->getQuantity())
                    ];
                    $data[] = $tmp;
                }
                $result["success"]=1;
                $result['start']=$start+1;
                $result['recordsTotal']=count($werehouse);
                $result['recordsFiltered']=count($werehouse);
                $result["data"]=$data;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result["msg"]="not post";
        }
        return new JsonModel($result);
    }

    public function sellHistoryAction()
    {
        $request = $this->getRequest();

        $result["success"]=0;
        if($request->isPost()){
            try{                
                $productId = $this->params()->fromRoute('id',0);

                $sells = $this->productManager->getSellHistory($productId);
                $data = array();
                foreach ($sells as $sellItem) {

                    $priceValue = $sellItem->getPriceValue();
                    $qty=$sellItem->getQuantity();
                    $packUnit=$sellItem->getPackUnit();
                    // $discount=$sellItem->getDiscount();
                    //mua theo thung
                    if($sellItem->isPack()){
                        $qtySale=$sellItem->isPack();
                        $unitSale='Thùng';
                        $price=$packUnit*$priceValue;//gia ban theo thung
                    }else{
                        $qtySale=$qty;
                        $unitSale=$sellItem->getProduct()->getUnit()->getName();
                        $price=$priceValue;//gia ban le
                    }
                    
                    $tmp = [
                        "grocery_name"=>$sellItem->getSellOrder()->getGrocery()->getGroceryName(),
                        "sell_id"=>$sellItem->getId(),
                        "order_id"=>$sellItem->getSellOrder()->getId(),
                        "created_date"=>Common::formatDateTime($sellItem->getSellOrder()->getCreatedDate()),
                        "price"=>Common::formatMoney($price),                        
                        "qty"=>$qtySale,
                        "unit_name"=>$unitSale,                        
                        "amount"=>Common::formatMoney($price*$qtySale),
                        "status_id"=>$sellItem->getSellOrder()->getStatus(),
                        "base_qty"=>$sellItem->getQuantity(),                        
                        "status_label"=>ConfigManager::getOrderStatus()[$sellItem->getSellOrder()->getStatus()]
                    ];
                    $data[] = $tmp;
                }
                
                $result["success"]=1;                
                $result["data"] = $data;
            }catch (\Exception $e){
                $result['success']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result["msg"]="not post";
        }
        return new JsonModel($result);
    }
}