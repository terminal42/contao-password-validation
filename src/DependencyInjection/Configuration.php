<?php

declare(strict_types=1);

/*
 * Password Validation Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2019, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
 */

namespace Terminal42\PasswordValidationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('terminal42_password_validation');

        $rootNode
            ->useAttributeAsKey('entity')
            ->prototype('array')
            ->children()
                ->integerNode('min_length')->end()
                ->integerNode('max_length')->end()
                ->integerNode('invalid_attempts')->end()
                ->integerNode('nc_account_disabled')->end()
                ->integerNode('password_history')->end()
                ->integerNode('change_days')->end()
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
