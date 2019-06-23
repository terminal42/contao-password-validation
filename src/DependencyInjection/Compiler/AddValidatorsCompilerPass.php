<?php


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
        $this->container        = $container;
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
