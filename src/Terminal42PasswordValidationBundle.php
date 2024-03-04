<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class Terminal42PasswordValidationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
