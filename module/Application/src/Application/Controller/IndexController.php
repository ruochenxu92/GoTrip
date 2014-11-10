<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {

    public function indexAction() {
        $this->layout()->setVariable('service_locator', $this->getServiceLocator());
        $index_service = $this->getServiceLocator()->get('index_service');
        $res = $index_service->read();
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setTemplate('layout/layout');
        $view->setVariables(array('content' => $res['content'], 'service_locator'=>$this->getServiceLocator()));
        return $view;

    }
}
