<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 13:51
 */

namespace Supplier\Form;


use Sulde\Service\Common\ConfigManager;
use Zend\Form\Form;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\Size;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;

class SupplierForm extends Form
{
    private $action;

    public function __construct($action = "add")
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'supplier-form',
            'class'=>'form-horizontal'
        ]);
        $this->action = $action;
        $this->addElements();
        $this->validator();
    }


    private function addElements()
    {
        //Name
        $this->add([
            'type'=>'text',
            'name'=>'name',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập tên NCC.',
                'id'=>'name'
            ],
            'options'=>[
                'label'=>'Tên NCC',
                'label_attributes'=>[
                    'for' => 'name',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'mobile',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập điện thoại NCC.',
                'id'=>'mobile'
            ],
            'options'=>[
                'label'=>'Điện thoại',
                'label_attributes'=>[
                    'for' => 'mobile',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'address',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập địa chỉ NCC.',
                'id'=>'address'
            ],
            'options'=>[
                'label'=>'Địa chỉ',
                'label_attributes'=>[
                    'for' => 'address',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //btn
        $this->add([
            'type'=>'submit',
            'name'=>'btnSubmit',
            'attributes'=>[
                'class'=>'btn btn-success',
                'value'=>'Save',
                'id'=>'btnSubmit'
            ]
        ]);

    }

    private function validator()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name'=>'name',
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
                            NotEmpty::IS_EMPTY=>'Tên NCC không được để trống'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>200,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Tên tối thiểu %min% ký tự',
                            StringLength::TOO_LONG=>'Tên dài không quá %max% ký tự'
                        ]
                    ]
                ]
            ]
        ]);

        //Address
        $inputFilter->add([
            'name'=>'address',
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
                            NotEmpty::IS_EMPTY=>'Địa chỉ không được để trống.'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>100,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Địa chỉ tối thiểu %min% ký tự',
                            StringLength::TOO_LONG=>'Địa chỉ tối đa %max% ký tự'
                        ]
                    ]
                ]
            ]
        ]);

        //Mobile
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
                            NotEmpty::IS_EMPTY=>'Điện thoại không được để trống.'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>10,
                        'max'=>11,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Số điện thoại tối thiểu %min% số',
                            StringLength::TOO_LONG=>'Số điện thoại tối đa %max% số'
                        ]
                    ]
                ]
            ]
        ]);
    }
}