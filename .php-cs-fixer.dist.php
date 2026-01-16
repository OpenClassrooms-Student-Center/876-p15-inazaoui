<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude([
        'var',
        'vendor',
    ]);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,

        // Typage et syntaxe moderne
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'strict_param' => true,

        // Imports
        'ordered_imports' => true,
        'no_unused_imports' => true,

        // Lisibilité
        'single_quote' => true,
        'no_extra_blank_lines' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
    ])
    ->setFinder($finder);
