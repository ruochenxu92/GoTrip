<?php

namespace Product\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class ProductAvailable extends AbstractTableGateway
{
	public function __construct(Adapter $adapter)
	{
		$this->table = "product_available";
		$this->adapter = $adapter;
	}
}