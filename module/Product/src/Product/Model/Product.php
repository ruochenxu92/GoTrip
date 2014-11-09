<?php

namespace Product\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class Product extends AbstractTableGateway
{
	public function __construct(Adapter $adapter)
	{
		$this->table = "product";
		$this->adapter = $adapter;
	}
}