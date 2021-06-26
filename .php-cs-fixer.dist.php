<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'strict_param' => false,
        'phpdoc_align' => false,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'yoda_style' => false,
        'phpdoc_separation' => false,
        'concat_space' => ['spacing' => 'one'],
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_order' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'class_attributes_separation' => true,
    ])
    ->setFinder($finder);
