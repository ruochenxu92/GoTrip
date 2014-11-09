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
        $config = $this->serviceLocator->get('config');
        $cache = $this->serviceLocator->get('cache');
        $app_version = $config['app_version'];
        if ($app_version['DEBUG']) {
            $index_serice = $this->getServiceLocator()->get('index_service');
            $index_serice->index_cache_generate($config,$cache);
        }
        $res = $cache->read_cache('index');
        if($res['result'])
            return array('html'=>$res['content']);
    }
}
