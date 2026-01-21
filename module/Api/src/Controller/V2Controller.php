<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Api\Controller;


use Api\Entity\ZaloApp;
use Api\Entity\ZaloAppAddress;
use Api\Service\ApiManager;
use Doctrine\ORM\EntityManager;
use DoctrineORMModule\Proxy\__CG__\GroceryCat\Entity\GroceryCat;
use Grocery\Service\GroceryManager;
use GroceryCat\Service\GroceryCatManager;
use Product\Entity\Product;
use Product\Service\ProductManager;
use Sell\Entity\Sell;
use Sell\Entity\SellOrder;
use Sell\Entity\SellOrderActivity;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\SuldeFrontController;
use Users\Entity\User;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class V2Controller extends SuldeFrontController
{

    private $entityManager;
    private $apiManager;
    private $imageUrl="https://anhvu.store";

    public function __construct(EntityManager $entityManager, ApiManager $apiManager)
    {
        $this->entityManager = $entityManager;
        $this->apiManager = $apiManager;
    }

    public function appSettingAction()
    {
        $result["name"]="NPP Anh Vũ";
        $result["hotline"]="0946 021 021";
        $result["zaloNumber"]="0367 459 253";
        $result["zaloId"]="436445804233649633";
        $result["zaloOAId"]="2037738688872092446";
        $result["subTitle"]="Chúng tôi ở đây để phục vụ bạn";
        $result["productDes"]="Cam kết bán hàng chính hãng, Zalo: 0877 427 569";
        $result["logo"]=$this->imageUrl."/img/logo-zalo-app.png";
        $result["co"]=500000;// chỉ nhứng đơn < co mới thông qua checkout của zalo

        /*$events[]=array(
            "id"=>1
            ,"image"=>"https://zalo-miniapp.github.io/zaui-coffee/dummy/banner-1.jpg"
            ,"screen"=>"honme"
            ,"display"=> "flashsale" //co nhieu gia tri popup, promote
            ,"flashsale_id"=>1
            ,"offer"=>null
        );
        $result["events"]=$events;*/

        $productManager = new ProductManager($this->entityManager);
        $productCatList = $productManager->getProductCatList();

        $categories=array();
        foreach ($productCatList as $key=>$item){
            $category["id"]=$item->getId();
            $category["code"]=$item->getCode();
            $category["name"]=$item->getName();
            $category["special"]=$item->getSpecial();

            $category["image"]=$this->imageUrl."/img/icons/no-image-icon.png";
            if($item->getIcon())
                $category["image"]=$item->getIcon();

            $categories[]=$category;
        }

        $result["categories"]=$categories;

        return new JsonModel($result);
    }

    //extract token từ mini app sang phone number
    public function extractZaloPhoneAction()
    {
        $request = $this->getRequest();
        $result["phone"]='';
        if ($request->isPost()) {

            $data = $request->getContent();

            try {
                if (!$data)
                    throw new \Exception("Dữ liệu đầu vào không đúng!");
                $jsonData = json_decode($data);

                $zaloAccessToken = $jsonData->zalo_access_token;
                $zaloPhoneToken = $jsonData->zalo_phone_token;
                $secretKey="kK6IMNJ3qbTVu66MDHOl";//mã bí mật trong https://developers.zalo.me/

                $endpoint = "https://graph.zalo.me/v2.0/me/info";
                $url = $endpoint . "?access_token=" . $zaloAccessToken ."&code=".$zaloPhoneToken."&secret_key=".$secretKey;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Thực hiện request
                $response = curl_exec($ch);

                // Kiểm tra lỗi
                if (curl_errno($ch)) {
                    $result["status"] = 0;
                } else {
                    //$response='{"data":{"number":"84936408678"},"error":0,"message":"Success"}';
                    $res = json_decode($response, true);
                    $result["phone"]=$res['data']['number'];
                    $result["status"] = 1;
                }
                // Đóng cURL
                curl_close($ch);

            } catch (\Exception $e) {
                $result["status"] = 0;
                $result["message"] = $e->getMessage();
            }
        }else{
            $result["status"] = 0;
            $result["message"] = 'Request fail!';

        }

        return new JsonModel($result);
    }
    public function customerInitAction()
    {
        $request = $this->getRequest();

        if($request->isPost()) {

            $data = $request->getContent();

            try {

                if(!$data)
                    throw new \Exception("Dữ liệu đầu vào không đúng!");

                $jsonData = json_decode($data);

                $name=$jsonData->name;
                $phone=$jsonData->phone;
                $avatar=$jsonData->avatar;
                $source=$jsonData->source;
                $zalo_id=$jsonData->zalo_id;

                //test
//                $zalo_id="7301857000364725690";

                if($zalo_id)
                    $zaloApp = $this->apiManager->getByZaloId($zalo_id);
                else
                    throw new \Exception("Zalo app id cannot be empty!");

                $phone = Common::verifyMobile($phone);

                //lan dau tien mo app, insert thong tin khach hang vao bang zalo_app
                if(!$zaloApp){
                    $zaloApp = new ZaloApp();
                    $zaloApp->setZaloId($zalo_id);
                    $zaloApp->setName($name);
                    $zaloApp->setAvatar($avatar);
                    $zaloApp->setSource($source);
                    $zaloApp->setCreatedDate(new \DateTime());
                    $zaloApp->setAccessDate(new \DateTime());

                    if(strlen($phone)>8){
                        $zaloApp->setPhone($phone);
                        $groceryManager = new GroceryManager($this->entityManager);
                        $grocery = $groceryManager->getByMobile($phone);
                        if($grocery)
                            $zaloApp->setGroceryId($grocery->getId());
                    }

                    $this->entityManager->persist($zaloApp);
                }else{
                    //da co thong tin khach hang => cap nhat lai thong tin moi
                    $zaloApp->setName($name);
                    $zaloApp->setAvatar($avatar);

                    //chi cap nha grocery id khi chua cap nhat vaf tim thay grocery
                    if(!$zaloApp->getGroceryId() & strlen($phone)>8){
                        $zaloApp->setPhone($phone);
                        $groceryManager = new GroceryManager($this->entityManager);
                        $grocery = $groceryManager->getByMobile($phone);
                        if($grocery)
                            $zaloApp->setGroceryId($grocery->getId());
                    }

                    $zaloApp->setAccessDate(new \DateTime());
                }
                $this->entityManager->flush();

                $result["_id"]=$zaloApp->getId();
                $result["name"]=$zaloApp->getName();
                $result["phone"]=$zaloApp->getPhone();
                $result["avatar"]=$zaloApp->getAvatar();
//                $result["source"]=$zaloApp->getSource();

                //address customer
                $customerAddress=array();
                if($zaloApp->getZaloAppAddress()){
                    foreach ($zaloApp->getZaloAppAddress() as $k=>$addressItem){
                        $address["_id"]=$addressItem->getId();
                        $address["name"]=$addressItem->getName();
                        $address["default"]=$addressItem->getDefault();
                        $address["phone"]=$addressItem->getPhone();
                        $address["street"]=$addressItem->getStreet();
                        $address["ward"]=$addressItem->getWard();
                        $address["distric"]=$addressItem->getDistric();
                        $address["province"]=$addressItem->getProvince();
                        $address["wdp"]=$addressItem->getWdp();

                        $customerAddress[]=$address;
                    }
                }
                $result["address"]=$customerAddress;

            } catch (\Exception $e) {
                $result["status"]=0;
                $result["message"]=$e->getMessage();
            }
        }else{
            $result["status"]=0;
            $result["message"]="Method not get!";
        }
        return new JsonModel($result);
    }

    public function customerAddressAction()
    {
        $request = $this->getRequest();

        if($request->isPost()) {
            try {

                $data = $request->getContent();
                if(!$data)
                    throw new \Exception("Dữ liệu đầu vào không đúng!");

                $jsonData = json_decode($data);

                $customerId=$jsonData->_id;

                if(!$customerId)
                    throw new \Exception("Mã khách hàng không được để trống!");

                $zaloApp = $this->apiManager->getById($customerId);
                if(!$zaloApp)
                    throw new \Exception("Không tìm thấy khách hàng phù hợp!");

                //delete all current address
                foreach ($zaloApp->getZaloAppAddress() as $k=>$addressItem){
                    $this->entityManager->remove($addressItem);
                }

                //add address for customer
                foreach ($jsonData->customer->address as $jsonAddressItem){
                    $zaloAppAddress = new ZaloAppAddress();
                    $zaloAppAddress->setName($jsonAddressItem->name);
                    $zaloAppAddress->setDefault($jsonAddressItem->default);
                    $zaloAppAddress->setPhone($jsonAddressItem->phone);
                    $zaloAppAddress->setStreet($jsonAddressItem->street);
                    $zaloAppAddress->setWard($jsonAddressItem->ward);
                    $zaloAppAddress->setDistric($jsonAddressItem->distric);
                    $zaloAppAddress->setProvince($jsonAddressItem->province);
                    $zaloAppAddress->setWdp($jsonAddressItem->wdp);
                    $zaloApp->addZaloAppAddress($zaloAppAddress);

                    //xac dinh khach hang theo phone defalut
                    if($jsonAddressItem->default==1 && $jsonAddressItem->phone){
                        $phone=Common::verifyMobile($jsonAddressItem->phone);
                        $zaloApp->setPhone($phone);

                        $groceryManager = new GroceryManager($this->entityManager);
                        $grocery = $groceryManager->getByMobile($phone);
                        //tim thay grocery theo so dien thoai
                        if($grocery){
                            $zaloApp->setGroceryId($grocery->getId());
                            $grocery->setAddress($zaloApp->getFullAddress());
                        }
                    }
                }

                $this->entityManager->flush();

                $result["status"]=1;
                $result["message"]="";
            }catch (\Exception $e) {
                $result["status"]=0;
                $result["message"]=$e->getMessage();
            }
        }else{
            $result["status"]=0;
            $result["message"]="Method not get!";
        }
        return new JsonModel($result);
    }

    public function productAllAction(){
        $productManager = new ProductManager($this->entityManager);

        $products = $productManager->getAll();
        $productResult = array();
        foreach ($products as $k=>$productItem){
            $productResult[]=$this->setProductResult($productItem);
        }
        return new JsonModel($productResult);
    }

    public function productAction(){
        $productId = $this->params()->fromRoute('id', 0);

        try {
            if($productId==0)
                throw new \Exception("Product id not empty");

            $productManager = new ProductManager($this->entityManager);
            $product = $productManager->getById($productId);
            if(!$product)
                throw new \Exception("Product not found!");

            $productResult=$this->setProductResult($product);

        }catch (\Exception $e) {
            $productResult=null;
        }
        return new JsonModel($productResult);
    }

    public function orderAllAction(){
        $zaloAppId = $this->params()->fromRoute('id', 0);
//        $zaloAppId=827;
        $zaloApp = $this->apiManager->getById($zaloAppId);

        if($zaloApp->getGroceryId() && $zaloApp->getGroceryId()!=1){
            $groceryManager = new GroceryManager($this->entityManager);
            $grocery = $groceryManager->getById($zaloApp->getGroceryId());
            $sellOrder = $grocery->getSellOrder();
        }else{
            $sellManager = new SellManager($this->entityManager);
            $sellOrder = $sellManager->getOrderByZaloAppId($zaloAppId);
        }

        $orders = array();
        foreach ($sellOrder as $orderItem){
            $order["id"]=$orderItem->getId();
            $order["created_date"]=$orderItem->getCreatedDate()->format('d/m/Y');
            $order["s_status"]=$this->getStringOrderStatus($orderItem->getStatus());

            if($orderItem->getStatus()==3 || $orderItem->getStatus()==31 || $orderItem->getStatus()==0)
                $status=$orderItem->getStatus();
            else $status=1;

            $order["n_status"]=$status;

            $order["edit_allowed"]=($orderItem->getStatus()>0?'0':1);
            $order["value"]=$orderItem->getTotalPrice();
            $orders[]=$order;


        }
        return new JsonModel($orders);
    }

    public function orderAction()
    {
        $orderId = $this->params()->fromRoute('id', 0);
        try {

            if($orderId==0) throw new \Exception("Order ID empty!");

            $sellManager = new SellManager($this->entityManager);
            $orderItem = $sellManager->getSellOrderById($orderId);

            $order["status"]=$this->getStringOrderStatus($orderItem->getStatus());
            $order["edit_allowed"]=($orderItem->getStatus()>0?'0':1);
            $order["created_date"]=$orderItem->getCreatedDate()->format('d/m/Y');
            $order["summary"]=$orderItem->getSummary();
            $order["value"]=$orderItem->getTotalPrice();
            $order["pack_sale"]=$orderItem->getDiscount();

            $products=array();
            foreach ($orderItem->getSell() as $sellItem){
                $productItem = $sellItem->getProduct();
                $productResult=$this->setProductResult($productItem);
                $productResult["quantity"]=$sellItem->getQuantity();

                $qty = $sellItem->getQuantity();
                $boxUnit=$productItem->getBoxUnit();
                //mua theo thung
                if($qty%$boxUnit==0)
                    $productResult["options"]["unit_type"]="pack";
                else
                    $productResult["options"]["unit_type"]="unit";


                $products[]=$productResult;
            }

            $order["products"]=$products;

        }catch (\Exception $e) {
            $order=null;
        }

        return new JsonModel($order);
    }

    public function orderCreateAction(){
        $request = $this->getRequest();
        $result["status"]=0;
        if($request->isPost()) {
            try {
                $data = $request->getContent();
                $dataOrder = json_decode($data);
                $customerId=$dataOrder->id;
                $phone=Common::verifyMobile($dataOrder->phone);
                $summary=$dataOrder->summary;

                $zaloApp = $this->apiManager->getById($customerId);
                if(!$zaloApp)
                    throw new \Exception("Không tìm thấy khách hàng phù hợp!");

                //xac dinh grocery
                $groceryManager = new GroceryManager($this->entityManager);
                if($zaloApp->getGroceryId() && $zaloApp->getGroceryId()!=1){
                    $grocery = $groceryManager->getById($zaloApp->getGroceryId());
                }else if($zaloApp->getPhone()!=$phone){
                    //chua co grocery id nhung phone cung cap trong order != phone dang lau trong db
                    $zaloApp->setPhone($phone);
                    $grocery = $groceryManager->getByMobile($phone);
                    //tim thay grocery theo so dien thoai
                    if($grocery){
                        $zaloApp->setGroceryId($grocery->getId());
                        $grocery->setAddress($zaloApp->getFullAddress());
                    }else{
                        //lay default grocery
                        $grocery = $groceryManager->getById(1);
                    }
                }else{
                    //set default grocery
                    $grocery = $groceryManager->getById(1);
                }

                //xac dinh user phu trach
                $userIdTuyen=$grocery->getGroceryCat()->getUser()->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userIdTuyen);

                $sellManager = new SellManager($this->entityManager);
                $orderId=$dataOrder->order->id;
                //neu order da co trong he thong (sua order)
                //xoa sell hien tai cua order
                if($orderId){
                    $sellOrder = $sellManager->getSellOrderById($orderId);
                    if($sellOrder->getStatus()>0)
                        throw new \Exception("Đơn hàng đang thực hiện, nên không thể sửa!");

                    foreach ($sellOrder->getSell() as $sell){
                        $this->entityManager->remove($sell);
                        $sellOrder->removeSell($sell);
                    }
                    $action='Sửa đơn';
                }else{
                    $sellOrder=new SellOrder();
                    $action='Tạo đơn';
                }

                $totalPrice=0;
                $productManager = new ProductManager($this->entityManager);
                //them sell vao order
                foreach ($dataOrder->order->items as $k=>$item){
                    $productId=$item->id;
                    $product = $productManager->getById($productId);
                    $price = $product->getActivePrice();

                    $qty=$item->quantity;
                    $unitType=$item->unit_type;

                    //tien chiet khau san pham
                    $discount=$product->getUnitPriceSale();

                    //mua theo thung
                    if($unitType=="pack"){
                        $boxUnit=$product->getBoxUnit();
                        $qty=$boxUnit*$qty;
                        //tien chiet khau theo thung
                        $discount=$product->getPackPriceSale();
                    }
//                    $totalPrice = $totalPrice + $qty * $price->getPrice();

                    $sell = new Sell();
                    $sell->setPrice($price);
                    $sell->setProduct($product);
                    $sell->setQuantity($qty);
                    $sell->setPackUnit($product->getBoxUnit());
                    $sell->setCost($product->getAveragePrice());
                    $sell->setDiscount($discount);

                    $sell->setSellOrder($sellOrder);
                    $sellOrder->addSell($sell);
                }

//                $exchanged = $dataOrder->exchanged;
//                if($exchanged)
//                    $sellOrder->setDiscount($exchanged);

                $sellOrder->setUser($user);
                $sellOrder->setGrocery($grocery);
                $sellOrder->setCreatedDate(new \DateTime());
                $sellOrder->setStatus(-2);//trang thai khach tao
                $sellOrder->setMethod(-1);//khach hang tao don
                $sellOrder->setSummary($summary);
                $sellOrder->setZaloAppId($zaloApp->getId());
                $sellOrder->setSource("zaloapp");
                $this->entityManager->persist($sellOrder);

                //insert sell order activity
                $sellOrderActivity = new SellOrderActivity();
                $sellOrderActivity->setSellOrder($sellOrder);
                $sellOrderActivity->setActionBy($grocery->getGroceryName());
                $sellOrderActivity->setActionTime(new \DateTime());
                $sellOrderActivity->setAction($action);
                $sellOrderActivity->setActionIcon('fa-shopping-cart');
                $this->entityManager->persist($sellOrderActivity);

                $this->entityManager->flush();
                $result["status"]=1;
                $result["message"]="";
                $result["data"]=array("id"=>$sellOrder->getId());
            }catch (\Exception $e) {
                $result["status"]=0;
                $result["message"]=$e->getMessage();
            }
        }else{
            $result["status"]=0;
            $result["message"]="Method not get!";
        }
        return new JsonModel($result);
    }

    private function getStringOrderStatus($p_status){
        if($p_status<0) $status=-1;
        else $status=$p_status;

//        if($p_status==3)$status=3;
//        else $status=-1;

        return ConfigManager::getOrderStatus()[$status];
    }

    private function setProductResult(Product $productItem)
    {
        $product["id"]=$productItem->getId();
        $product["name"]=$productItem->getName();
        $product["price"]=$productItem->getActivePriceValue();
        $product["pack"]=$productItem->getBoxUnit();
        $product["unit"]=$productItem->getUnit()->getName();
        $product["weight"]=$productItem->getWeight();
        $product["code"]=$productItem->getCode();
        $product["label_name"]=$productItem->getLabelName();
        $product["group_id"]=$productItem->getGroupId();
        $product["description"]=$productItem->getNoteOrder();
        $product["exchange_unit"]=$productItem->getExchangeUnit();

        $isSale=0;
        if($productItem->getPackSaleType()){
            $product["pack_sale"]["type"]=$productItem->getPackSaleType();
            if($productItem->getPackSaleType()=='percent'){
                $product["pack_sale"]["value"]=$productItem->getPackSaleValue()/100;//giam gia theo %
            }else{
                $product["pack_sale"]["value"]=$productItem->getPackSaleValue();//so tien giam gia
            }
            $isSale=1;
        }else{
            $product["pack_sale"]=null;
        }

        if($productItem->getUnitSaleType()){
            $product["sale"]["type"]=$productItem->getUnitSaleType();
            if($productItem->getUnitSaleType()=='percent'){
                $product["sale"]["value"]=$productItem->getUnitSaleValue()/100;//giam gia theo %
            }else{
                $product["sale"]["value"]=$productItem->getUnitSaleValue();//so tien giam gia
            }
            $isSale=1;
        }else{
            $product["sale"]=null;
        }

        $img='';
        if($productItem->getImg())
            $img=$this->imageUrl.$productItem->getImg();
        $product["image"]=$img;
        $product["categoryId"]=$productItem->getProductCat()->getCode();

        $productCreatedDate = strtotime($productItem->getCreatedDate()->format("Y-m-d"));
        $today = strtotime(date("Y-m-d"));
        $dateDiff = abs($productCreatedDate - $today);

        if($productItem->getInventory()<$productItem->getNorm()){
            $product["ribbon"]=1;//"Sắp cháy hàng!";
        }
        elseif ($isSale==1) {
            $product["ribbon"] = 2;//"Trợ giá!";
        }
        elseif(floor($dateDiff / (60*60*24))<=30){
            $product["ribbon"]=3;//"Mới mở bán";
        }
        else $product["ribbon"]=4;//"Yêu thích";

        return $product;
    }

    public function bannersAction(){
        $result[]=array(
            "id"=>1
            ,"image"=>"https://cdn-merchant.vinid.net/images/gallery/vinshop-seo/1708491422_3_web_banner_1600x900_720.jpg"
            ,"screen"=>"honme"
            ,"display"=> "flashsale" //co nhieu gia tri popup, promote
            ,"flashsale_id"=>1
            ,"offer"=>null
        );
        $result[]=array(
            "id"=>2
            ,"image"=>"https://zalo-miniapp.github.io/zaui-coffee/dummy/banner-2.jpg"
            ,"screen"=>"honme"
            ,"display"=> "flashsale" //co nhieu gia tri popup, promote
            ,"flashsale_id"=>1
            ,"offer"=>null
        );
//        return new JsonModel($result);
        $result=array();
        return new JsonModel($result);
    }

    public function productRecommendAction(){
        //chua su dung zalo app id
//        $zaloAppId = $this->params()->fromRoute('id', 0);

        $productManager = new ProductManager($this->entityManager);

        $products = $productManager->getRecommend();

        $productResult = array();
        foreach ($products as $k=>$productItem){
            $productResult[]=$this->setProductResult($productItem->getProduct());
        }
        return new JsonModel($productResult);
    }

    public function indexAction()
    {
        echo Common::verifyMobile('84936408678');
        return new ViewModel();
    }

    public function zmpCheckoutAction(){
        $result["returnCode"]=1;
        $result["returnMessage"]="Thành công";
        return new JsonModel($result);
    }

    public function zmpRevokeAction(){
        $result["returnCode"]=1;
        $result["returnMessage"]="Thành công";
        return new JsonModel($result);
    }
    public function zmpNotifyAction(){
        $result["returnCode"]=1;
        $result["returnMessage"]="Thành công";
        return new JsonModel($result);
    }
}