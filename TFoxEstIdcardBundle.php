<?php
namespace TFox\EstIdcardBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TFox\EstIdcardBundle\Security\Factory\EstIdcardFactory;

class TFoxEstIdcardBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
	
		$extension = $container->getExtension('security');
		$extension->addSecurityListenerFactory(new EstIdcardFactory());
	}
}
