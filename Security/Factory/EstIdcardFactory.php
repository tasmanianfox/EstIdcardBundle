<?php

namespace TFox\EstIdcardBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class EstIdcardFactory implements SecurityFactoryInterface 
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.est_id_card.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('est_id_card.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.est_id_card.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('est_id_card.security.authentication.listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'est_id_card';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}