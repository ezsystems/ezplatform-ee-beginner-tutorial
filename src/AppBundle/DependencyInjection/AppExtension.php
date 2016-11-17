<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Resource\FileResource;

class AppExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $configFile = __DIR__ . '/../Resources/config/blocks.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ez_systems_landing_page_field_type', $config);
        $container->addResource(new FileResource($configFile));
    }
}
