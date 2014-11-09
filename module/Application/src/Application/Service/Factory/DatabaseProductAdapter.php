<?php

namespace Application\Service\Factory;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DatabaseProductAdapter implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$config = $serviceLocator->get('config');
		$db_adapter = new Adapter($config['db_product']);
		return $db_adapter;
	}
}
