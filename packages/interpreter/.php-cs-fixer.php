<?php

/**
 * Configuration PHP-CS-Fixer pour le projet Interpreteur Jeedom
 * 
 * Applique les standards PSR-12 avec quelques règles additionnelles
 * pour améliorer la lisibilité et la maintenabilité du code.
 */

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude('vendor')
    ->exclude('coverage')
    ->exclude('logs')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        // PSR-12 comme base
        '@PSR12' => true,
        
        // Règles additionnelles pour améliorer la qualité
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'single_trait_insert_per_statement' => true,
        
        // Amélioration des commentaires et documentation
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_annotation_correct_order' => true,
        
        // Amélioration de la structure du code
        'no_leading_import_slash' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_superfluous_phpdoc_tags' => false, // Garde les tags même redondants
        'no_whitespace_before_comma_in_array' => true,
        'normalize_index_brace' => true,
        'object_operator_without_whitespace' => true,
        'phpdoc_no_alias_tag' => ['replacements' => ['type' => 'var', 'link' => 'see']],
        
        // Espacement et formatage
        'concat_space' => ['spacing' => 'one'],
        'function_typehint_space' => true,
        'no_spaces_around_offset' => true,
        'ternary_operator_spaces' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => true,
        
        // Structures de contrôle
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        
        // Règles strictes PHP 7.4+
        'strict_param' => false, // Pas de strict_types automatique, on le gère manuellement
        'declare_strict_types' => false, // Idem
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(false)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');