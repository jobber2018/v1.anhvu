<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 13:51
 */

namespace Werehouse\Form;


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

class WerehouseForm extends Form
{
    private $action;

    public function __construct($action = "add")
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'werehouse-form',
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