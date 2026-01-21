<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-28
 * Time: 10:06
 */

namespace Sulde\Service\Common;


class Define
{
    const _ORDER_DELIVERED_STATUS =21;//trang thai don hang da giao
    const _ORDER_DELIVERING_STATUS=2;//trang thai don hang dang giao
    const _ORDER_PAID_STATUS=3;//trang thai don hang da hoan thanh
    const _ORDER_WAIT_FOR_PAY_STATUS=31;//trang thai don hang da chua thanh toan
    const _ORDER_WAITING_PACKING_STATUS=1;//trang thai don hang da cho dong goi
    const _ORDER_PACKING_STATUS=11;//trang thai don hang dang dong goi
    const _ORDER_PACKED_STATUS=111;//trang thai don hang da dong goi

    const PUBLISHED =1;
    const INPUBLISHED =0;
    const ACTIVE =1;
    const INACTIVE =0;
    const DELETED =1;
    const ITEM_PAGE_COUNT =10;
    const DEFAULT_HOTEL_ACCOUNT =1;
    const DEFAULT_HOTEL_NAME ="System";
    const DEFAULT_USER_FULLNAME ="No Name";
    const DEFAULT_USER_PASS ="1@345678";
    const DEFAULT_USER_ROLE ="user";
    const DEFAULT_CALL_PHONE_NUMBER ="+84963883580";
    const DEFAULT_CALL_MONEY ="10000";
    const DEFAULT_BOOKING_MONEY ="20000";

    //define email campaign status
    const EMAIL_CAMPAIGN_STATUS_NG ='Not good';
    const EMAIL_CAMPAIGN_STATUS_G ='Good';
    const EMAIL_CAMPAIGN_STATUS_RUNNING ='Running';
    const EMAIL_CAMPAIGN_STATUS_COMPLETES ='Completed';

    //define email campaign step status
    const EMAIL_CAMPAIGN_STEP_STATUS_NG ='Not good';
    const EMAIL_CAMPAIGN_STEP_STATUS_G ='Good';
    const EMAIL_CAMPAIGN_STEP_STATUS_WAIT ='Wait';
    const EMAIL_CAMPAIGN_STEP_STATUS_QUEUE ='Queue';

    //Define aws email
    const EMAIL_NO_REPLY = 'noreply@bestay.org';
    const EMAIL_AWS_SMTP = 'smtp.gmail.com';
    const EMAIL_AWS_HOST = 'smtp.gmail.com';
    const EMAIL_AWS_PORT = 465;
    const EMAIL_AWS_CONNECT_TYPE = 'login';
    const EMAIL_AWS_CONNECT_PORT = 465;
    const EMAIL_AWS_CONNECT_SSL = 'tls';
//    const EMAIL_AWS_USERNAME = 'AKIAXSLECV4ENFQVSYC3';
//    const EMAIL_AWS_PASSWORD = 'BO1pr5etU4IDU7bsl5Rx3SP4w8MkniW9Zksvq8W/IATw';
    const EMAIL_AWS_USERNAME = 'truonghm1980@gmail.com';
    const EMAIL_AWS_PASSWORD = '@022';

}