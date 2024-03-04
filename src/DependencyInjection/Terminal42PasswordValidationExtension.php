<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class Terminal42PasswordValidationExtension extends Extension
{
    private static array $files = [
        'listeners.yml',
        'services.yml',
        'validators.yml',
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));

        foreach (self::$files as $file) {
            $loader->load($file);
        }

        $definition = $container->getDefinition('terminal42_password_validation.validator_configuration');

        foreach ($config as $key => $value) {
            $definition->addMethodCall('addConfiguration', [$key, $value]);
        }
    }
}
