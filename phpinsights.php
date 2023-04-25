<?php

declare(strict_types=1);

return [
    'preset' => 'drupal',
    'ide' => 'phpstorm',
    'exclude' => [
        '*Test.php',
        '*TestBase.php',
    ],
    'add' => [],
    'config' => [
        ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff::class => [
            'maxLength' => 50,
        ],
        PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 120,
            'absoluteLineLimit' => 120
        ],
    ],
    'remove' => [
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits::class,
        ObjectCalisthenics\Sniffs\Metrics\MethodPerClassLimitSniff::class,
        ObjectCalisthenics\Sniffs\Files\ClassTraitAndInterfaceLengthSniff::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses::class,
    ],
    'requirements' => [
        'min-quality' => 80,
        'min-complexity' => 80,
        'min-architecture' => 80,
        'min-style' => 80,
        'disable-security-check' => false,
    ],
];
