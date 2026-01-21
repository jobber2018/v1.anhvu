<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/13/19 12:45 PM
 *
 */

namespace Sulde\Controller;

use Sulde\Service\SuldeAdminController;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
//        $translatedMessage = $this->translator()->translate('Message to be translated');
        return new ViewModel();
    }

    public function toIncludeAction()
    {
        return new ViewModel(['name' => 'Child View model']);
    }

    public function notAuthorizedAction(){
        if($this->getRequest()->getHeader('Accept')->getPrioritized()[0]->getSubType()=='json'){
            $result['status']=0;
            $result['message']='Bạn không có quyền truy cập chức năng này!';
            $result['msg']='Bạn không có quyền truy cập chức năng này!';
            return new JsonModel($result);
        }else{
            $this->flashMessenger()->addErrorMessage("You do not have the permissions to access this function!");
        }
    }
}
