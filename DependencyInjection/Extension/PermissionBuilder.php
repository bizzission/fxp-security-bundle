<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\DependencyInjection\Extension;

use Sonatra\Component\Security\Permission\PermissionConfig;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionBuilder implements ExtensionBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        $loader->load('permission.xml');
        $configs = array();

        foreach ($config['permissions'] as $type => $permConfig) {
            if ($permConfig['enabled']) {
                $configs[] = $this->buildPermissionConfig($container, $type, $permConfig);
            }
        }

        $container->getDefinition('sonatra_security.permission_manager')->replaceArgument(4, $configs);
        BuilderUtils::loadProvider($loader, $config, 'permission');
    }

    /**
     * Build the permission config.
     *
     * @param ContainerBuilder $container The container
     * @param string           $type      The type of permission
     * @param array            $config    The config of permissions
     *
     * @return Reference
     */
    private function buildPermissionConfig(ContainerBuilder $container, $type, array $config)
    {
        if (!class_exists($type)) {
            $msg = 'The "%s" permission class does not exist';
            throw new InvalidConfigurationException(sprintf($msg, $type));
        }

        $def = new Definition(PermissionConfig::class, array(
            $type,
            $this->buildPermissionConfigFields($type, $config),
            $config['master'],
        ));
        $def->setPublic(false);

        $id = 'sonatra_security.permission_config.'.strtolower(str_replace('\\', '_', $type));
        $container->setDefinition($id, $def);

        return new Reference($id);
    }

    /**
     * Build the fields of permission config.
     *
     * @param string $type   The type of permission
     * @param array  $config The config of permissions
     *
     * @return string[]
     */
    private function buildPermissionConfigFields($type, array $config)
    {
        $fields = array();
        $ref = new \ReflectionClass($type);

        if ($config['build_fields'] && 0 === count($config['fields'])) {
            foreach ($ref->getProperties() as $property) {
                $fields[] = $property->getName();
            }
        } else {
            foreach ($config['fields'] as $field) {
                if (!$ref->hasProperty($field)) {
                    $msg = 'The permission field "%s" does not exist in "%s" class';

                    throw new InvalidConfigurationException(sprintf($msg, $field, $type));
                }

                $fields[] = $field;
            }
        }

        return $fields;
    }
}