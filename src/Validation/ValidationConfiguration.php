<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

namespace Terminal42\PasswordValidationBundle\Validation;

/**
 * This class holds the configuration and parameters for validation.
 */
final class ValidationConfiguration
{
    /**
     * @var array<string, array<string, mixed>> Configuration parameters in the format [Contao\FrontendUser => [ min_chars => 8, max_chars => 20 ]]
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
