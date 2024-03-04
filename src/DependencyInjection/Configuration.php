<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('terminal42_password_validation');
        $treeBuilder
            ->getRootNode()
            ->useAttributeAsKey('entity')
            ->prototype('array')
            ->children()
                ->integerNode('min_length')->end()
                ->integerNode('max_length')->end()
                ->integerNode('invalid_attempts')
                    ->setDeprecated('terminal42/contao-password-validation', '1.0.8', 'The "%node%" option is not available since Contao 4.9 comes with improved brute-force-protection.')
                ->end()
                ->integerNode('nc_account_disabled')
                    ->setDeprecated('terminal42/contao-password-validation', '1.0.8', 'The "%node%" option is not available since Contao 4.9 comes with improved brute-force-protection.')
                ->end()
                ->integerNode('password_history')->end()
                ->integerNode('change_days')->end()
                ->integerNode('haveibeenpwned')->end()
                ->scalarNode('other_chars')->end()
                ->arrayNode('require')
                    ->children()
                        ->integerNode('uppercase')->end()
                        ->integerNode('lowercase')->end()
                        ->integerNode('numbers')->end()
                        ->integerNode('other')->end()
                    ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
