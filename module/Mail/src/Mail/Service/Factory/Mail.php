<?php

namespace Mail\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mail\Mail\MailService;

class Mail implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$config = $serviceLocator->get('config');
		$mailService = new MailService($config['mail']);
		return $mailService;
	}
}

?>