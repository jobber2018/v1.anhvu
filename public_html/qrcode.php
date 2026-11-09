<?php
define('CRC16POLYN', 0x1021);

include('phpqrcode/qrlib.php');
$payload="000201"; //Phiên bản đặc tả QR Code
$initMethod="010211";//Phương thức khởi tạo QR dynamic
/*3856 //Thông tin định danh ĐVCNTT
    0010A000000727 //dinh doanh vietqr
    0126 //Tổ chức thụ hưởng( NHTV,TGTT)
        0006970415
        0112101877709178
    0208QRIBFTTA //dịch vụ chuyển nhanh */
//$accountInformation="38560010A0000007270126000697041501121018777091780208QRIBFTTA";

//thay đổi thông tin tk và chạy để lấy tk thô: https://anhvu.store/vietqr-bank.html
$accountInformation="00020101021138450010A000000727012700069704300113999094602102153037045802VN6304B566";
//38550010A000000727012500069704260111800015599330208QRIBFTTA
//630455EE
$currencyCode="5303704";//Mã tiền tệ = VND

$transactionAmount = @$_GET['amount'];
if(strlen($transactionAmount)){
    $transactionAmount="540".strlen($transactionAmount).$transactionAmount;
}

$countryCode="5802VN";//ma quoc gia = vn

//62 thong tin bo sung, 08 //ten dichj vu
$info = @$_GET['info'];
if($info){
    $lenInfo=strlen($info);
    if($lenInfo>90) $info=substr(0,80,$info);

    $info="62".($lenInfo+4)."08".$lenInfo.$info;
}
//$dataField="62280824Test noi dung thanh toan"; //thong tin bo sung, 0824 //ten dichj vu
//thanh toan don hang ODN00123
$CRC="6304";
$content = $payload.$initMethod.$accountInformation.$currencyCode.$transactionAmount.$countryCode.$info.$CRC;
$CRC.=dechex(CRC16Normal($content)); //Mã kiểm chứng giá trị
$content = $payload.$initMethod.$accountInformation.$currencyCode.$transactionAmount.$countryCode.$info.$CRC;
//echo $transactionAmount;
QRcode::png($content);


function CRC16Normal($buffer) {
    $result = 0xFFFF;
    if (($length = strlen($buffer)) > 0) {
        for ($offset = 0; $offset < $length; $offset++) {
            $result ^= (ord($buffer[$offset]) << 8);
            for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                if (($result <<= 1) & 0x10000) $result ^= CRC16POLYN;
                $result &= 0xFFFF;
            }
        }
    }
    return $result;
}

?>