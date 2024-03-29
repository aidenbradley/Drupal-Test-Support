<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/tests',
    ]);

    $ecsConfig->rules([
        NoUnusedImportsFixer::class,
    ]);

    $ecsConfig->sets([
         SetList::SPACES,
         SetList::ARRAY,
         SetList::DOCBLOCK,
         SetList::NAMESPACES,
         SetList::COMMENTS,
         SetList::PSR_12,
    ]);

    $ecsConfig->skip([
        PhpdocLineSpanFixer::class,
    ]);
};
