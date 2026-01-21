<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 13:51
 */

namespace Product\Form;


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

class ProductForm extends Form
{
    private $action;

    public function __construct($action = "add",$p_cat,$p_unit)
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'product-form',
            'class'=>'form-horizontal'
        ]);
        $this->action = $action;
        $this->addElements($p_cat,$p_unit);
        $this->validator();
    }


    private function addElements($p_cat,$p_unit)
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
            'name'=>'name',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập tên sản phẩm',
                'id'=>'name'
            ],
            'options'=>[
                'label'=>'Tên sản phẩm (*)',
                'label_attributes'=>[
                    'for' => 'name',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'code',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Mã sản phẩm',
                'id'=>'code'
            ],
            'options'=>[
                'label'=>'Mã sản phẩm (*)',
                'label_attributes'=>[
                    'for' => 'code',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'code_1',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Mã sản phẩm 1',
                'id'=>'code_1'
            ],
            'options'=>[
                'label'=>'Mã sản phẩm 1',
                'label_attributes'=>[
                    'for' => 'code_1',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'code_2',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Mã sản phẩm 2',
                'id'=>'code_2'
            ],
            'options'=>[
                'label'=>'Mã sản phẩm 2',
                'label_attributes'=>[
                    'for' => 'code_2',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);
        $this->add([
            'type'=>'text',
            'name'=>'code_3',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Mã sản phẩm 3',
                'id'=>'code_3'
            ],
            'options'=>[
                'label'=>'Mã sản phẩm 3',
                'label_attributes'=>[
                    'for' => 'code_3',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'pack_code',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Mã thùng',
                'id'=>'pack_code'
            ],
            'options'=>[
                'label'=>'Mã thùng',
                'label_attributes'=>[
                    'for' => 'pack_code',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'checkbox',
            'name'=>'active',
            'attributes'=>[
                'class'=>'icheckbox',
                'placeholder'=>'Active',
                'id'=>'active'
            ],
            'options'=>[
                'label'=>'Active',
                'label_attributes'=>[
                    'for' => 'active',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //Price
        $this->add([
            'type'=>'number',
            'name'=>'price',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'price'
            ],
            'options'=>[
                'label'=>'Giá bán (*)',
                'label_attributes'=>[
                    'for' => 'price',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //norm
        $this->add([
            'type'=>'number',
            'name'=>'norm',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'norm'
            ],
            'options'=>[
                'label'=>'Định mức',
                'label_attributes'=>[
                    'for' => 'norm',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //norm input
        $this->add([
            'type'=>'number',
            'name'=>'norm_input',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'norm_input'
            ],
            'options'=>[
                'label'=>'Định mức nhập',
                'label_attributes'=>[
                    'for' => 'norm_input',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //box unit
        $this->add([
            'type'=>'number',
            'name'=>'box_unit',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'box_unit'
            ],
            'options'=>[
                'label'=>'Quy cách (*)',
                'label_attributes'=>[
                    'for' => 'box_unit',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //weight
        $this->add([
            'type'=>'text',
            'name'=>'weight',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'weight'
            ],
            'options'=>[
                'label'=>'Trọng lượng',
                'label_attributes'=>[
                    'for' => 'weight',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //Cat
        $this->add([
            'type'=>'select',
            'name'=>'product_cat',
            'attributes'=>[
                'class'=>'form-control select',
                'id'=>'product_cat'
            ],
            'options'=>[
                'label'=>'Loại:',
                'label_attributes'=>[
                    'for' => 'product_cat',
                    'class'=>'col-md-3 control-label'
                ],
                'value_options'=>$p_cat
            ]
        ]);
        //Unit
        $this->add([
            'type'=>'select',
            'name'=>'product_unit',
            'attributes'=>[
                'class'=>'form-control select',
                'id'=>'product_unit'
            ],
            'options'=>[
                'label'=>'Đơn vị:',
                'label_attributes'=>[
                    'for' => 'product_unit',
                    'class'=>'col-md-3 control-label'
                ],
                'value_options'=>$p_unit
            ]
        ]);

        //hinh thuc discount ban le
        $this->add([
            'type'=>'select',
            'name'=>'unit_sale_type',
            'attributes'=>[
                'class'=>'form-control select',
                'id'=>'unit_sale_type'
            ],
            'options'=>[
                'label'=>'Hình thức giảm giá:',
                'label_attributes'=>[
                    'for' => 'unit_sale_type',
                    'class'=>'col-md-3 control-label'
                ],
                'value_options'=>array("0"=>"...","percent"=>"Phần trăm","fixed"=>"Bằng tiền")
            ]
        ]);

        //discount ban le
        $this->add([
            'type'=>'number',
            'name'=>'unit_sale_value',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'unit_sale_value'
            ],
            'options'=>[
                'label'=>'Chiết khấu bán lẻ',
                'label_attributes'=>[
                    'for' => 'unit_sale_value',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //hinh thuc discount ban thung
        $this->add([
            'type'=>'select',
            'name'=>'pack_sale_type',
            'attributes'=>[
                'class'=>'form-control select',
                'id'=>'pack_sale_type'
            ],
            'options'=>[
                'label'=>'Chiết khấu bán thùng',
                'label_attributes'=>[
                    'for' => 'pack_sale_type',
                    'class'=>'col-md-3 control-label'
                ],
                'value_options'=>array("0"=>"...","percent"=>"Phần trăm","fixed"=>"Bằng tiền")
            ]
        ]);

        //discount ban thung
        $this->add([
            'type'=>'number',
            'name'=>'pack_sale_value',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'pack_sale_value'
            ],
            'options'=>[
                'label'=>'Chiết khấu bán thùng',
                'label_attributes'=>[
                    'for' => 'pack_sale_value',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'note_order',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'note_order'
            ],
            'options'=>[
                'label'=>'Ghi chú đặt hàng',
                'label_attributes'=>[
                    'for' => 'note_order',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'exchange_unit',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'exchange_unit'
            ],
            'options'=>[
                'label'=>'Mức ban lẻ tối thiểu',
                'label_attributes'=>[
                    'for' => 'exchange_unit',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'label_name',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'label_name'
            ],
            'options'=>[
                'label'=>'Tên nhãn',
                'label_attributes'=>[
                    'for' => 'label_name',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'group_id',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'group_id'
            ],
            'options'=>[
                'label'=>'Nhóm',
                'label_attributes'=>[
                    'for' => 'group_id',
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

        $this->validator();
    }

    private function validator()
    {
        $inputFilter = new InputFilter();


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
                            NotEmpty::IS_EMPTY=>'Tên sản phẩm không được để trống!'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>100,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Tên sản phẩm tối thiểu %min% ký tự',
                            StringLength::TOO_LONG=>'Tên sản phẩm không dài quá %max% ký tự'
                        ]
                    ]
                ]
            ]
        ]);
        $inputFilter->add([
            'name'=>'code',
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
                            NotEmpty::IS_EMPTY=>'Mã sản phẩm không được để trống!'
                        ]
                    ]
                ]
            ]
        ]);

        $inputFilter->add([
            'name'=>'norm',
            'required'=>false
        ]);
        $inputFilter->add([
            'name'=>'norm_input',
            'required'=>false
        ]);
        $inputFilter->add([
            'name'=>'exchange_unit',
            'required'=>false
        ]);
        $inputFilter->add([
            'name'=>'unit_sale_type',
            'required'=>false
        ]);
        $inputFilter->add([
            'name'=>'unit_sale_value',
            'required'=>false
        ]);
        $inputFilter->add([
            'name'=>'pack_sale_type',
            'required'=>false
        ]);
        $inputFilter->add([
            'name'=>'pack_sale_value',
            'required'=>false
        ]);
        $inputFilter->add([
            'name'=>'label_name',
            'required'=>false
        ]);
        $this->uploadInputFilter();
        $this->setInputFilter($inputFilter);
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