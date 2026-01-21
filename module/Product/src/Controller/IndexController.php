<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Product\Controller;

use Product\Service\ProductManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\Common\Common;
use Sulde\Service\SuldeFrontController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends SuldeFrontController
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
        $product = $this->productManager->getAll();
        $viewModel = new ViewModel();
        $viewModel->setVariable('product',$product);
        return $viewModel;
    }

    public function detailAction()
    {
        $productId = $this->params()->fromRoute('id',0);
        $product = $this->productManager->getById($productId);

        $relatedProducts = $this->productManager->getProductCatById($product->getProductCat()->getId());

        return new ViewModel([
            'product'=>$product,
            'relatedProducts'=>$relatedProducts
        ]);
    }

    public function addToCardAction()
    {
        $productId = $this->params()->fromRoute('id',0);
        $request = $this->getRequest();
        try{
//            $_SESSION['my_card']=null;
            $qty=$request->getPost("qty");
            $opt=$request->getPost("opt");

            $card['qty']=$qty;
            $card['opt']=$opt;

            $_SESSION['my_card'][$productId]=$card;

            $result["success"]=1;
            $result["count_product"]=count($_SESSION['my_card']);
            return new JsonModel($result);

        }catch (\Exception $e){
            $result["success"]=0;
            $result["msg"]=$e->getMessage();
            return new JsonModel($result);
        }
    }
    public function myCartAction()
    {
        $myCard=array();
        $totalPrice=0;
        if($_SESSION['my_card']){
            $productIds=array();
            foreach ($_SESSION['my_card'] as $key=>$card){
                $productIds[]=$key;
            }

            $products = $this->productManager->getMyCard($productIds);
            foreach ($products as $productItem){
                $product["id"] = $productItem->getId();
                $product["name"] = $productItem->getName();
                $product["weight"] = $productItem->getWeight();
                $product["img"] = ($productItem->getImg())?$productItem->getImg():'/img/icons/no-image-icon.png';
                $product["qty"] = $_SESSION['my_card'][$productItem->getId()]['qty'];
                $product["opt"] = $_SESSION['my_card'][$productItem->getId()]['opt'];
                $product["box_unit"] = $productItem->getBoxUnit();
                $product["unit_name"] = $productItem->getUnit()->getName();

                $activePrice = $productItem->getActivePrice()->getPrice();
                if($product["opt"]==1)
                    $price = $activePrice*$product["qty"]*$product["box_unit"];
                else
                    $price = $activePrice*$product["qty"];

                $totalPrice=$totalPrice+$price;

                $product["price"] = $price;
                $myCard[]=$product;
            }
        }

        return new ViewModel([
            'myCard'=>$myCard,
            'total_price'=>$totalPrice
        ]);
    }

    public function deleteProMyCartAction(){
//        $pId = $this->params()->fromRoute('id',0);
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $pId = $request->getPost("id");
                $product=array();
                foreach ($_SESSION['my_card'] as $key=>$item){
                    if($key!=$pId){
                        $product[$key]=$item;
                    }
                }
                $_SESSION['my_card']=$product;

                $result['status']=1;
                $result['msg']='Đã bỏ sản phẩm ra khỏi giỏ hàng!';
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result['status']=0;
            $result['msg']="Không thể thực hiện xoá dữ liệu!";
        }
        return new JsonModel($result);
    }
    public function priceAction()
    {
        return $this->redirect()->toRoute('product-front');
        /*$product = $this->productManager->getAll();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('product',$product);
        return $viewModel;*/
    }

    public function autocompleteAction()
    {
        $data = $this->params()->fromPost();
        $keyword=$data["q"];

        $product = $this->productManager->searchPaginator($keyword,20,0);
        $products=array();
        foreach ($product as $productItem){
            $tmp['id']=$productItem->getId();
            $tmp['name']=$productItem->getName();
            $tmp['subCode']=$productItem->getSubCode();
            $tmp['inventory']=$productItem->getInventory();

            $tmp["price"]=$productItem->getUnitPriceAfterSale();//gia ban le sau khi tru chiet khau
            $tmp["priceUnitDiscount"]=$productItem->getUnitPriceSale();//so tien chiet khau theo san pham

            $tmp["packPrice"]=$productItem->getPackPriceAfterSale();//gia thung sau khi tru chiet khau
            $tmp["pricePackDiscount"]=$productItem->getPackPriceSale();//so tien chiet khau theo thung

            $tmp['image']=$productItem->getImg();
            $tmp['note']=$productItem->getNoteOrder();
            $tmp['unit']=$productItem->getUnit()->getName();
            $tmp['packUnit']=$productItem->getBoxUnit();
            $tmp['weight']=$productItem->getWeight();
//            $tmp['exchangeUnit']=$productItem->getExchangeUnit();
            $products[]=$tmp;
        }
        return new JsonModel($products);
    }
}