<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-27
 * Time: 18:51
 */

namespace Sulde\Service\Common;


use DateTime;
use League\Flysystem\Exception;

class Common
{
    //lam tron so
    static function round($p_money){
        try{
            return round(($p_money)/1000)*1000;;
        }catch (\Exception $e){
            return 0;
        }
    }

    static function verifyMobile($p_mobile){
        //02.422.469.246 - so co dinh viettel
        //00.936.408.678
        $search = array("-", "(", ")", " ",".");
        $mobile = str_replace($search, "", $p_mobile);
        $mobile = "0".substr($mobile,-9);
        return $mobile;
    }

    static function verifyEmail($p_email){
        $email = filter_var($p_email, FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    static function formatMoney($p_money){
        try{
            return number_format($p_money);
        }catch (\Exception $e){
            return 0;
        }
    }
    static function formatDateTime($p_dateTime){
        try{
            //$p_dateTime is datetime off php
            if($p_dateTime instanceof DateTime)
                return $p_dateTime->format('d/M/Y H:i:s');

            //$p_dateTime is datetime string
            if(strtotime($p_dateTime)!==false)
                return date('d/M/Y H:i:s',strtotime($p_dateTime));

            throw new \Exception('Date time is null');

        }catch (\Exception $e){
            return "--";
        }
    }
    static function formatTime($p_time){
        try{
            if($p_time==null) return '';
            return $p_time->format('H:s:i');
        }catch (\Exception $e){
            return "";
        }
    }
    static function formatDate($p_date){
        try{
            return $p_date->format('d/M/Y');
        }catch (\Exception $e){
            return "--";
        }
    }

    static function isDate($p_date){
        try{
            if(strtotime($p_date)!==false)
                return DateTime::createFromFormat('Y-m-d', date('Y-m-d',strtotime($p_date)));
            else return false;
        }catch (\Exception $e){
            return false;
        }
    }

    static function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, count($alphabet)-1);
            $pass[$i] = $alphabet[$n];
        }
        return implode(" ",$pass);
    }

    public static function getTimeAgo(\DateTime $p_datetime)
    {

        try{
            $time_difference = time() - strtotime($p_datetime->format('Y-m-d H:i:s'));

        if( $time_difference < 1 ) { return 'less than 1 second ago'; }
        $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second'
        );

        foreach( $condition as $secs => $str )
        {
            $d = $time_difference / $secs;

            if( $d >= 1 )
            {
                $t = round( $d );
                return $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
            }
        }

        }catch (Exception $e) {
            return "";
        }

        
    }

    public static function convertAlias($p_cs){

//        $cs=preg_replace('/([^\pL\.\ ]+)/u', '', strip_tags(trim($p_cs)));
        $cs=strip_tags(trim($p_cs));

        $vietnamese=array("''","."," ","à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă","ằ","ắ","ặ","ẳ","ẵ", "è","é","ẹ","ẻ","ẽ","ê","ề","ế","ệ","ể","ễ", "ì","í","ị","ỉ","ĩ", "ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ","ờ","ớ","ợ","ở","ỡ", "ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ", "ỳ","ý","ỵ","ỷ","ỹ", "đ", "À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă","Ằ","Ắ","Ặ","Ẳ","Ẵ", "È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ", "Ì","Í","Ị","Ỉ","Ĩ", "Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ","Ờ","Ớ","Ợ","Ở","Ỡ", "Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ", "Ỳ","Ý","Ỵ","Ỷ","Ỹ", "Đ");
        $latin=array("","","-","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a", "e","e","e","e","e","e","e","e","e","e","e", "i","i","i","i","i", "o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o", "u","u","u","u","u","u","u","u","u","u","u", "y","y","y","y","y", "d", "A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A", "E","E","E","E","E","E","E","E","E","E","E", "I","I","I","I","I", "O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O", "U","U","U","U","U","U","U","U","U","U","U", "Y","Y","Y","Y","Y", "D");
        $csLatin= str_replace($vietnamese,$latin,$cs);

        $csLatin = str_replace("--","-",trim($csLatin));
        return Common::slugify($csLatin);
    }

    public static function slugify($text)
    {
        // replace non letter or digits by -
        //$text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public static function substrwords($text, $maxchar, $end='...') {
        if (strlen($text) > $maxchar || $text == '') {
            $words = preg_split('/\s/', $text);
            $output = '';
            $i      = 0;
            while (1) {
                $length = strlen($output)+strlen($words[$i]);
                if ($length > $maxchar) {
                    break;
                }
                else {
                    $output .= " " . $words[$i];
                    ++$i;
                }
            }
            $output .= $end;
        }
        else {
            $output = $text;
        }
        return $output;
    }

    public static function getDomain(){
        $http = isset($_SERVER['HTTPS']) ? "https://" : "http://";
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost";
        return $http.$host;
    }

    public static function isPermission($controller, $action, $privileges)
    {
//        return true;
        foreach ($privileges as $key=>$privilege){
            if($privilege['controller']==$controller && $privilege['action']==$action)
                return true;
        }
        return false;
    }

    public static function convertNumberToWords($number) {

        $hyphen      = ' ';
        $conjunction = ' ';
        $separator   = ' ';
        $negative    = 'âm ';
        $decimal     = ' phẩy ';
        $one		 = 'mốt';
        $ten         = 'lẻ';
        $dictionary  = array(
            0                   => 'Không',
            1                   => 'Một',
            2                   => 'Hai',
            3                   => 'Ba',
            4                   => 'Bốn',
            5                   => 'Năm',
            6                   => 'Sáu',
            7                   => 'Bảy',
            8                   => 'Tám',
            9                   => 'Chín',
            10                  => 'Mười',
            11                  => 'Mười một',
            12                  => 'Mười hai',
            13                  => 'Mười ba',
            14                  => 'Mười bốn',
            15                  => 'Mười lăm',
            16                  => 'Mười sáu',
            17                  => 'Mười bảy',
            18                  => 'Mười tám',
            19                  => 'Mười chín',
            20                  => 'Hai mươi',
            30                  => 'Ba mươi',
            40                  => 'Bốn mươi',
            50                  => 'Năm mươi',
            60                  => 'Sáu mươi',
            70                  => 'Bảy mươi',
            80                  => 'Tám mươi',
            90                  => 'Chín mươi',
            100                 => 'trăm',
            1000                => 'ngàn',
            1000000             => 'triệu',
            1000000000          => 'tỷ',
            1000000000000       => 'nghìn tỷ',
            1000000000000000    => 'ngàn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if ($number < 0) {
            return $negative . self::convertNumberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= strtolower( $hyphen . ($units==1?$one:$dictionary[$units]) );
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= strtolower( $conjunction . ($remainder<10?$ten.$hyphen:null) . self::convertNumberToWords($remainder) );
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number - ($numBaseUnits*$baseUnit);
                $string = self::convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= strtolower( $remainder < 100 ? $conjunction : $separator );
                    $string .= strtolower( self::convertNumberToWords($remainder) );
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    public static function getRemoteFileInfo($url) {
        $url='https://anhvu.store'.$url;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $data = curl_exec($ch);
        $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        /*return [
            'fileExists' => (int) $httpResponseCode == 200,
            'fileSize' => (int) $fileSize
        ];*/

//        if($fileSize)?$fileSize:0;
        return (int) $fileSize;
    }
}