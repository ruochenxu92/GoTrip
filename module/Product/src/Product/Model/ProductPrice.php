<?php

namespace Product\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class ProductPrice extends AbstractTableGateway
{
	public function __construct(Adapter $adapter)
	{
		$this->table = "product_price";
		$this->adapter = $adapter;
	}
}