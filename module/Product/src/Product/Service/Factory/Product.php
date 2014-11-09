<?php

namespace Product\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Product\Db\ProductService;

class Product implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$db_master_adapter = $serviceLocator->get('database_master_adapter');
		$db_slave_adapter = $serviceLocator->get('database_product_adapter');
		$product_service = new ProductService($db_master_adapter, $db_slave_adapter, $serviceLocator);
		return $product_service;
	}
}

?>