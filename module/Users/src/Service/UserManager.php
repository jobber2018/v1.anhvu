<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/15/19 11:58 AM
 *
 */



namespace Users\Service;


use DateTime;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Users\Entity\Roles;
use Users\Entity\User;
use Zend\Crypt\Password\Bcrypt;

class UserManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    public function searchUser($p_name=null,$p_mobile=null,$p_email=null,$p_status=1){

        //if($p_status==1) $status = Define::ACTIVE;
        //else $status = Define::UNACTIVE;

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from(User::class, 'u')
            ->where('u.id > 1')
            ->orderBy('u.created_date', 'DESC');
            //->setParameter('status', $p_status);

        //if($p_status!=null) {
            $queryBuilder->andWhere('u.status = :status')
                ->setParameter('status', $p_status);
        //}

        if($p_name) {
            $queryBuilder->andWhere('u.fullname LIKE :name')
                ->setParameter('name', $p_name.'%');
        }
        if($p_mobile) {
            $queryBuilder->andWhere('u.mobile LIKE :mobile')
                ->setParameter('mobile', '%'.$p_mobile.'%');
        }
        if($p_email) {
            $queryBuilder->andWhere('u.email = :email')
                ->setParameter('email', $p_email);
        }
        return $queryBuilder->getQuery();
    }

    public function checkEmailExists($email){
        $user = $this->entityManager->getRepository(User::class)->findOneByEmail($email);
        // if($user!==null) return true;
        // return false;
        return $user !== null;
    }

    /**
     * @param $email
     * @return User
     */
    public function getUserByEmail($email){
        $user = $this->entityManager->getRepository(User::class)->findOneByEmail($email);
        return $user;
    }

    public function checkMobileExists($mobile){
        $user = $this->entityManager->getRepository(User::class)->findOneBy(array("mobile"=>$mobile));
        return $user !== null;
    }

    /**
     * Get by id user
     * @param $userID
     * @return User
     */
    public function getByID($userID){
        $user = $this->entityManager->getRepository(User::class)->find($userID);
        return $user;
    }

    /**
     * @param $roleCode
     * @return Roles
     */
    public function getRoles($roleCode){
        $role = $this->entityManager->getRepository(Roles::class)->findOneBy(array("code"=>$roleCode));
        return $role;
    }

    /**
     * Add new user
     * @param $data
     * @return User
     * @throws \Exception
     */
    public function addUser($data){

        if($data['email'] && $this->checkEmailExists($data['email'])){
            throw new \Exception("Email ".$data['email']." đã có người sử dụng");
        }
        if($this->checkMobileExists($data['mobile'])){
            throw new \Exception("mobile ".$data['mobile']." đã có người sử dụng");
        }

        $user = new User;
        $user->setUsername($data['username']);
        $user->setFullname($data['fullname']);

        $common = new Common();
        $user->setMobile($common->verifyMobile($data['mobile']));

        if($data['birthdate']){
            $birthdate = new \DateTime($data['birthdate']);
            $birthdate->format('Y-m-d');
            $user->setBirthday($birthdate);
        }

        $user->setEmail($data['email']);

        $user->setCreatedDate(new DateTime);

        if($data['status']) $user->setStatus($data['status']);
        else $user->setStatus(0);

        if($data['social_id']) $user->setSocialId($data['social_id']);
        if($data['avatar']) $user->setAvatar($data['avatar']);

        if($data['role']) $user->setRole($data['role']);
        else $user->setRole(Define::DEFAULT_USER_ROLE);

        $password = $data["password"];
        if(!$password) $password = Common::randomPassword();

        $bcrypt = new Bcrypt();
        $password = $bcrypt->create($password);
        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;

    }


    public function editUser($user,$data){

        $user->setFullname($data['fullname']);
        if($data['email']) $user->setEmail($data['email']);

        try {
            $birthdate = new \DateTime($data['birthdate']);
            $birthdate->format('Y-m-d');
            $user->setBirthday($birthdate);
        } catch (\Exception $e) {
            //throw new \Exception($e->getMessage());
        }

        $this->entityManager->flush();
        return $user;
    }

    public function removeUser(User $user){
        $user->setStatus(0);
        //$this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * Change password
     * @param $user
     * @param $data
     * @return bool
     */
    public function changePassword($user,$data,$isCheckOldPw=true){
        $securePass = $user->getPassword();
        $password = $data['old_pw'];
        if($isCheckOldPw==true){
            if(!$this->verifyPassword($securePass,$password)){
                return false;
            }
        }

        $newPassword = $data['new_pw'];

        $bcrypt = new Bcrypt();
        $securePass = $bcrypt->create($newPassword);
        $user->setPassword($securePass);

        $this->entityManager->flush();
        return true;
    }

    public function verifyPassword($securePass,$password ){
        $bcrypt = new Bcrypt();
        if ($bcrypt->verify($password, $securePass)) {
            return true;
        }
        return false;
    }

    /*public function createTokenPasswordReset($user){
        $token = Rand::getString(32,"0123456789qwertyuiopasdfghjklzxcvbnm", true);
        $user->setPasswordResetToken($token);

        $dateCreate = date('Y-m-d H:i:s');
        $dateCreate = new \DateTime($dateCreate);
        $dateCreate->format('Y-m-d H:i:s');
        $user->setPasswordResetTokenDate($dateCreate);
        $this->entityManager->flush();

        $http = isset($_SERVER['HTTPS']) ? "https://" : "http://";
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost";
        $url = $http.$host."/zendframework/public/set-password/".$token;

        $bodyMessage = "Chào bạn, ".$user->getFullname()."
                        \nBạn vui lòng chọn vào link bên dưới để thực hiện reset password:
                        \n$url
                        \nNếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua thông báo này.
                        ";


        $message = new Message();
        $message->addTo($user->getEmail());
        $message->addFrom("jobber.vn@gmail.com");
        $message->setSubject('ResetPassword!');
        $message->setBody($bodyMessage);

        $transport = new SmtpTransport();
        $options   = new SmtpOptions([
            'name'              => 'smtp.gmail.com',
            'host'              => 'smtp.gmail.com',
            'port'              => 587,
            'connection_class'  => 'login',
            'connection_config' => [
                'username' => 'jobber.vn@gmail.com',
                'password' => 'aaa@018',
                'port'     => 587,
                'ssl'      => 'tls'
            ],
        ]);
        $transport->setOptions($options);
        $transport->send($message);

    }*/


    /**
     * @param User $user
     * @param $loginInfo
     * @return User
     * @throws \Exception
     */
    public function updateLogin(User $user, $loginInfo){
        $user->setLoginDate(new DateTime);
        $user->setLoginInfo($loginInfo);
        $this->entityManager->flush();
        return $user;
    }

    public function getBySocialId($socialId){
        return $this->entityManager->getRepository(User::class)->findOneBy(array("social_id"=>$socialId));
    }

    /**
     * @param $token
     * @return User
     */
    public function getUserByToken($token)
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(array("token"=>$token));
    }

    public function activeUser(User $user)
    {
        if($user->getStatus()==0)
            $user->setStatus(1);
        else $user->setStatus(0);

        $this->entityManager->flush();
    }
}