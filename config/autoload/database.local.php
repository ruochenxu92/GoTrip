<?php
return array(
	'db_master' => array(
		'driver' => 'mysqli',
		'database' => 'gotrip',    //TODO need to change to real db name
		'username' => 'gotrip_master',
		'password' => '123456',
		'hostname' => 'localhost',
		'charset' => 'utf8',
		'options' => array(
			'buffer_results' => 1,
		),
	),
	'db_product' => array(
		'driver' => 'mysqli',
		'database' => 'gotrip',
		'username' => 'gotrip_product', //TODO probably need to change
		'password' => '123456',
		'hostname' => 'localhost',
		'charset' => 'utf8',
		'options' => array(
			'buffer_results' => 1,
		),
	),
);