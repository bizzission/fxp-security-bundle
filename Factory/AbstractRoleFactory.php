<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Abstract factory for role injection in security identity manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractRoleFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = $this->getServiceId('provider').'.'.$id;
        $container
            ->setDefinition($providerId, new ChildDefinition($this->getServiceId('provider')))
        ;

        $listenerId = $this->getServiceId('listener').'.'.$id;
        $container
            ->setDefinition($listenerId, new ChildDefinition($this->getServiceId('listener')))
            ->replaceArgument(1, $config)
        ;

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * Get the service id.
     *
     * @param string $type The type
     *
     * @return string
     */
    protected function getServiceId($type)
    {
        return sprintf('sonatra_security.authentication.%s.%s', $type, $this->getKey());
    }
}
