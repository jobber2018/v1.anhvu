<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/15/19 2:59 PM
 *
 */

namespace Users\Form;

use Sulde\Service\Common\ConfigManager;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Date;
use Zend\Validator\EmailAddress;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;

class UserForm extends Form
{
    private $action;

    public function __construct($action = "add")
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'user-form',
            'class'=>'form-horizontal'
        ]);
        $this->action = $action;
        $this->addElements();
        $this->validator();
    }

    private function addElements()
    {
        //Mobile
        $this->add([
            'type'=>'text',
            'name'=>'mobile',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Input mobile number',
                'id'=>'mobile'
            ],
            'options'=>[
                'label'=>'Mobile:',
                'label_attributes'=>[
                    'for' => 'mobile',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        if($this->action=="add"){
            //password
            $this->add([
                'type'=>'password',
                'name'=>'password',
                'attributes'=>[
                    'class'=>'form-control',
                    'placeholder'=>'Nhập mật khẩu',
                    'id'=>'password'
                ],
                'options'=>[
                    'label'=>'Mật khẩu:',
                    'label_attributes'=>[
                        'for' => 'password',
                        'class'=>'col-md-3 control-label'
                    ]
                ]
            ]);

            //confirm_password
            $this->add([
                'type'=>'password',
                'name'=>'confirm_password',
                'attributes'=>[
                    'class'=>'form-control',
                    'placeholder'=>'Nhập lại mật khẩu',
                    'id'=>'confirm_password'
                ],
                'options'=>[
                    'label'=>'Nhập lại mật khẩu:',
                    'label_attributes'=>[
                        'for' => 'confirm_password',
                        'class'=>'col-md-3 control-label'
                    ]
                ]
            ]);
        }

        //fullname
        $this->add([
            'type'=>'text',
            'name'=>'fullname',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập họ tên',
                'id'=>'fullname'
            ],
            'options'=>[
                'label'=>'Họ tên:',
                'label_attributes'=>[
                    'for' => 'fullname',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //birthdate
        $this->add([
            'type'=>'Date',
            'name'=>'birthdate',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Chọn ngày sinh:',
                'id'=>'birthdate'
            ],
            'options'=>[
                'label'=>'Ngày sinh:',
                'label_attributes'=>[
                    'for' => 'birthdate',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //email
        $this->add([
            'type'=>'email',
            'name'=>'email',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập email',
                'id'=>'email'
            ],
            'options'=>[
                'label'=>'Email:',
                'label_attributes'=>[
                    'for' => 'email',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);
        $this->add([
            'type'=>'select',
            'name'=>'role',
            'attributes'=>[
                'class'=>'form-control select',
                'id'=>'role'
            ],
            'options'=>[
                'label'=>'Role(s):',
                'label_attributes'=>[
                    'for' => 'role',
                    'class'=>'col-md-3 control-label'
                ],
                'value_options'=>ConfigManager::getRoleAdmin()
            ]
        ]);

        //btn
        $this->add([
            'type'=>'submit',
            'name'=>'btnSubmit',
            'attributes'=>[
                'class'=>'btn btn-success',
                'value'=>'Save'
            ]
        ]);
        
    }

    private function validator()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        if($this->action=="add"){
            $inputFilter->add([
                'name'=>'mobile',
                'required'=>true,
                'filters'=>[
                    ['name'=>'StringTrim'],
                    ['name'=>'StringToLower'],
                    ['name'=>'StripTags'],
                    ['name'=>'StripNewlines']
                ],
                'validators'=>[
                    [
                        'name'=>'NotEmpty',
                        'options'=>[
                            'break_chain_on_failure'=>true,
                            'messages'=>[
                                NotEmpty::IS_EMPTY=>'Mobile không được rỗng'
                            ]
                        ]
                    ],
                    [
                        'name'=>'StringLength',
                        'options'=>[
                            'min'=>8,
                            'max'=>50,
                            'messages'=>[
                                StringLength::TOO_SHORT=>'Username ít nhất %min% kí tự',
                                StringLength::TOO_LONG=>'Username không quá %max% kí tự'
                            ]
                        ]
                    ]
                ]
            ]);

            $inputFilter->add([
                'name'=>'password',
                'required'=>true,
                'filters'=>[
//                    ['name'=>'StringTrim'],
//                    ['name'=>'StripTags'],
//                    ['name'=>'StripNewlines']
                ],
                'validators'=>[
                    [
                        'name'=>'NotEmpty',
                        'options'=>[
                            'break_chain_on_failure'=>true,
                            'messages'=>[
                                NotEmpty::IS_EMPTY=>'Mật khẩu không được rỗng'
                            ]
                        ]
                    ],
                    [
                        'name'=>'StringLength',
                        'options'=>[
                            'break_chain_on_failure'=>true,
                            'min'=>8,
                            'max'=>20,
                            'messages'=>[
                                StringLength::TOO_SHORT=>'Mật khẩu ít nhất %min% kí tự',
                                StringLength::TOO_LONG=>'Mật khẩu không quá %max% kí tự',
                            ]
                        ]
                    ],
                    [
                        'name'=>"Regex",
                        'options'=>[
                            'break_chain_on_failure'=>true,
                            'pattern'=>'/[a-zA-Z0-9_-]/',
                            'messages'=>[
//                                Regex::INVALID=> "Pattern %pattern% không chính xác",
//                                Regex::NOT_MATCH=> "Mật khẩu phải chưa các kí tự sau %pattern%",
//                                Regex::ERROROUS=> "Có lỗi nội bộ đối với pattern %pattern%",
                            ]
                        ]
                    ],
                    [
                        'name'=>"Regex",
                        'options'=>[
                            'break_chain_on_failure'=>true,
                            'pattern'=>'/[!@#$%^&]/',
                            'messages'=>[
//                                Regex::INVALID=> "Pattern %pattern% không chính xác",
//                                Regex::NOT_MATCH=> "Mật khẩu phải chưa các kí tự sau %pattern%",
//                                Regex::ERROROUS=> "Có lỗi nội bộ đối với pattern %pattern%",
                            ]
                        ]
                    ]
                ]
            ]);
            $inputFilter->add([
                'name'=>'confirm_password',
                'required'=>true,
                'filters'=>[
//                    ['name'=>'StringTrim'],
//                    ['name'=>'StripTags'],
//                    ['name'=>'StripNewlines']
                ],
                'validators'=>[
                    [
                        'name'=>'NotEmpty',
                        'options'=>[
                            'break_chain_on_failure'=>true,
                            'messages'=>[
                                NotEmpty::IS_EMPTY=>'Mật khẩu nhập lại không được rỗng'
                            ]
                        ]
                    ],
                    [
                        'name'=>'Identical',
                        'options'=>[
                            'break_chain_on_failure'=>true,
                            'token'=>'password',
                            'messages'=>[
                                Identical::NOT_SAME=>'Mật khẩu không giống nhau',
                                Identical::MISSING_TOKEN=>'Missing token'
                            ]
                        ]
                    ],
                    [
                        'name'=>"Regex",
                        'options'=>[
                            'break_chain_on_failure'=>true,
                            'pattern'=>'/[a-zA-Z0-9_-]/',
                            'messages'=>[
//                                Regex::INVALID=> "Pattern %pattern% không chính xác",
//                                Regex::NOT_MATCH=> "Mật khẩu phải chưa các kí tự sau %pattern%",
//                                Regex::ERROROUS=> "Có lỗi nội bộ đối với pattern %pattern%",
                            ]
                        ]
                    ],
                    [
                        'name'=>"Regex",
                        'options'=>[
                            'pattern'=>'/[!@#$%^&]/',
                            'messages'=>[
//                                Regex::INVALID=> "Pattern %pattern% không chính xác",
//                                Regex::NOT_MATCH=> "Mật khẩu phải chưa các kí tự sau %pattern%",
//                                Regex::ERROROUS=> "Có lỗi nội bộ đối với pattern %pattern%",
                            ]
                        ]
                    ]
                ]
            ]);
        }
        $inputFilter->add([
            'name'=>'role',
            'required'=>false
        ]);
        $inputFilter->add([
            'name'=>'email',
            'required'=>false,
            'filters'=>[
                ['name'=>'StringTrim'],
                ['name'=>'StripTags'],
                ['name'=>'StripNewlines']
            ],
            'validators'=>[
                [
                    'name'=>'Regex',
                    'break_chain_on_failure'=>true,
                    'options'=>[
                        'pattern'=>"/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/",
                        'messages'=>[
                            Regex::NOT_MATCH=>'Email phải chứa các kí tự %pattern%'
                        ]
                    ]
                ],
                [
                    'name'=>'NotEmpty',
                    'options'=>[
                        'break_chain_on_failure'=>true,
                        'messages'=>[
                            NotEmpty::IS_EMPTY=>'Email không được rỗng'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'break_chain_on_failure'=>true,
                        'min'=>10,
                        'max'=>50,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Email ít nhất %min% kí tự',
                            StringLength::TOO_LONG=>'Email không quá %max% kí tự',
                        ]
                    ]
                ],
                [
                    'name'=>'EmailAddress',
                    'break_chain_on_failure'=>true,
                    'options'=>[
                        'messages'=>[
                            EmailAddress::INVALID_FORMAT=>'Email không đúng định dạng',
                            EmailAddress::INVALID_HOSTNAME=>'Hostname không đúng'
                        ]
                    ]
                ],

            ]
        ]);
        $inputFilter->add([
            'name'=>'fullname',
            'required'=>true,
            'filters'=>[
                ['name'=>'StringTrim'],
                ['name'=>'StripTags'],
                ['name'=>'StripNewlines']
            ],
            'validators'=>[
                [
                    'name'=>'NotEmpty',
                    'options'=>[
                        'break_chain_on_failure'=>true,
                        'messages'=>[
                            NotEmpty::IS_EMPTY=>'Họ tên không được rỗng'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'max'=>100,
                        'messages'=>[
                            StringLength::TOO_LONG=>'Họ tên không quá %max% kí tự',
                        ]
                    ]
                ]
            ]
        ]);

        $inputFilter->add([
            'name'=>'birthdate',
            'required'=>false,
            'validators'=>[
                [
                    'name'=>'Date',
                    'options'=>[
                        'break_chain_on_failure'=>true,
                        'messages'=>[
                            Date::INVALID_DATE=>'Không đúng định dạng ngày tháng'
                        ]
                    ]
                ],
                [
                    'name'=>'NotEmpty',
                    'options'=>[
                        'break_chain_on_failure'=>true,
                        'messages'=>[
                            NotEmpty::IS_EMPTY=>'Ngày sinh không được rỗng'
                        ]
                    ]
                ],

            ]
        ]);
    }
}