<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Product\Controller;

use Product\Entity\Product;
use Product\Entity\ProductPrice;
use Product\Entity\ProductRecommend;
use Product\Form\ProductForm;
use Product\Service\ProductManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\Common\Define;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeAdminController;
use Users\Entity\User;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $productManager;

    public function __construct(EntityManager $entityManager, ProductManager $productManager)
    {
        $this->entityManager = $entityManager;
        $this->productManager = $productManager;
    }


    /**
     * @return ViewModel
     */
    public function indexAction(){

        $request = $this->getRequest();
        if($request->isPost()) {
            $keyword = $this->params()->fromPost('search')['value'];
            $length = $this->params()->fromPost('length', Define::ITEM_PAGE_COUNT);
            $start = $this->params()->fromPost('start', 0);
            $draw = $this->params()->fromPost('draw', 1);

            $tableColumns=$this->params()->fromPost('columns');
            $orderColumnIndex=$this->params()->fromPost('order')[0]['column'];
            $orderDir=$this->params()->fromPost('order')[0]['dir'];//asc or desc
            $orderColumnName=$tableColumns[$orderColumnIndex]['name'];

            $products = $this->productManager->productSearch($keyword, $length, $start, $orderColumnName, $orderDir);
            $productResult = array();
            foreach ($products as $productItem) {
                $tmp['id'] = $productItem->getId();
                $tmp['product_code'] = $productItem->getCode();
                $tmp['pack_code'] = $productItem->getPackCode();
                $tmp['name'] = $productItem->getName();
                $tmp['weight'] = $productItem->getWeight();
                $tmp['pack_unit'] = $productItem->getBoxUnit();
                $tmp['image'] = $productItem->getImg();
                $tmp['unit_name'] = $productItem->getUnit()->getName();
                $tmp['product_price'] = $productItem->getActivePrice()->getPrice();
                $tmp['pack_price'] = $productItem->getPackPrice();
                $tmp['product_price_sale'] = $productItem->getUnitPriceSale();
                $tmp['pack_price_sale'] = $productItem->getPackPriceSale();
                $tmp['product_price_after_sale'] = $productItem->getUnitPriceAfterSale();
                $tmp['pack_price_after_sale'] = $productItem->getPackPriceAfterSale();
                $tmp['inventory'] = $productItem->getInventory();
                $productResult[]=$tmp;
            }
            $result['draw']=$draw;
            $result['recordsTotal']=count($products);
            $result['recordsFiltered']=count($products);
            $result['data']=$productResult;
            return new JsonModel($result);
        }

        $product = $this->productManager->getAll();
        return new ViewModel([
            'product'=>$product
        ]);
    }

    /**
     * @return ViewModel
     */
    public function deletedAction(){
        $product = $this->productManager->getDeleted();
        return new ViewModel([
            'product'=>$product
        ]);
    }

    /**
     * @return ViewModel
     */
    public function inactiveAction(){
        $product = $this->productManager->getInactive();
        return new ViewModel([
            'product'=>$product
        ]);
    }

    /**
     * @return ViewModel
     */
    public function optimizeAction(){
        $product = $this->productManager->getAll();

        $productOptimize=array();
        foreach ($product as $productItem){
            $price= $productItem->getActivePrice()->getPrice();
            $inventory = $productItem->getInventory();
            $averagePrice = $productItem->getAveragePrice();
            if(($inventory>0 && $averagePrice==0) || ($price-$averagePrice<=0))
                $productOptimize[]=$productItem;
            //echo $productItem->getId().': ave: '. $averagePrice .' pri:'.$price. '<br>';
        }
        return new ViewModel([
            'product'=>$productOptimize
        ]);
    }

    public function editAction(){
        $productId = $this->params()->fromRoute('id',0);

        $product = $this->productManager->getById($productId);

        $productCatList = $this->productManager->getProductCatList();
        foreach ($productCatList as $item) {
            $productCatData[$item->getId()] = $item->getName();
        }
        $unitList = $this->productManager->getUnitList();
        foreach ($unitList as $item) {
            $productUnitData[$item->getId()] = $item->getName();
        }

        $form =new ProductForm("edit",$productCatData,$productUnitData);

        $request = $this->getRequest();
        if($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($data);

            if ($form->isValid()) {
                try {
                    $productCode=trim($data["code"]);
                    $packCode=trim($data["pack_code"]);
                    $productCode1=trim($data["code_1"]);
                    $productCode2=trim($data["code_2"]);
                    $productCode3=trim($data["code_3"]);

                    if($packCode==$productCode){
                        $msg='Mã thùng và mã sản phẩm không được giống nhau!';
                        $form->get('pack_code')->setMessages([$msg]);
                        throw new \Exception($msg);
                    }

                    //kiem tra ma san pham da duowc su dung?
                    if($productCode){
                        $productItem = $this->productManager->getBarcode($productCode);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('code')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }

                    //kiem tra ma thung da duowc su dung?
                    if($packCode){
                        $productItem = $this->productManager->getBarcode($packCode);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('pack_code')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }

                    if($productCode1){
                        $productItem = $this->productManager->getBarcode($productCode1);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('code_1')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }
                    if($productCode2){
                        $productItem = $this->productManager->getBarcode($productCode2);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('code_2')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }
                    if($productCode3){
                        $productItem = $this->productManager->getBarcode($productCode3);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('code_3')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }

                    $imageUpload = new ImageUpload('imageFile', $request->getFiles()->toArray(), 'product/');
                    $fileUrl = $imageUpload->upload();
                    if($fileUrl)
                        $product->setImg('/img/'.$fileUrl);

//                    if(!$data["name"]) throw new \Exception("Tên sản phẩm không được để trống!");
                    $product->setName(trim($data["name"]));

                    $productCat = $this->productManager->getCatById($data["product_cat"]);
                    $productUnit = $this->productManager->getUnitById($data["product_unit"]);

                    $product->setProductCat($productCat);
                    $product->setUnit($productUnit);

                    $product->setCode($productCode);
                    $product->setCode1($productCode1);
                    $product->setCode2($productCode2);
                    $product->setCode3($productCode3);

                    $product->setPackCode($packCode);
                    $product->setWeight($data["weight"]);
                    $product->setBoxUnit($data["box_unit"]);
                    $product->setActive($data["active"]);
                    $product->setNoteOrder($data["note_order"]);
                    $product->setNorm($data["norm"]);
                    $product->setNormInput($data["norm_input"]);

                    if(!$data["label_name"])
                        $product->setLabelName($data["weight"]);
                    else
                        $product->setLabelName($data["label_name"]);

                    $product->setGroupId($data["group_id"]);

                    if(!$data["exchange_unit"]) $product->setExchangeUnit(1);
                    else $product->setExchangeUnit($data["exchange_unit"]);

                    if($data["pack_sale_type"]){
                        $product->setPackSaleType($data["pack_sale_type"]);
                        //chon hinh thuc giam gia nhuwng khong nhap gia tri
                        if(!$data["pack_sale_value"]){
                            $product->setPackSaleValue(0);
                        }else
                            $product->setPackSaleValue($data["pack_sale_value"]);
                    }else{
                        //khong chon hinh thuc giam gia => gia tri luon =0
                        $product->setPackSaleType(0);
                        $product->setPackSaleValue(0);
                    }


                    if($data["unit_sale_type"]){
                        $product->setUnitSaleType($data["unit_sale_type"]);
                        //chon hinh thuc giam gia nhuwng khong nhap gia tri
                        if(!$data["unit_sale_value"]){
                            $product->setUnitSaleValue(0);
                        }else
                            $product->setUnitSaleValue($data["unit_sale_value"]);
                    }else{
                        //khong chon hinh thuc giam gia => gia tri luon =0
                        $product->setUnitSaleType(0);
                        $product->setUnitSaleValue(0);
                    }


                    if($data["norm_input"])
                        $product->setNormInput($data["norm_input"]);
                    else
                        $product->setNormInput(0);

                    //sua gia ban
                    if($data["price"] && $product->getActivePrice()->getPrice()!=$data["price"]){
                        $productPrice = new ProductPrice();
                        $productPrice->setProduct($product);
                        $productPrice->setPrice($data["price"]);
                        $productPrice->setActive(1);
                        $productPrice->setCreatedDate(new \DateTime());

//                        $this->userInfo;
                        $userId= $this->userInfo->getId();
                        $user = $this->entityManager->getRepository(User::class)->find($userId);
                        $productPrice->setUser($user);
                        $product->addPrice($productPrice);
                    }

                    $this->entityManager->persist($product);
                    $this->entityManager->flush();

                    $this->flashMessenger()->addSuccessMessage('Đã sửa thông tin sản phẩm ' . $data["name"]);
                    return $this->redirect()->toRoute('product-admin');
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        }else{
            $data=[
                "name"=>$product->getName(),
                "pack_code"=>$product->getPackCode(),
                "code"=>$product->getCode(),
                "code_1"=>$product->getCode1(),
                "code_2"=>$product->getCode2(),
                "code_3"=>$product->getCode3(),
                "product_cat"=>$product->getProductCat()->getId(),
                "product_unit"=>$product->getUnit()->getId(),
                "weight"=>$product->getWeight(),
                "box_unit"=>$product->getBoxUnit(),
                "price"=>$product->getActivePrice()->getPrice(),
                "active"=>$product->getActive(),
                "norm"=>$product->getNorm(),
                "norm_input"=>$product->getNormInput(),
                "exchange_unit"=>$product->getExchangeUnit(),
                "pack_sale_type"=>$product->getPackSaleType(),
                "pack_sale_value"=>$product->getPackSaleValue(),
                "unit_sale_type"=>$product->getUnitSaleType(),
                "unit_sale_value"=>$product->getUnitSaleValue(),
                "label_name"=>$product->getLabelName(),
                "group_id"=>$product->getGroupId(),
                "note_order"=>$product->getNoteOrder()
            ];
            $form->setData($data);
        }
        return new ViewModel(['form'=>$form,'product'=>$product]);
    }

    public function editPriceAction(){
        $productId = $this->params()->fromRoute('id',0);

        $product = $this->productManager->getById($productId);

        $productCatList = $this->productManager->getProductCatList();
        foreach ($productCatList as $item) {
            $productCatData[$item->getId()] = $item->getName();
        }
        $unitList = $this->productManager->getUnitList();
        foreach ($unitList as $item) {
            $productUnitData[$item->getId()] = $item->getName();
        }

        $form =new ProductForm("edit",$productCatData,$productUnitData);

        $request = $this->getRequest();
        if($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($data);

            if ($form->isValid()) {
                try {
                    $productCode=trim($data["code"]);
                    $packCode=trim($data["pack_code"]);
                    $productCode1=trim($data["code_1"]);
                    $productCode2=trim($data["code_2"]);
                    $productCode3=trim($data["code_3"]);

                    if($packCode==$productCode){
                        $msg='Mã thùng và mã sản phẩm không được giống nhau!';
                        $form->get('pack_code')->setMessages([$msg]);
                        throw new \Exception($msg);
                    }

                    //kiem tra ma san pham da duowc su dung?
                    if($productCode){
                        $productItem = $this->productManager->getBarcode($productCode);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('code')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }

                    //kiem tra ma thung da duowc su dung?
                    if($packCode){
                        $productItem = $this->productManager->getBarcode($packCode);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('pack_code')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }

                    if($productCode1){
                        $productItem = $this->productManager->getBarcode($productCode1);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('code_1')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }
                    if($productCode2){
                        $productItem = $this->productManager->getBarcode($productCode2);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('code_2')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }
                    if($productCode3){
                        $productItem = $this->productManager->getBarcode($productCode3);
                        if($productItem){
                            if($productItem[0]->getId()!=$productId){
                                $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                                $form->get('code_3')->setMessages([$msg]);
                                throw new \Exception($msg);
                            }
                        }
                    }

                    $imageUpload = new ImageUpload('imageFile', $request->getFiles()->toArray(), 'product/');
                    $fileUrl = $imageUpload->upload();
                    if($fileUrl)
                        $product->setImg('/img/'.$fileUrl);

//                    if(!$data["name"]) throw new \Exception("Tên sản phẩm không được để trống!");
                    $product->setName(trim($data["name"]));

                    $productCat = $this->productManager->getCatById($data["product_cat"]);
                    $productUnit = $this->productManager->getUnitById($data["product_unit"]);

                    $product->setProductCat($productCat);
                    $product->setUnit($productUnit);

                    $product->setCode($productCode);
                    $product->setCode1($productCode1);
                    $product->setCode2($productCode2);
                    $product->setCode3($productCode3);

                    $product->setPackCode($packCode);
                    $product->setWeight($data["weight"]);
                    $product->setBoxUnit($data["box_unit"]);
                    $product->setActive($data["active"]);
                    $product->setNoteOrder($data["note_order"]);
                    $product->setNorm($data["norm"]);
                    $product->setNormInput($data["norm_input"]);

                    if(!$data["label_name"])
                        $product->setLabelName($data["weight"]);
                    else
                        $product->setLabelName($data["label_name"]);

                    $product->setGroupId($data["group_id"]);

                    if(!$data["exchange_unit"]) $product->setExchangeUnit(1);
                    else $product->setExchangeUnit($data["exchange_unit"]);

                    if($data["pack_sale_type"]){
                        $product->setPackSaleType($data["pack_sale_type"]);
                        //chon hinh thuc giam gia nhuwng khong nhap gia tri
                        if(!$data["pack_sale_value"]){
                            $product->setPackSaleValue(0);
                        }else
                            $product->setPackSaleValue($data["pack_sale_value"]);
                    }else{
                        //khong chon hinh thuc giam gia => gia tri luon =0
                        $product->setPackSaleType(0);
                        $product->setPackSaleValue(0);
                    }


                    if($data["unit_sale_type"]){
                        $product->setUnitSaleType($data["unit_sale_type"]);
                        //chon hinh thuc giam gia nhuwng khong nhap gia tri
                        if(!$data["unit_sale_value"]){
                            $product->setUnitSaleValue(0);
                        }else
                            $product->setUnitSaleValue($data["unit_sale_value"]);
                    }else{
                        //khong chon hinh thuc giam gia => gia tri luon =0
                        $product->setUnitSaleType(0);
                        $product->setUnitSaleValue(0);
                    }


                    if($data["norm_input"])
                        $product->setNormInput($data["norm_input"]);
                    else
                        $product->setNormInput(0);

                    //sua gia ban
                    if($data["price"] && $product->getActivePrice()->getPrice()!=$data["price"]){
                        $productPrice = new ProductPrice();
                        $productPrice->setProduct($product);
                        $productPrice->setPrice($data["price"]);
                        $productPrice->setActive(1);
                        $productPrice->setCreatedDate(new \DateTime());

//                        $this->userInfo;
                        $userId= $this->userInfo->getId();
                        $user = $this->entityManager->getRepository(User::class)->find($userId);
                        $productPrice->setUser($user);
                        $product->addPrice($productPrice);
                    }

                    $this->entityManager->persist($product);
                    $this->entityManager->flush();

                    $this->flashMessenger()->addSuccessMessage('Đã sửa thông tin sản phẩm ' . $data["name"]);
                    return $this->redirect()->toRoute('report-admin',['action'=>'product']);
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        }else{
            $data=[
                "name"=>$product->getName(),
                "pack_code"=>$product->getPackCode(),
                "code"=>$product->getCode(),
                "code_1"=>$product->getCode1(),
                "code_2"=>$product->getCode2(),
                "code_3"=>$product->getCode3(),
                "product_cat"=>$product->getProductCat()->getId(),
                "product_unit"=>$product->getUnit()->getId(),
                "weight"=>$product->getWeight(),
                "box_unit"=>$product->getBoxUnit(),
                "price"=>$product->getActivePrice()->getPrice(),
                "active"=>$product->getActive(),
                "norm"=>$product->getNorm(),
                "norm_input"=>$product->getNormInput(),
                "exchange_unit"=>$product->getExchangeUnit(),
                "pack_sale_type"=>$product->getPackSaleType(),
                "pack_sale_value"=>$product->getPackSaleValue(),
                "unit_sale_type"=>$product->getUnitSaleType(),
                "unit_sale_value"=>$product->getUnitSaleValue(),
                "label_name"=>$product->getLabelName(),
                "group_id"=>$product->getGroupId(),
                "note_order"=>$product->getNoteOrder()
            ];
            $form->setData($data);
        }
        return new ViewModel(['form'=>$form,'product'=>$product]);
    }
    public function addAction(){

        $productCatList = $this->productManager->getProductCatList();
        foreach ($productCatList as $item) {
            $productCatData[$item->getId()] = $item->getName();
        }
        $unitList = $this->productManager->getUnitList();
        foreach ($unitList as $item) {
            $productUnitData[$item->getId()] = $item->getName();
        }

        $form =new ProductForm("add",$productCatData,$productUnitData);

        $request = $this->getRequest();

        if($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($data);
//            $form->get('pack_code')->setMessages(['abc']);
            if ($form->isValid()) {
                try {
                    $productCode=trim($data["code"]);
                    $packCode=trim($data["pack_code"]);

                    if($packCode==$productCode){
                        $msg='Mã thùng và mã sản phẩm không được giống nhau!';
                        $form->get('pack_code')->setMessages([$msg]);
                        throw new \Exception($msg);
                    }

                    //kiem tra ma san pham da duowc su dung?
                    if($productCode){
                        $productItem = $this->productManager->getBarcode($productCode);
                        if($productItem){
                            $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                            $form->get('code')->setMessages([$msg]);
                            throw new \Exception($msg);
                        }
                    }
                    //kiem tra ma thung da duowc su dung?
                    if($packCode){
                        $productItem = $this->productManager->getBarcode($packCode);
                        if($productItem){
                            $msg='<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!';
                            $form->get('pack_code')->setMessages([$msg]);
                            throw new \Exception($msg);
                        }
                    }

                    $product = new Product();
                    $imageUpload = new ImageUpload('imageFile', $request->getFiles()->toArray(), 'product/');
                    $fileUrl = $imageUpload->upload();
                    if($fileUrl)
                        $product->setImg('/img/'.$fileUrl);

                    $product->setName($data["name"]);

                    $productCat = $this->productManager->getCatById($data["product_cat"]);
                    $productUnit = $this->productManager->getUnitById($data["product_unit"]);

                    $product->setProductCat($productCat);
                    $product->setUnit($productUnit);

                    $product->setCode($productCode);
                    $product->setPackCode($packCode);
                    $product->setWeight($data["weight"]);
                    $product->setBoxUnit($data["box_unit"]);
                    $product->setInventory(0);
                    $product->setAveragePrice(0);
                    $product->setActive(1);
                    $product->setIsDel(0);
                    $product->setNorm($data["norm"]);
                    $product->setNoteOrder($data["note_order"]);

                    if(!$data["label_name"])
                        $product->setLabelName($data["weight"]);
                    else
                        $product->setLabelName($data["label_name"]);
                    $product->setGroupId($data["group_id"]);

                    $product->setCreatedDate(new \DateTime());

                    if(!$data["exchange_unit"]) $product->setExchangeUnit(1);
                    else $product->setExchangeUnit($data["exchange_unit"]);

                    if($data["norm_input"])
                        $product->setNormInput($data["norm_input"]);
                    else
                        $product->setNormInput(0);
                    $product->setExchangeUnit(1);


                    $productPrice = new ProductPrice();
                    $productPrice->setProduct($product);
                    $productPrice->setPrice($data["price"]);
                    $productPrice->setActive(1);
                    $productPrice->setCreatedDate(new \DateTime());

//                    $this->userInfo;
                    $userId= $this->userInfo->getId();
                    $user = $this->entityManager->getRepository(User::class)->find($userId);
                    $productPrice->setUser($user);
                    $product->addPrice($productPrice);

                    $this->entityManager->persist($product);
                    $this->entityManager->flush();

                    $this->flashMessenger()->addSuccessMessage('Thêm mới thành công sản phẩm ' . $data["name"]);
                    return $this->redirect()->toRoute('report-admin',["action"=>'product']);
                } catch (\Exception $e) {
                    $message = $e->getMessage();
//                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        }else{
            $productId = $this->params()->fromRoute('id',0);
            if($productId>0){
                $product = $this->productManager->getById($productId);

                $data=[
                    "name"=>$product->getName(),
                    "product_cat"=>$product->getProductCat()->getId(),
                    "product_unit"=>$product->getUnit()->getId(),
                    "weight"=>$product->getWeight(),
                    "box_unit"=>$product->getBoxUnit(),
                    "price"=>$product->getActivePrice()->getPrice(),
                    "active"=>1,
                    "norm"=>$product->getNorm(),
                    "norm_input"=>$product->getNormInput(),
                    "note_order"=>$product->getNoteOrder()
                ];
                $form->setData($data);
            }
        }
        return new ViewModel(['form'=>$form]);
    }

    public function priceAction(){
        $productId = $this->params()->fromRoute('id',0);
        $product = $this->productManager->getById($productId);
        return new ViewModel([
            'product'=>$product
            ,'sell'=>$product->getSell()
            ,'werehouse'=>$product->getWerehouse()
        ]);
    }

    public function detailAction(){
        $productId = $this->params()->fromRoute('id',0);
        $product = $this->productManager->getById($productId);
        return new ViewModel([
            'product'=>$product
            ,'sell'=>$product->getSell()
            ,'werehouse'=>$product->getWerehouse()
        ]);
    }

    public function sortableAction()
    {
        $request = $this->getRequest();
        $result["success"]=0;
        if($request->isPost()){
            try{
                $data = $request->getPost("d");
                foreach ($data as $key=>$item) {
                    $obj = json_decode(json_encode($item));
                    $productId = $obj->id;
                    $sort = $obj->index;
                    $product = $this->productManager->getById($productId);
                    $product->setSortPriceTable($sort);
                    $this->entityManager->flush();
                }
                $result["success"]=1;
                $result["msg"]="Ok";
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);
        }

        $product = $this->productManager->getProductSortTable();

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariable('product',$product);
        return $viewModel;
    }

    public function recommendAction(){
        $products = $this->productManager->getRecommend();
        $productList = $this->productManager->getAll();
        return new ViewModel([
            'products'=>$products
            ,'productList'=>$productList
        ]);
    }

    public function addRecommendAction(){
        $request = $this->getRequest();
        $result["success"]=0;
        if($request->isPost()){
            try{
                $productId = $request->getPost("id");
                $product = $this->productManager->getById($productId);

                $productRecommend = new ProductRecommend();
                $productRecommend->setProduct($product);
                $productRecommend->setSort(0);
                $this->entityManager->persist($productRecommend);
                $this->entityManager->flush();

                $result["success"]=1;
                $result["msg"]="Ok";
            }catch (\Exception $e){
                $result['success']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result["msg"]="not post";
        }
        return new JsonModel($result);
    }

    public function updateRecommendAction(){
        $request = $this->getRequest();
        $result["success"]=0;
        if($request->isPost()){
            try{
                $data = $request->getPost("d");
                foreach ($data as $item) {
                    $obj = json_decode(json_encode($item));
                    $productId = $obj->id;
                    $sort = $obj->index;

                    $productRecommend = $this->productManager->getRecommendByProductId($productId);
                    $productRecommend->setSort($sort);
                    $this->entityManager->persist($productRecommend);
                    $this->entityManager->flush();
                }

                $result["success"]=1;
                $result["msg"]="OK";
            }catch (\Exception $e){
                $result['success']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result["msg"]="not post";
        }
        return new JsonModel($result);
    }

    public function deleteRecommendAction(){
        $request = $this->getRequest();
        $result["success"]=0;
        if($request->isPost()){
            try{
                $productId = $request->getPost("id");
                $productRecommend = $this->productManager->getRecommendByProductId($productId);
                $this->entityManager->remove($productRecommend);
                $this->entityManager->flush();
                $result["success"]=1;
                $result["msg"]="Ok";
            }catch (\Exception $e){
                $result['success']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result["msg"]="not post";
        }
        return new JsonModel($result);
    }

    public function updateBarcodeAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $productId=$request->getPost('id');
                $productCode=$request->getPost('product_code');
                $packCode=$request->getPost('pack_code');

                if($packCode==$productCode)
                    throw new \Exception('Mã thùng và mã sản phẩm không được giống nhau!');
                $errorMsg=[];
                if($productCode){
                    $productItem = $this->productManager->getBarcode($productCode);
                    if($productItem)
                        if($productItem[0]->getId()!=$productId)
                            $errorMsg[]=array(
                                "code"=>'productCode'
                                ,"message"=>'<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!'
                            );

                }
                if($packCode){
                    $productItem = $this->productManager->getBarcode($packCode);
                    if($productItem)
                        if($productItem[0]->getId()!=$productId)
                            $errorMsg[]=array(
                                "code"=>'packCode'
                                ,"message"=>'<b>'.$productItem[0]->getName().' | '.$productItem[0]->getWeight().'</b> đang sử dụng!'
                            );

                }

                if($errorMsg){
                    $result['data'] = $errorMsg;
                    throw new \Exception('Có mã đang được sử dụng!');
                }

                $product = $this->productManager->getById($productId);
                $product->setCode($productCode);
                $product->setPackCode($packCode);
                $this->entityManager->flush();

                $result["status"]=1;
                $result['message'] = 'Cập nhật thành công!';
            }catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }

            return new JsonModel($result);
        }else{
            $productId=$request->getQuery('id');
            $product = $this->productManager->getById($productId);
            $tmp['id']=$product->getId();
            $tmp['name']=$product->getName();
            $tmp['weight']=$product->getWeight();
            $tmp['product_code']=$product->getCode();
            $tmp['pack_code']=$product->getPackCode();
            $result["status"]=1;
            $result["data"]=$tmp;
            return new JsonModel($result);
        }
    }
}