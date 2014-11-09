<?php
return array(
	'controllers' => array(
		'invokables' => array(
			'Product\Controller\Query' => 'Product\Controller\QueryController',
		),
	),
	'router' => array(
		'routes' => array(
			'product' => array(
				'type'	=> 'Literal',
				'options' => array(
					// Change this to something specific to your module
					'route'	=> '/product',
					'defaults' => array(
						// Change this value to reflect the namespace in which
						// the controllers for your module are found
						'__NAMESPACE__' => 'Product\Controller',
						'controller'	=> 'Query',
						'action'		=> 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					// This route is a sane default when developing a module;
					// as you solidify the routes for your module, however,
					// you may want to remove it and replace it with more
					// specific routes.
					'default' => array(
						'type'	=> 'Segment',
						'options' => array(
							'route'	=> '/:controller/:action/[:id]',
							'constraints' => array(
								'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action'	 => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id' 		 => '[0-9]*',
							),
							'defaults' => array(
							),
						),
					),
				),
			),
		),
	),
	'view_manager' => array(
		'template_path_stack' => array(
			'Product' => __DIR__ . '/../view',
		),
		'strategies' => array(
			'ViewJsonStrategy',
		),
	),
	'service_manager' => array(
		'factories' => array(
			'product_service' => 'Product\Service\Factory\Product',
                        'product_schedule_service' => 'Product\Service\Factory\ProductSchedule',
		)
	),
);
