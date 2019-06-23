<?php

/*
 * password-validation extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
 */

namespace Terminal42\PasswordValidationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class AddValidatorsCompilerPass implements CompilerPassInterface
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $componentManager;

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $this->container = $container;
        $this->componentManager = $container->getDefinition('terminal42_password_validation.validator_manager');

        $this->addToManager('terminal42_password_validation.validator', 'addValidator');
    }

    /**
     * @param string $tagName
     * @param string $method
     */
    private function addToManager(string $tagName, string $method): void
    {
        foreach ($this->container->findTaggedServiceIds($tagName) as $id => $tags) {
            /** @var array $tags */
            foreach ($tags as $attributes) {
                $this->componentManager->addMethodCall($method, [new Reference($id), $attributes['alias']]);
            }
        }
    }
}
