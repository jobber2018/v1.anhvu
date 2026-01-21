<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-25
 * Time: 12:48
 */

namespace Sulde\Service\Common;


class ConfigManager
{

//  	private $geoKey = 'AIzaSyAmKyH1mE6NGFXstQCAaUAzqvvAzOpT_tU'; //key on account truonghm1980@gmail.com
  	private $geoKey = 'AIzaSyBSVcl6NTQVJCFHAdPda7xnaYbxGL0rrG8';//key on account jobber.vn@gmail.com | United States

//    private $geoApiUrl = 'https://maps.google.com/maps/api/geocode/json';
    private $geoApiUrl = 'https://maps.googleapis.com/maps/api/js';

    public function getGeoKey(){
        return $this->geoKey;
    }

    public function getGeoApiUrl(){
        $url = $this->geoApiUrl . '?key='.$this->geoKey;
        return $url;
    }

    static function getDay(){
        return  [
            'Monday'=> '2',
            'Tuesday'=>'3',
            'Wednesday' => '4',
            'Thursday' => '5',
            'Friday'=>'6',
            'Saturday'=>'7',
            'Sunday'=>'8'
        ];
    }

    //154:(Chi phí nhân công trực tiếp): tien luong
    //211:(TSCĐ hữu hình) -chi phi khau hao tai san
    //642:(Chi phí quản lý doanh nghiệp) - xăng xe, mạng ....
    //641:(Chi phí bán hàng) - Thuê nhà làm cơ sở bán hàng, kho chứa hàng bán
    static function getAccountFinance(){
        return array(154,211,642,641,3339);
    }

    static function isStockChecking(){
        return  0;//=1 dang kiem kho, =0 khong kiem kho
    }

    static function getOrderStatus(){
        return  [
            '-2'=> 'Khách tạo',
            '-1'=> 'Chờ xử lý',
            '0'=> 'Huỷ đơn',
            '1'=> 'Chờ đóng gói',
            '11'=> 'Đang đóng gói',
            '111'=> 'Đã đóng gói',
            '2'=>'Đang giao',
            '21'=>'Đã giao',
            '3'=>'Đã thanh toán',
            '31'=>'Chưa thanh toán'
        ];
    }
    static function getCausesReturn(){
        return  [
            'pick'=> 'Bốc nhầm',
            'create'=> 'Lên đơn nhầm',
            'customer'=> 'Khách không lấy',
            'defective'=> 'Hàng lỗi',
            'other'=> 'Khác'
        ];
    }

    static function getCarColor(){
        return  [
            'AM-29H23118'=> '#4085e8',
            'PM-29H23118'=> '#f9071e',
            'ALL-29H23118'=> '#9f0eee',
            'AM-30M71302'=> '#283bec',
            'PM-30M71302'=> '#ee0e4c',
            'ALL-30M71302'=> '#9300cd',
            'AM-30M61300'=> '#0eee0e',
            'PM-30M61300'=> '#c1ee0e',
            'ALL-30M61300'=> '#0ee4ee',
        ];
    }

    static function getRoleAdmin(){
        return[
            'admin'=> 'Admin',
            'staff' => 'Nhân viên',
            'editor'=> 'Biên tập',
            'customer'=>'Khách hàng (Admin homestay)',
            'user' => 'Người dùng'
        ];
    }

    static function getRoleUser(){
        return[
            'customer'=>'Khách hàng (Admin homestay)',
            'user' => 'Người dùng'
        ];
    }

    static function facebookApi(){
        return[
            'app_id' => '1133385857049196',
            'app_secret' => '29acfc6d2f35834b5f40feb5879e0bb4',
            'default_graph_version' => 'v2.10',
            //'default_access_token' => '{access-token}', // optional
        ];
    }
    static function facebookCallBackURL(){
        return 'http://bestay.org/login-with-facebook.html';
    }

    static function googleApi(){
        return[
            'client_id' => '484832435939-fh5dtbdt0clnh2jnc14r63u8162ivk3u.apps.googleusercontent.com',
            'client_secret' => 'w9kBMN0egW45gTW9o0_zp9Eo',
            'redirect_url' => 'https://bestay.org/login-with-google.html',
            'developer_key' => 'AIzaSyDIruF5R2IU_TpebcJk7bsrIq6TNBRp6E4'
          	//'developer_key' => 'AIzaSyB2_JOwyDmzvX8Hx4ONZR1AKxpc5aly_B8'
          	//'developer_key' => 'AIzaSyBnqhMOsaPMRnT_Q7IRNWGylitnb67qtBI'
        ];
    }
}