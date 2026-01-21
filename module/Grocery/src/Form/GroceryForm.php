<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 13:51
 */

namespace Grocery\Form;


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

class GroceryForm extends Form
{
    private $action;

    public function __construct($action = "add")
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'grocery-form',
            'class'=>'form-horizontal'
        ]);
        $this->action = $action;
        $this->addElements();
        $this->validator();
    }


    private function addElements()
    {
        $this->add([
            'name'=>'imageFile',
            'attributes'=>[
                'type'=>'file',
                'id'=>'imageFile'
            ],
            'options'=>[
                'label'=>'Default image'
            ]
        ]);

        //Name
        $this->add([
            'type'=>'text',
            'name'=>'grocery_name',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập tên cửa hàng.',
                'id'=>'grocery_name'
            ],
            'options'=>[
                'label'=>'Tên cửa hàng',
                'label_attributes'=>[
                    'for' => 'grocery_name',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'owner_name',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập tên chủ cửa hàng.',
                'id'=>'owner_name'
            ],
            'options'=>[
                'label'=>'Tên chủ cửa hàng',
                'label_attributes'=>[
                    'for' => 'owner_name',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'address',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập địa chỉ cửa hàng.',
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
        $this->add([
            'type'=>'text',
            'name'=>'mobile',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập số điện thoại chủ cửa hàng.',
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


        //lat
        $this->add([
            'type'=>'hidden',
//            'type'=>'text',
            'name'=>'lat',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Input lat',
                'id'=>'lat'
            ],
            'options'=>[
                'label'=>'Lat (*):',
                'label_attributes'=>[
                    'for' => 'lat',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'delivery_note',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Giờ giao hàng, vận chuyển bằng oto hay xe máy...',
                'id'=>'delivery_note'
            ],
            'options'=>[
                'label'=>'Delivery note:',
                'label_attributes'=>[
                    'for' => 'delivery_note',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);
        //lng
        $this->add([
            'type'=>'hidden',
//            'type'=>'text',
            'name'=>'lng',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Input lng',
                'id'=>'lng'
            ],
            'options'=>[
                'label'=>'Lng (*):',
                'label_attributes'=>[
                    'for' => 'lng',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'checkbox',
            'name'=>'zalo_connect',
            'attributes'=>[
                'id'=>'zalo_connect'
            ],
            'options'=>[
                'label'=>'Zalo connect?',
                'label_attributes'=>[
                    'for' => 'zalo_connect',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'select',
            'name'=>'approach',
            'attributes'=>[
                'class'=>'form-control select',
                'id'=>'approach'
            ],
            'options'=>[
                'label'=>'Approach:',
                'label_attributes'=>[
                    'for' => 'approach',
                    'class'=>'col-md-3 control-label'
                ],
                'value_options'=>["0"=>"...","1"=>"Online","2"=>"Online/Offline","3"=>"Offline"]
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

        //Name
        $inputFilter->add([
            'name'=>'grocery_name',
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
                            NotEmpty::IS_EMPTY=>'Tên cửa hàng không được để trống'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>200,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Tên cửa hàng tối thiểu %min% ký tự',
                            StringLength::TOO_LONG=>'Tên cửa hàng dài không quá %max% ký tự'
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
                            NotEmpty::IS_EMPTY=>'Địa chỉ cửa hàng không được để trống.'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>100,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Hotel name least %min% characters',
                            StringLength::TOO_LONG=>'Hotel name no more than %max% characters'
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
                            NotEmpty::IS_EMPTY=>'Điện thoại chủ cửa hàng không được để trống.'
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

        //Owner grocery
        $inputFilter->add([
            'name'=>'owner_name',
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
                            NotEmpty::IS_EMPTY=>'Tên chủ cửa hàng không được để trống.'
                        ]
                    ]
                ]
            ]
        ]);

        $this->uploadInputFilter();
    }

    public function uploadInputFilter(){
        $fileUpload = new FileInput('imageFile');
        $fileUpload->setRequired(false);
        //fileSize
        $size = new Size(['max'=>20000*1024]); //200kB
        $size->setMessages([
            Size::TOO_BIG=>'File bạn chọn quá lớn, vui lòng chọn file có kích thước bé hơn %max%'
        ]);

        //MimeType
        //image/png, image/jpeg, image/jpg
        $mimeType = new MimeType('image/png, image/jpeg, image/jpg');
        $mimeType->setMessages([
            MimeType::FALSE_TYPE=>'Kiểu file %type% không được phép chọn',
            MimeType::NOT_DETECTED=>'MimeType không xác định',
            MimeType::NOT_READABLE => 'MineType không thể đọc'
        ]);

        $fileUpload->getValidatorChain()
            ->attach($size, true, 2)
            ->attach($mimeType,true,1);

        $inputFilter = new InputFilter();
        $inputFilter->add($fileUpload);
        $this->setInputFilter($inputFilter);
    }
}