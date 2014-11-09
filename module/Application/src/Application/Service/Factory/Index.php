<?php

namespace Application\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Db\IndexService;

class Index implements FactoryInterface {

    public function createService(ServiceLocatorInterface $serviceLocator) {
        $index_service = new IndexService($serviceLocator);
        return $index_service;
    }
}

?>