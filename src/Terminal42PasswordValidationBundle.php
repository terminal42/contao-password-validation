<?php

namespace Terminal42\PasswordValidationBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Terminal42\PasswordValidationBundle\DependencyInjection\Compiler\AddValidatorsCompilerPass;

final class Terminal42PasswordValidationBundle extends Bundle
{

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new AddValidatorsCompilerPass()
        );
    }
}
