<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 13:51
 */

namespace Sell\Form;


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

class SellForm extends Form
{
    private $action;

    public function __construct($action = "add")
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'sell-form',
            'class'=>'form-horizontal'
        ]);
        $this->action = $action;
        $this->addElements();
        $this->validator();
    }


    private function addElements()
    {
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
    }
}