<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/src']);
    $ecsConfig->import(__DIR__.'/vendor/contao/easy-coding-standard/config/contao.php');

    $services = $ecsConfig->services();

    $services
        ->set(HeaderCommentFixer::class)
        ->call('configure', [[
            'header' => "This file is part of terminal42/contao-password-validation.\n\n(c) terminal42 gmbh <https://terminal42.ch>\n\n@license MIT",
        ]]);
};
