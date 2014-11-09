<?php

namespace Product\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class ProductInfo extends AbstractTableGateway
{
	public function __construct(Adapter $adapter)
	{
		$this->table = "product_info";
		$this->adapter = $adapter;
	}
}