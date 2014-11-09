<?php

namespace Mail\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mail\Mail\MailComposerService;

class MailComposer implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$config = $serviceLocator->get('config');
		$mailComposerService = new MailComposerService($config);
		return $mailComposerService;
	}
}

?>