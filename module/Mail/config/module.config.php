<?php
return array(
	'mail' => array(
		'enable' => true,
                'smtp_options' => array(
                    'name' => 'smtp.zoho.com',
                    'host' => 'smtp.zoho.com',
                    'port' => 465,
                    'connection_class' => 'login',
                    'connection_config' => array(
                        'username' => 'auto-reply@xiyouus.com',
                        'password' => 'Luffy0608',
                        'ssl'      => 'ssl',
                    ),
                ),
                'mail_address' => 'auto-reply@xiyouus.com',
                'display_name' => '西游迹旅行',
	),
    
	'service_manager' => array(
		'factories' => array(
			'mail_service' => 'Mail\Service\Factory\Mail',
                        'mail_composer_service' => 'Mail\Service\Factory\MailComposer',
		),
	),
);
