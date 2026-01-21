<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/17/19 6:30 PM
 *
 */

namespace Users\Controller;
use DateTime;
use EmailTemplate\Entity\EmailTemplate;
use EmailTemplate\Service\EmailTemplateManager;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Google_Client;
use Google_Service_Plus;
use Mustache_Engine;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Users\Service\AuthManager;
use Users\Service\UserManager;
use Zend\Authentication\AuthenticationService;
use Zend\Crypt\Password\Bcrypt;
use Zend\Mail\Message;
use Zend\Math\Rand;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Users\Form\LoginForm;
use Zend\Authentication\Result;

use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Mime;
use Zend\Mime\Message as MimeMessage;

class AuthController extends AbstractActionController{

    private $entityManager, $userManager, $authManager, $authService, $mailOptions;

    /**
     * AuthController constructor.
     * @param $entityManager
     * @param UserManager $userManager
     * @param AuthManager $authManager
     * @param AuthenticationService $authService
     */
    public function __construct($entityManager, UserManager $userManager,AuthManager $authManager,AuthenticationService $authService, $mailOption){
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->authManager = $authManager;
        $this->authService = $authService;
        $this->mailOptions =$mailOption;
    }


    /**
     * @return \Zend\Http\Response|ViewModel
     * @throws \Exception
     */

    public function loginAction(){
        $form = new LoginForm;

//        if($this->authService->hasIdentity()){
//            return $this->redirect()->toRoute('admin-dashboard');
//        }

        if($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $form->setData($data);
            if($form->isValid()){
                $data = $form->getData();
                $result = $this->authManager->login($data['mobile'], $data['password'], $data['remember']);
                if($result->getCode() == Result::SUCCESS){
                    $loginInfo = $_SERVER ['HTTP_USER_AGENT'];
                    $identity = $result->getIdentity();
                    $this->userManager->updateLogin($identity,$loginInfo);
                    if($identity->getRole()=='admin')
                        return $this->redirect()->toRoute('admin-dashboard');
                    elseif ($identity->getRole()=='staff')
                        return $this->redirect()->toRoute('staff-dashboard');

//                    return $this->redirect()->toRoute('user-admin',array('controller' => 'admin','action' =>  'profile'));
                }
                else{
                   $message = current($result->getMessages());
                   $this->flashMessenger()->addErrorMessage($message);
                   return $this->redirect()->toRoute('login');
                }
            }

        }
        $this->layout()->setTemplate('layoutLogin');
        return new ViewModel(['form'=>$form]);
    }

    public function logoutAction(){
        $this->authManager->logout();
        return $this->redirect()->toRoute('login');
    }

    public function userLoginAction(){
        $form = new LoginForm;

        if($this->authService->hasIdentity()){
            $role = $this->authService->getIdentity()->getRole();
            if($role=='user' || $role=='customer')
                return $this->redirect()->toRoute('user-dashboard');
//            else
//                return $this->redirect()->toRoute('user-login');
        }

        if($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $form->setData($data);
            if($form->isValid()){
                $data = $form->getData();
                $result = $this->authManager->login($data['mobile'], $data['password'], $data['remember']);

                if($result->getCode() == Result::SUCCESS){
                    $loginInfo = $_SERVER ['HTTP_USER_AGENT'];
                    $identity = $result->getIdentity();
                    $this->userManager->updateLogin($identity,$loginInfo);
                    return $this->redirect()->toRoute('user-dashboard');
                }
                else{
                    $message = current($result->getMessages());
                    $this->flashMessenger()->addErrorMessage($message);
                    return $this->redirect()->toRoute('user-login');
                }
            }

        }
        $this->layout()->setTemplate('userLayoutLogin');
        return new ViewModel(['form'=>$form]);
    }

    public function userLogoutAction(){
        $this->authManager->logout();
        return $this->redirect()->toRoute('user-login');
    }

    public function userSignupAction(){
        include("module/phpmailer/class.smtp.php");
	  	include "module/phpmailer/class.phpmailer.php"; 
	  	include("module/phpmailer/config.php");
      	$request = $this->getRequest();
        $result=array();
        $result['status']=1;

        if($request->isPost()) {
            try{
                $fullname = $request->getPost("fullname");
                $mobile = $request->getPost("mobile");
                $email = $request->getPost("email");
                $pass = $request->getPost("pass");
                $repeatpass = $request->getPost("repeatpass");

                if($pass!=$repeatpass) throw new \Exception('Vui lòng xác nhận lại mật khẩu');
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \Exception('Email không đúng');

                $data['fullname'] = $fullname;
                $data['mobile'] = $mobile;
                $data['password'] = $pass;
                $data['email'] = $email;
                $data['status']=1;

                $user = $this->userManager->addUser($data);

                //send mail for signup
                //build url
                $http = isset($_SERVER['HTTPS']) ? "https://" : "http://";
                $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost";
                $url=$http.$host."/user-login.html";

                //$emailTemplateManager = new EmailTemplateManager($this->entityManager);

                if(Common::verifyEmail($email)){
                    //$emailTemplateManager->sendMail($email,null,'register-user-homestay',array('name'=>$fullname,'url'=>$url));
                  	
                  	$context.="<br />Xin chào: ".$fullname;
                    $context.="<br />Chào mừng bạn đến với BESTAY.ORG</a><br>
    								 Hãy click vào liên kết bên dưới để đăng nhập hệ thống.<br>
    								<i>Bằng cách click vào đường dẫn đăng nhập, bạn đồng ý chấp nhận Điều khoản sử dụng & Chính sách quyền riêng tư của chúng tôi.</i>";
                    $context.='<br /><center><a href="https://bestay.org/user-login.html" style="display:block;max-width:200px;min-height:50px;padding:0 10px;border-radius:4px;border:0;font-weight:bold;color:#fff;font-size:18px;line-height:50px;background-color:#f45710;margin:30px auto 42px; text-decoration:none" target="_blank">Đăng nhập</a></center>';
                    $context.="<br /><b>Nếu liên kết bên trên không hoạt động vui lòng copy/paste link sau lên trình duyệt: https://bestay.org/user-login.html</b>";
                  	$context.="<br />Thành công của các Chủ Nhà cũng chính là thành công của BESTAY.";
                    $mailTo = $email;
                  	$nameTo = $fullname;
                    $subject = 'BESTAY - Xác nhận đăng ký thành viên';
                    sendMailer($subject, $context, $nameTo, $mailTo, $diachicc='', $emailFrom, $nameFrom);
                }
                //auto login
                $resultAuth = $this->authManager->login($data['mobile'], $data['password'], 1);
                if($resultAuth->getCode() == Result::SUCCESS){
                    $loginInfo = $_SERVER ['HTTP_USER_AGENT'];
                    $this->userManager->updateLogin($user,$loginInfo);
//                    $result['redirect']=;
                }
            }catch (\Exception $e){
                $result['status']=0;
                $result['message']=$e->getMessage();
            }
        }
        return new JsonModel($result);
    }

    private function doLogin($user,$mobile,$password){
        $result = $this->authManager->login($mobile, $password, 1);
        if($result->getCode() == Result::SUCCESS){
            $loginInfo = $_SERVER ['HTTP_USER_AGENT'];
            $this->userManager->updateLogin($user,$loginInfo);
            return $this->redirect()->toRoute('user-dashboard');
        }
        else{
            return $this->redirect()->toRoute('user-login');
        }
    }

    public function loginWithFacebookAction()
    {
        try {
            $view = new ViewModel();
            $view->setTemplate('users/auth/login-with-social');

            $fb = new \Facebook\Facebook(ConfigManager::facebookApi());

            $helper = $fb->getRedirectLoginHelper();

            try {
                $accessToken = $helper->getAccessToken();
            } catch(FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            if (! isset($accessToken)) {
                $permissions = array('public_profile','email'); // Optional permissions
                $loginUrl = $helper->getLoginUrl(ConfigManager::facebookCallBackURL(), $permissions);
                header("Location: ".$loginUrl);
                exit;
            }

            try {
                $fields = array('id', 'name', 'email');
                $response = $fb->get('/me?fields='.implode(',', $fields).'', $accessToken);
            } catch(FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            $this->layout()->setTemplate('userLayoutLogin');
            $user = $response->getGraphUser();
            $userInfo = $this->userManager->getBySocialId($user['id']);
            if($userInfo) $this->doLogin($userInfo,$userInfo->getMobile(),Define::DEFAULT_USER_PASS);

            $view->setVariable('user',$user);
            $this->layout()->setTemplate('userLayoutLogin');
            return $view;

        } catch (FacebookSDKException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public function loginWithGoogleAction()
    {
        $googleApi = ConfigManager::googleApi();
        $google_client_id 		= $googleApi['client_id'];
        $google_client_secret 	= $googleApi['client_secret'];
        $google_redirect_url 	= $googleApi['redirect_url']; //path to your script
        $google_developer_key 	= $googleApi['developer_key'];

        $client = new Google_Client();


        $client->setApplicationName('Login to vnhomestay.com.vn');
        $client->setClientId($google_client_id);
        $client->setClientSecret($google_client_secret);
        $client->setRedirectUri($google_redirect_url);
        $client->setDeveloperKey($google_developer_key);

        // Khai báo xin các quyền truy cập: lấy email, tên, ID người dùng ...
        // Tham khảo các quyền khác Scope: https://developers.google.com/identity/protocols/googlescopes
        $client->addScope([
            'https://www.googleapis.com/auth/plus.login',
            'https://www.googleapis.com/auth/userinfo.email'
        ]);

        //Đây là URL đến Google, bạn cần mở nếu chưa đăng nhập
//        $auth_url = $client->createAuthUrl();

        if (isset($_SESSION['access_token']) && $_SESSION['access_token'])
        {
            /*
             * Đã đăng nhập trước rồi do tồn tại access_token trong Session
             * Nên bạn không cần xác thực từ Google nữa mà chỉ việc lấy thông tin
             */
            $user = $this->getGoogleInfo($client);

        }
        else
        {
            /**
             * Nếu tồn tại $_GET['code'] trên URL có nghĩa là Google vừa gửi Code truy cập tới cho bạn, bạn cần lấy thông
             * tin này để truy cập.
             */
            if (isset($_GET['code'])) {
                $client->fetchAccessTokenWithAuthCode($_GET['code']);
                //Lấy mã Token và lưu lại tại SESSION
                $_SESSION['access_token'] = $client->getAccessToken();
                $user = $this->getGoogleInfo($client);
            }
            else
            {
                //Chuyển hướng sang Google để lấy xác thực
                $auth_url = $client->createAuthUrl();
                header("Location: $auth_url");
                die();
            }
        }

        $userInfo = $this->userManager->getBySocialId($user['id']);
        if($userInfo) $this->doLogin($userInfo,$userInfo->getMobile(),Define::DEFAULT_USER_PASS);

        $view = new ViewModel();
        $view->setVariable('user', $user);
        $view->setTemplate('users/auth/login-with-social');
        return $view;
    }

    function getGoogleInfo($client) {

        $client->setAccessToken($_SESSION['access_token']);
        $plus = new Google_Service_Plus($client);

        if ($client->isAccessTokenExpired()) {
            //Truy cập bị hết hạn, cần xác thực lại
            //Chuyển hướng sang Google để lấy xác thực
            $auth_url = $client->createAuthUrl();
            header("Location: $auth_url");
            die();
        }

        //Lấy các thông tin của User
        $data = array();
        $me = $plus->people->get('me');
        $data['id']    = @$me['id'];                    //ID
        $data['email'] = @$me['emails'][0]['value'];    //Địa chỉ email
        $data['name']  = @$me['displayName'];           //Tên
        $data['image'] = @$me['image']['url'];          //Url của ảnh avatar
        return $data;
    }

    public function socialRegisterAction(){
        $request = $this->getRequest();

        $view = new ViewModel();
        $view->setTemplate('users/auth/login-with-social');
        $data=array();
        if($request->isPost()) {
            try{
                $socialId = $request->getPost("social_id");
                $name = $request->getPost("name");
                $email = $request->getPost("email");
                $mobile = $request->getPost("mobile");
                $avatar = $request->getPost("image");

                $data["email"] = $email;
                $data["social_id"] = $socialId;
                $data["fullname"] = $name;
                $data["mobile"] = $mobile;
                $data["avatar"] = $avatar;
                $data['status']=1;
                $data["password"] =Define::DEFAULT_USER_PASS;

                if(!$mobile) throw new \Exception("Vui lòng nhập số điện thoại.");

                $user = $this->userManager->addUser($data);

                $this->doLogin($user,$mobile,Define::DEFAULT_USER_PASS);

            }catch (\Exception $e){
                $view->setVariable('errorMessage', $e->getMessage());
                $data["id"]=$socialId;
                $data["name"] = $name;
                $view->setVariable('user', $data);

            }
        }
        $this->layout()->setTemplate('userLayoutLogin');
        return $view;
    }

    public function userResetPasswordAction(){
        include("module/phpmailer/class.smtp.php");
	  	include "module/phpmailer/class.phpmailer.php"; 
	  	include("module/phpmailer/config.php"); 
      	$request = $this->getRequest();
        if($request->isPost()) {
            try{
                $email = $request->getPost("resetEmail");

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \Exception('Please enter the correct email.');

//                $email = 'truonghm1980@gmail.com';
                $userManager = new UserManager($this->entityManager);
                $user = $userManager->getUserByEmail($email);
                if($user==null) throw new \Exception('Email do not exits');

                $token = Rand::getString(32,"0123456789qwertyuiopasdfghjklzxcvbnm", true);

                //get template with key
                //$emailTemplateManager = new EmailTemplateManager($this->entityManager);
                //$emailTemplate = $emailTemplateManager->getByKey('request-reset-password');
                //$template = $emailTemplate->getTemplate();

                //build url
                $http = isset($_SERVER['HTTPS']) ? "https://" : "http://";
                $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost";
                $url = $http.$host."/user-set-password/".$token.".html";//$this->url('user-set-password',['token'=>$token]);

                //render template to content mail
                //$m = new Mustache_Engine;
                //$renderTemplate = $m->render($template, array('name' => $user->getFullname(),'url'=>$url));

                //$html = new MimePart($renderTemplate);
               // $html->type = Mime::TYPE_HTML;
                //$html->charset = 'utf-8';
                //$html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

                //$message = new Message();
                //$message->setEncoding('UTF-8');
                //$message->getHeaders()->addHeaderLine('X-API-Key', 'FOO-BAR-BAZ-BAT');

                //$message->setFrom($emailTemplate->getReply(), 'VNHOMESTAY');
               // $message->addTo($user->getEmail(), $user->getFullname());
                //$message->setSubject($emailTemplate->getSubject());

               // $body = new MimeMessage();
                //$body->setParts([$html]);
                //$message->setBody($body);

                //$transport = new SmtpTransport();
                //$options   = new SmtpOptions($this->mailOptions);
                //$transport->setOptions($options);
                //$transport->send($message);
              	//duong note
              	$context.="<br />Xin chào: ". $user->getFullname();
                $context.="<br />Theo yeu cau xin lai mat khau moi cho tai khoan cua quy khach tren trang https://bestay.org<br>
Chung toi goi mail nay de xac nhan co phai ban muon that su xin lai password<br>
Neu dong y Quy khach vui long Click vao link ben duoi de xac nhan xin mat khau moi";
              	$context.="<br />URL: ". $url;
              	$mailTo = $email;
              	$subject = 'BESTAY - Xac nhan xin mat khau moi';
				sendMailer($subject, $context, $nameTo, $mailTo, $diachicc='', $emailFrom, $nameFrom);
              
                $this->flashMessenger()->addSuccessMessage('Please check your email to proceed with password change.');

                //update token
                $user->setToken($token);
                $user->setTokenCreatedDate(new DateTime);
                $this->entityManager->flush();

            }catch (\Exception $e){
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
            return $this->redirect()->toRoute('user-reset-password');
        }
        return new ViewModel();
    }

    public function userSetPasswordAction(){
        $token = $this->params()->fromRoute('token', null);
        $request = $this->getRequest();

        try{
            if($request->isPost()) {
                $token = $request->getPost("token");

                if($token==null)
                    throw new \Exception('The token is no longer valid, please perform the password reset function again.');

                $userManager = new UserManager($this->entityManager);
                $user = $userManager->getUserByToken($token);

                if($user==null)
                    throw new \Exception('The token is no longer valid, please perform the password reset function again.');

                $newPassword = $request->getPost("password");
                $bcrypt = new Bcrypt();
                $securePass = $bcrypt->create($newPassword);
                $user->setPassword($securePass);
                $user->setToken(null);
                $this->entityManager->flush();
                $this->flashMessenger()->addSuccessMessage('Password changed successfully.');
                return $this->redirect()->toRoute('user-set-password');
            }

        }catch (\Exception $e){
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            return $this->redirect()->toRoute('user-set-password',['token'=>$token]);

        }
        return new ViewModel(['token'=>$token]);
    }
}

?>