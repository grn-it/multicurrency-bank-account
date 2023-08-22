<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'global_namespace_import' => ['import_classes' => true],
        'curly_braces_position' => false,
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'single_line_throw' => false,
        'binary_operator_spaces' => ['operators' => ['=' => null]],
        'no_whitespace_in_blank_line' => false,
        'yoda_style' => false,
        'trailing_comma_in_multiline' => false,
        'nullable_type_declaration_for_default_null_value' => false
    ])
    ->setFinder($finder)
;
