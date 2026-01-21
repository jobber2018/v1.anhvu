<?php
namespace Users\Form;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;

class LoginForm extends Form{
    public function __construct(){
        parent::__construct();
        
        $this->loginForm(); //định nghĩa form
        $this->loginInputFilter(); //định nghĩa cho filter+validate
    }

    //create textfield
    private function loginForm(){
        //mobile
        $mobile = new Element\Text('mobile');
        /*$mobile->setLabel('Mobile: ')
            ->setLabelAttributes([
                'for' => 'mobile',
                'class' => 'col-sm-3 control-label'
            ]);*/
        $mobile->setAttributes([
            'id'=>'mobile',
            'class'=>'form-control',
            'placeholder' => 'Enter mobile or email.'
        ]);
        $this->add($mobile);

        //password
        $pw = new Element\Password('password');
        /*$pw->setLabel('Password:')
            ->setLabelAttributes([
                'for' =>'password',
                'class'=>'col-sm-3 control-label'
            ]);*/
        $pw->setAttributes([
            'id'=>'password',
            'class'=>'form-control',
            'placeholder'=>'Enter your pass.'
        ]);
        $this->add($pw);

        //remember
        $remember_me = new Element\Checkbox('remember');
        $remember_me->setLabel('Remember me')
                    ->setLabelAttributes([
                        'for'=>'remember',
                        'class'=>'btn-link remember'
                    ]);
        $remember_me->setAttributes([
            'id'=>'remember',
            'value'=>1,
            'required'=>false
        ]);
        $this->add($remember_me);

        //submit
        $submit = new Element\Submit('submit');
        $submit->setAttributes([
            'value'=>'Login',
            'class'=>'btn btn-success'
        ]);
        $this->add($submit);


    }

    //create inputfilter
    private function loginInputFilter(){
        $inputFilter = new InputFilter\InputFilter();
        $this->setInputFilter($inputFilter);

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
                            NotEmpty::IS_EMPTY=>'Mobile not empty'
                        ]
                    ]
                ]
            ]
        ]);

        //pw
        $inputFilter->add([
            'name'=>'password',
            'required'=>true,
            'filters'=>[
                //trim/newline/tolower/toupper
//                ['name'=>'StringToLower'],
//                ['name'=>'StringTrim'],
//                ['name'=>'StripTags'],
//                ['name'=>'StripNewlines']
            ],
            'validators'=>[
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>50,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Mật khẩu ít nhất %min% kí tự',
                            StringLength::TOO_LONG=>'Mật khẩu không quá %max% kí tự'
                        ]
                    ]
                ]
            ]
        ]);

        //remember me
        $inputFilter->add([
            'name'=>'remember',
            'required'=>false,
            'validators'=>[
                [
                    'name'=>'InArray',
                    'options'=>[
                        'haystack'=>[0,1],
                        'messages'=>[
                            InArray::NOT_IN_ARRAY=>'Dữ liệu không hợp lệ',
                           ]
                    ]
                ]
            ]
        ]);

    }
}
?>