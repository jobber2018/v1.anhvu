<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Sell\Controller;


use Admin\Service\AdminManager;
use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Exception;
use Grocery\Service\GroceryManager;
use Mustache_Engine;
use Product\Entity\ProductActivity;
use Product\Service\ProductManager;
use Sell\Entity\Sell;
use Sell\Entity\SellOrder;
use Sell\Entity\SellOrderActivity;
use Sell\Form\SellForm;
use Sell\Service\SellManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeFrontController;
use Sulde\Service\SuldeUserController;
use Users\Entity\User;
use Users\Service\UserManager;
use Zend\Paginator\Paginator;
use Zend\Validator\Date;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class UserController extends SuldeUserController
{

    private $entityManager;
    private $sellManager;

    public function __construct(EntityManager $entityManager, SellManager $sellManager)
    {
        $this->entityManager = $entityManager;
        $this->sellManager = $sellManager;
    }

    public function indexAction()
    {
        return new ViewModel();
    }

    public function addAction()
    {
        $groceryID = $this->params()->fromRoute('id', 0);
        $groceryManager = new GroceryManager($this->entityManager);
        $grocery = $groceryManager->getById($groceryID);

        //khong tao don hang khi dang kiem kho.
        if(ConfigManager::isStockChecking())
            return $this->redirect()->toRoute('sell-user',['action'=>'stock-checking','id'=>$groceryID]);

        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $data = $request->getPost("pro");

//                $this->userInfo;
                $userId= $this->userInfo->getId();
//                $userId=$grocery->getGroceryCat()->getUser()->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                $sellOrder = new SellOrder();
                $totalPrice=0;

                $productManager = new ProductManager($this->entityManager);

                foreach ($data as $key=>$item) {
                    $obj = json_decode(json_encode($item));
                    $odd = $obj->odd;
                    $price = $obj->price;
//                    $quantity = $odd;

                    $productId = $obj->id;

                    $product = $productManager->getById($productId);

                    $quantity=$product->validateQty($odd);

                    $totalPrice = $totalPrice + $quantity * $price;

                    $inventory = $product->getInventory();
                    $product->setInventory($inventory - $quantity);

                    $price = $product->getActivePrice();

                    $sell = new Sell();
                    $sell->setPrice($price);
                    $sell->setProduct($product);
                    $sell->setQuantity($quantity);

                    $sell->setSellOrder($sellOrder);
                    $sellOrder->addSell($sell);

                    //tao moi ban ghi product activity
                    $productActivity = new ProductActivity();
                    $productActivity->setUser($user);
                    $productActivity->setProduct($product);
                    $productActivity->setNote('Thêm vào đơn: '.$grocery->getGroceryName());
                    $productActivity->setCreatedDate(new \DateTime());
                    //$productActivity->setUrl("/admin/werehouse/view/".$werehouseOrder->getId().".html");
                    $change = 0-$quantity;
                    $productActivity->setChange($change);
                    $this->entityManager->persist($productActivity);
                }

                $sellOrder->setUser($user);
                $sellOrder->setTotalPrice($totalPrice);
                $sellOrder->setGrocery($grocery);
                $sellOrder->setCreatedDate(new \DateTime());
                $sellOrder->setStatus(1);
                $sellOrder->setMethod(0);//truc tiep
                $this->entityManager->persist($sellOrder);
                $this->entityManager->flush();

                $adminManager = new AdminManager($this->entityManager);
                $msg = '<i class="fa fa-shopping-cart"></i>Tạo đơn hàng' . $sellOrder->getGrocery()->getGroceryName();
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);

                $this->flashMessenger()->addSuccessMessage('Đơn hàng đã được tạo.');
                $result['status']=1;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);

        }else{
            $productManager = new ProductManager($this->entityManager);
            $productList = $productManager->getAll();
        }
        return new ViewModel(['grocery'=>$grocery,'productList'=>$productList]);
    }

    public function orderDetailAction(){
        $sellOrderID = $this->params()->fromRoute('id', 0);

        $sellOrder = $this->sellManager->getSellOrderById($sellOrderID);

        return new ViewModel(['sellOrder'=>$sellOrder,'grocery'=>$sellOrder->getGrocery()]);
    }

    /**
     * sau khi sua xong admin edit order thi xoa ham nay ben user
     * @return void|JsonModel
     */
    public function addProToOrderAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            try {
                $proId = $request->getPost("pid");
                $orderId = $request->getPost("oid");
                $qty=$request->getPost("qty");
                $option=$request->getPost("opt");

                if(!$proId || !$orderId || !$qty)
                    throw new Exception('Error! Có thể không tim thấy sản phẩm hoặc đơn hàng hoặc chưa nhập số lượng cần bán!');

                $sellOrder = $this->sellManager->getSellOrderById($orderId);

                $productManager = new ProductManager($this->entityManager);
                $product = $productManager->getById($proId);

                //ban le
                if($option=='unit'){
                    //tien chiet khau san pham
                    $discount=$product->getUnitPriceSale();
                }else{
                    //tien chiet khau theo thung
                    $discount=$product->getPackPriceSale();
                    //quy doi so luong thung thanh so luong san pham
                    $qty=$qty*$product->getBoxUnit();
                }

                $inventory = $product->getInventory();
                //check product da co trong don hang chua? neu chua tra ve sell moi, neu co roi thi tra ve sell dang co
                $sell=$sellOrder->buildSell($product);

                $qtyBefore = 0;
                //san pham da co trong don
                if($sell->getProduct()){
                    $qtyBefore = $sell->getQuantity();
                    //cong them so luong da nhap truoc
                    $inventory +=$qtyBefore;
                }

                //ton kho khong du de ban, tu dong quy sang ban le
                if($qty>$inventory){
                    //lay discount theo ban le
                    $discount=$product->getUnitPriceSale();
                    $qty=$inventory;
                }

                //$quantity=$product->validateQty($qty);

                //giam so luong sp trong kho
                $product->setInventory($inventory - $qty);

                $sell->setPrice($product->getActivePrice());
                $sell->setProduct($product);
                $sell->setQuantity($qty);
                $sell->setDiscount($discount);
                $sell->setCost($product->getAveragePrice());
                $sell->setPackUnit($product->getBoxUnit());

                $sell->setSellOrder($sellOrder);
                $sellOrder->addSell($sell);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                //tao moi ban ghi product activity
                $productActivity = new ProductActivity();
                $productActivity->setUser($user);
                $productActivity->setProduct($product);
                $productActivity->setCreatedDate(new \DateTime());

                $change = $qtyBefore-$qty;
                $productActivity->setChange($change);

                //insert sell order activity
                $sellOrderActivity = new SellOrderActivity();
                $sellOrderActivity->setSellOrder($sell->getSellOrder());
                $sellOrderActivity->setActionBy($user->getUsername());
                $sellOrderActivity->setActionTime(new \DateTime());

                //neu change =0 thi khong them ban ghi
                if($change>0){
                    $productActivity->setNote($sellOrder->getGrocery()->getGroceryName().': trả lại');
                    $this->entityManager->persist($productActivity);

                    $action='Bỏ '.$change.' '.$product->getName();
                    $sellOrderActivity->setAction($action);
                    $sellOrderActivity->setActionIcon('fa-minus-square');
                    $this->entityManager->persist($sellOrderActivity);

                }elseif($change<0){
                    $productActivity->setNote('Thêm vào đơn: '.$sellOrder->getGrocery()->getGroceryName());
                    $sellOrderActivity->setActionIcon('fa-plus-square');
                    $this->entityManager->persist($productActivity);

                    $action='Thêm '.(-1*$change).' '.$product->getName();
                    $sellOrderActivity->setAction($action);
                    $this->entityManager->persist($sellOrderActivity);
                }

                $this->entityManager->persist($sellOrder);
                $this->entityManager->flush();

                $result['status']=1;
                $result['msg'] = 'Đã thêm '. $product->getName() . ' vào đơn hàng!';

            } catch (\Exception $e) {
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }

            return new JsonModel($result);
        }
    }

    public function editAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $data = $request->getPost("pro");
                $sellOrderID=$request->getPost("id");
                $sellOrder = $this->sellManager->getSellOrderById($sellOrderID);
                $totalPriceOld = $sellOrder->getTotalPrice();

                $totalPrice=0;

                foreach ($data as $key=>$item) {
                    $obj = json_decode(json_encode($item));
                    $odd = $obj->odd;
                    $price = $obj->price;
//                    $quantity = $odd;


                    $productId = $obj->id;
                    $productManager = new ProductManager($this->entityManager);
                    $product = $productManager->getById($productId);

                    $quantity=$product->validateQty($odd);
                    $totalPrice = $totalPrice + $quantity * $price;

                    $inventory = $product->getInventory();
                    $product->setInventory($inventory - $quantity);

                    $price = $product->getActivePrice();

                    $sell = new Sell();
                    $sell->setPrice($price);
                    $sell->setProduct($product);
                    $sell->setQuantity($quantity);

                    $sell->setSellOrder($sellOrder);
                    $sellOrder->addSell($sell);
                }

                $sellOrder->setTotalPrice($totalPrice+$totalPriceOld);
                $this->entityManager->persist($sellOrder);
                $this->entityManager->flush();

                $this->flashMessenger()->addSuccessMessage('Đã cập nhật đơn hàng.');
                $result['status']=1;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);
        }else{
            $sellOrderID = $this->params()->fromRoute('id', 0);
            $sellOrder = $this->sellManager->getSellOrderById($sellOrderID);

            $productManager = new ProductManager($this->entityManager);
            $productList = $productManager->getAll();
        }
        return new ViewModel([
            'sellOrder'=>$sellOrder,
            'grocery'=>$sellOrder->getGrocery(),
            'productList'=>$productList
        ]);
    }

    /**
     * @todo: xoa ham nay sau khi khong dung edit_order.phtml cu
     * được thay băng hàm delProductOrderAction bên admin
     * @return JsonModel
     */
    public function xoaDeleteProInOrderAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $sellId = $request->getPost("id");
                $sell = $this->sellManager->getById($sellId);
                $product = $sell->getProduct();
                $inventory = $product->getInventory();
                $quantity = $sell->getQuantity();
                //tra lai san pham vao kho
                $product->setInventory($inventory+$quantity);

                $this->entityManager->remove($sell);

                $userId= $this->userInfo->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                //tao moi ban ghi product activity
                $productActivity = new ProductActivity();
                $productActivity->setUser($user);
                $productActivity->setProduct($product);
                $productActivity->setNote('Bỏ ra khỏi đơn: '.$sell->getSellOrder()->getGrocery()->getGroceryName());
                $productActivity->setCreatedDate(new \DateTime());
                $productActivity->setChange($quantity);
                $this->entityManager->persist($productActivity);

                //insert sell order activity
                $sellOrderActivity = new SellOrderActivity();
                $sellOrderActivity->setSellOrder($sell->getSellOrder());
                $sellOrderActivity->setActionBy($user->getUsername());
                $action='Xoá '.$quantity.' '.$product->getName();
                $sellOrderActivity->setActionIcon('fa-minus-square');
                $sellOrderActivity->setAction($action);
                $sellOrderActivity->setActionTime(new \DateTime());
                $this->entityManager->persist($sellOrderActivity);

//                $this->entityManager->persist($sellOrder);
                $this->entityManager->flush();
                $result['proId']=$product->getId();
                $result['status']=1;
                $result['msg']='Đã xoá sản phẩm '.$product->getName();
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

    //huy don hang khi dang tru ton kho
    public function cancelOrderAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $sellOrderId = $request->getPost("id");
                if ($sellOrderId > 0) {
                    //lay thong tin don hang
                    $sellOrder = $this->sellManager->getSellOrderById($sellOrderId);

                    if($sellOrder->getStatus()!=1)
                        throw new Exception('Bạn không thể huỷ đơn hàng này. Vui lòng liên hệ Admin để được hỗ trợ!');

                    //update inventory cho san pham
                    foreach ($sellOrder->getSell() as $sellItem) {
                        $qtyReturn = $sellItem->getQuantity();
                        $product = $sellItem->getProduct();
                        $inventory = $product->getInventory();
                        $product->setInventory($inventory + $qtyReturn);

                        //tao moi ban ghi product activity
                        $productActivity = new ProductActivity();
                        $productActivity->setUser($sellOrder->getUser());
                        $productActivity->setProduct($product);
                        $productActivity->setNote('Huỷ đơn: '.$sellOrder->getGrocery()->getGroceryName());
                        $productActivity->setCreatedDate(new \DateTime());
                        //$productActivity->setUrl("/admin/werehouse/view/".$werehouseOrder->getId().".html");
                        $change = $qtyReturn;
                        $productActivity->setChange($change);
                        $this->entityManager->persist($productActivity);
                    }
                    //set status = 0: huy don hang
                    $sellOrder->setCanceledDate(new \DateTime());
                    $sellOrder->setCanceledBy($this->userInfo->getFullname());
                    $sellOrder->setStatus(0);
                    $this->entityManager->flush();
                    $result['status'] = 1;
                    $result['msg'] = 'Đã xác nhận huỷ đơn hàng: ' . $sellOrder->getGrocery()->getGroceryName();
                } else {
                    throw new Exception('Không tìm thấy đơn hàng muốn huỷ!');
                }
            } catch (\Exception $e) {
                $result['status'] = 0;
                $result['msg'] = $e->getMessage();
            }
        }
        return new JsonModel($result);
    }

    public function addDraftOrderAction()
    {
        $groceryID = $this->params()->fromRoute('id', 0);
        $groceryManager = new GroceryManager($this->entityManager);
        $grocery = $groceryManager->getById($groceryID);

        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $data = $request->getPost("pro");

                //lay user tuyen
                $userIdTuyen=$grocery->getGroceryCat()->getUser()->getId();
                $user = $this->entityManager->getRepository(User::class)->find($userIdTuyen);

                $sellOrder = new SellOrder();
//                $totalPrice=0;
//                $totalDiscount=0;

                $productManager = new ProductManager($this->entityManager);

                foreach ($data as $key=>$item) {
                    $obj = json_decode(json_encode($item));
                    $qty = $obj->qty;
                    $productId = $obj->id;
                    $option = $obj->option;

                    $product = $productManager->getById($productId);
                    //ban theo thung
                    if($option=='unit'){
                        //tien chiet khau san pham
                        $discount=$product->getUnitPriceSale();
//                        $price = $product->getActivePriceValue()*$qty;
                    }else{
                        //tien chiet khau theo thung
                        $discount=$product->getPackPriceSale();
//                        $price = $product->getPackPrice()*$qty;
                        //quy doi so luong thung thanh so luong san pham
                        $qty=$qty*$product->getBoxUnit();
                    }

//                    $totalPrice +=$price;
//                    $totalDiscount+=$discount;

                    $sell = new Sell();
                    $sell->setPrice($product->getActivePrice());
                    $sell->setProduct($product);
                    $sell->setQuantity($qty);
                    $sell->setDiscount($discount);
                    $sell->setCost($product->getAveragePrice());
                    $sell->setPackUnit($product->getBoxUnit());

                    $sell->setSellOrder($sellOrder);
                    $sellOrder->addSell($sell);
                }

                $sellOrder->setUser($user);
                //$sellOrder->setTotalPrice($totalPrice);
                $sellOrder->setGrocery($grocery);
                $sellOrder->setCreatedDate(new \DateTime());
                $sellOrder->setStatus(-1);

                //neu user dang nhap khac user tuyen =>goi dien tao don
                if($userIdTuyen!=$this->userInfo->getId())
                    $sellOrder->setMethod($this->userInfo->getId());//goi dien
                else
                    $sellOrder->setMethod(0);//truc tiep

                //insert sell order activity
                $sellOrderActivity = new SellOrderActivity();
                $sellOrderActivity->setSellOrder($sellOrder);
                $sellOrderActivity->setActionBy($this->userInfo->getUsername());
                $sellOrderActivity->setActionTime(new \DateTime());
                $action='Tạo đơn';
                $sellOrderActivity->setActionIcon('fa-shopping-cart');
                $sellOrderActivity->setAction($action);
                $this->entityManager->persist($sellOrderActivity);

                $this->entityManager->persist($sellOrder);
                $this->entityManager->flush();

                $adminManager = new AdminManager($this->entityManager);
                $msg = '<i class="fa fa-shopping-cart"></i>Tạo đơn hàng' . $sellOrder->getGrocery()->getGroceryName();
                $data["title"]= $msg;
                $data["msg"]=$msg;
                $data["uid"]=$this->userInfo->getId();
                $adminManager->addActivity($data);

                $this->flashMessenger()->addSuccessMessage('Đơn hàng đã được đưa vào danh sách chờ xử lý!');
                $result['status']=1;
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);

        }else{
            $productManager = new ProductManager($this->entityManager);
            $productList = $productManager->getAll();
        }
        return new ViewModel(['grocery'=>$grocery,'productList'=>$productList]);
    }

    public function orderDetailDraftAction(){
        $request = $this->getRequest();

        if($request->isPost()){
            try{
                $proId = $request->getPost("pid");
                $orderId = $request->getPost("oid");
                $qty=$request->getPost("qty");
                $option=$request->getPost("opt");

                if(!$proId || !$orderId || !$qty)
                    throw new Exception('Error! Có thể không tim thấy sản phẩm hoặc đơn hàng hoặc chưa nhập số lượng cần bán!');

                $sellOrder = $this->sellManager->getSellOrderById($orderId);

                //chi edit khi order o trang thai chua duyet;
                if($sellOrder->getStatus()>0){
                    throw new Exception("Không thể sửa đơn hàng khi đã xác nhận!");
                }

                $productManager = new ProductManager($this->entityManager);
                $product = $productManager->getById($proId);

                //ban le
                if($option=='unit'){
                    //tien chiet khau san pham
                    $discount=$product->getUnitPriceSale();
                }else{
                    //tien chiet khau theo thung
                    $discount=$product->getPackPriceSale();
                    //quy doi so luong thung thanh so luong san pham
                    $qty=$qty*$product->getBoxUnit();
                }

                //check product da co trong don hang chua? neu chua tra ve sell moi, neu co roi thi tra ve sell dang co
                $sell=$sellOrder->buildSell($product);


                $sell->setPrice($product->getActivePrice());
                $sell->setProduct($product);
                $sell->setQuantity($qty);
                $sell->setDiscount($discount);
                $sell->setCost($product->getAveragePrice());
                $sell->setPackUnit($product->getBoxUnit());

                $sell->setSellOrder($sellOrder);
                $sellOrder->addSell($sell);

                $this->entityManager->persist($sellOrder);
                $this->entityManager->flush();

                $result['status']=1;
                $result['msg'] = 'Đã thêm '. $qty .' sản phẩm '. $product->getName() . ' vào đơn hàng!';
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);
        }
        else{
            $sellOrderID = $this->params()->fromRoute('id', 0);
            $sellOrder = $this->sellManager->getSellOrderById($sellOrderID);
            $productManager = new ProductManager($this->entityManager);
            $productList = $productManager->getAll();

            return new ViewModel([
                'sellOrder'=>$sellOrder,
                'grocery'=>$sellOrder->getGrocery(),
                'productList'=>$productList
            ]);
        }
    }

    public function deleteProductOrderDraftAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $sellId = $request->getPost("id");
                $sell = $this->sellManager->getById($sellId);
                $this->entityManager->remove($sell);

                $this->entityManager->flush();
                $result['status']=1;
                $result['msg']='Đã xoá sản phẩm '.$sell->getProduct()->getName();
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

    public function submitOrderDraftAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            $msg = array();
            $doCreateOrder = 1;
            try{
                $sellOrderId = $request->getPost("id");

                $sellOrder = $this->sellManager->getSellOrderById($sellOrderId);

                if($sellOrder->getStatus()>0)
                    throw new Exception('Error! Đơn hàng đang ở trạng thái '. ConfigManager::getOrderStatus()[$sellOrder->getStatus()] .'. Bạn không thể tạo đơn hàng ở trạng thái này!');

                if(count($sellOrder->getSell())==0)
                    throw new Exception('Error! Vui lòng thêm sản phẩm vào đơn hàng!');

                foreach ($sellOrder->getSell() as $sellItem){

                    //so luong dat hang
                    $qty = $sellItem->getQuantity();

                    //san pham dat hang
                    $product = $sellItem->getProduct();

                    $inventory = $product->getInventory();

                    //kiem tra ton kho voi so luong dat hang
                    if($qty > $inventory || $inventory==0){
                        $msg[$sellItem->getId()]=$inventory;
                        $doCreateOrder=-1;
                    }else{
                        $qty = $product->validateQty($qty);
                        $product->setInventory($inventory - $qty);
                        $sellItem->setQuantity($qty);
                        $sellItem->setApprovedQty($qty);
                    }
                }

                if($doCreateOrder==1){
                    $userId= $this->userInfo->getId();
                    $userLogin = $this->entityManager->getRepository(User::class)->find($userId);
                    //tao moi ban ghi product activity
                    foreach ($sellOrder->getSell() as $sellItem){
                        $qty = $sellItem->getQuantity();
                        $productActivity = new ProductActivity();
                        $productActivity->setUser($userLogin);
                        $productActivity->setProduct($sellItem->getProduct());
                        $productActivity->setNote('Duyệt đơn: '.$sellOrder->getGrocery()->getGroceryName());
                        $productActivity->setCreatedDate(new \DateTime());
                        //$productActivity->setUrl("/admin/werehouse/view/".$werehouseOrder->getId().".html");
                        $change = 0-$qty;
                        $productActivity->setChange($change);
                        $this->entityManager->persist($productActivity);
                    }

                    $sellOrder->setStatus($doCreateOrder);
                    $sellOrder->setConfirmedDate(new DateTime());

                    //insert sell order activity
                    $sellOrderActivity = new SellOrderActivity();
                    $sellOrderActivity->setSellOrder($sellOrder);
                    $sellOrderActivity->setActionBy($this->userInfo->getUsername());
                    $sellOrderActivity->setActionTime(new \DateTime());
                    $action='Xác nhận và chuyển Chờ đóng gói';
                    $sellOrderActivity->setActionIcon('fa-gavel');
                    $sellOrderActivity->setAction($action);
                    $this->entityManager->persist($sellOrderActivity);

                    $this->entityManager->flush();
                    $result['status']=1;
                    $result['msg']='Đơn hàng đã được đưa vào danh sách chờ đóng gói!';
                }
                else{
                    $result['status']=0;
                    $result['msg']='Không thể gửi đơn hàng! Vui lòng kiểm tra thông báo trên mỗi sản phẩm.';
                    $result['data']=$msg;
                }
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
        }else{
            $result['status']=0;
            $result['msg']="Không thể thực hiện yêu cầu!";
        }
        return new JsonModel($result);
    }

    public function deleteOrderDraftAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $sellOrderId = $request->getPost("id");
                $sellOrder = $this->sellManager->getSellOrderById($sellOrderId);
                if($sellOrder->getStatus()>0)
                    throw new Exception('Error! Không thể xoá đơn hàng đang trong quy trình giao hàng!');

                //chi nguoi tao hoac admin moi duoc xoa don
                if($this->userInfo->getRole()!='admin' && $sellOrder->getUser()->getId()!=$this->userInfo->getId()){
                    throw new Exception('Chỉ Admin hoặc người tạo đơn mới có thể xoá đơn!');
                }

                $this->entityManager->remove($sellOrder);

                $this->entityManager->flush();
                $result['status']=1;
                $result['msg']='Đã xoá đơn nháp!';
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

    public function noteAction(){
        $request = $this->getRequest();

        if($request->isPost()){
            try{
                $sellOrderID = $request->getPost("oid");
                $note = $request->getPost("note");

                if($sellOrderID && $note){
                    $sellOrder = $this->sellManager->getSellOrderById($sellOrderID);

                    $uName=$this->userInfo->getFullname();
                    if($sellOrder->getNote())
                        $note = $sellOrder->getNote()."\n" .$uName ."|".date("Y-m-d h:i:s")."|".$note;
                    else
                        $note = $uName."|".date("Y-m-d h:i:s")."|".$note;

                    $sellOrder->setNote($note);
                    $this->entityManager->flush();
                }

                $this->flashMessenger()->addSuccessMessage('Đã thêm ghi chú cho đơn hàng');

                if($sellOrder->getStatus()==-1)
                    return $this->redirect()->toRoute('sell-user',['action'=>'order-detail-draft','id'=>$sellOrderID]);
            }catch (\Exception $e){
                $this->flashMessenger()->addSuccessMessage($e->getMessage());
            }
            return $this->redirect()->toRoute('sell-user',['action'=>'order-detail','id'=>$sellOrderID]);
        }
    }

    public function countOrderCustomerNewAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try{
                $sellOrderCustomer = $this->sellManager->getSellOrder(-2);
                $sellOrderNew = $this->sellManager->getSellOrder(1);
                $sellOrderDraft = $this->sellManager->getSellOrder(-1);
                $result['status']=1;
                $result['cus_order_number']=count($sellOrderCustomer);
                $result['new_order_number']=count($sellOrderNew);
                $result['draft_order_number']=count($sellOrderDraft);
            }catch (\Exception $e){
                $result['status']=0;
                $result['msg']=$e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    public function stockCheckingAction(){
        $request = $this->getRequest();
        $groceryID = $this->params()->fromRoute('id', 0);
        return new ViewModel(['groceryID'=>$groceryID]);

    }
}