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

namespace Terminal42\PasswordValidationBundle\Validation;

/**
 * This class holds the configuration and parameters for validation.
 */
final class ValidationConfiguration
{
    /**
     * @var array Configuration parameters in the format [Contao\FrontendUser => [ min_chars => 8, max_chars => 20 ]]
     */
    private $configurations = [];

    public function addConfiguration(string $entityName, array $configuration): void
    {
        $this->configurations[$entityName] = $configuration;
    }

    public function hasConfiguration(string $entityName): bool
    {
        return \array_key_exists($entityName, $this->configurations);
    }

    public function getConfiguration(string $entityName): ?array
    {
        return $this->configurations[$entityName];
    }
}
