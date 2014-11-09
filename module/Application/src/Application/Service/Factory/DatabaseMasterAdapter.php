<?php

namespace Application\Service\Factory;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DatabaseMasterAdapter implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$config = $serviceLocator->get('config');
		$db_adapter = new Adapter($config['db_master']);
		return $db_adapter;
	}
}
