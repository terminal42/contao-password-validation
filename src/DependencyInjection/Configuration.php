<?php

declare(strict_types=1);

/*
 * Password Validation Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2021, terminal42 gmbh
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
        $treeBuilder = new TreeBuilder('terminal42_password_validation');

        // Keep compatibility with symfony/config < 4.2
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('terminal42_password_validation');
        }

        $rootNode
            ->useAttributeAsKey('entity')
            ->prototype('array')
            ->children()
                ->integerNode('min_length')->end()
                ->integerNode('max_length')->end()
                ->integerNode('invalid_attempts')
                    ->setDeprecated('The "%node%" option is not available since Contao 4.9 comes with improved brute-force-protection.')
                ->end()
                ->integerNode('nc_account_disabled')
                    ->setDeprecated('The "%node%" option is not available since Contao 4.9 comes with improved brute-force-protection.')
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
