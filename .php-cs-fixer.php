<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/fidry/php-cs-fixer-config/src/FidryConfig.php';

use Fidry\PhpCsFixerConfig\FidryConfig;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude([
        '.build',
        '.github',
        '.phive',
        'tools',
    ]);

$config = new FidryConfig(
    <<<'EOF'
        This file is part of the Fidry CPUCounter Config package.

        (c) Théo FIDRY <theo.fidry@gmail.com>

        For the full copyright and license information, please view the LICENSE
        file that was distributed with this source code.
        EOF,
    72000,
);
$config->addRules([
    // For PHP 7.2 compat
    'get_class_to_class_keyword' => false,
    'heredoc_indentation' => false,
    'trailing_comma_in_multiline' => false,
    'use_arrow_functions' => false,

    'mb_str_functions' => false,
    'no_trailing_whitespace_in_string' => false,
]);
$config->setCacheFile(__DIR__ . '/.build/php-cs-fixer/.php-cs-fixer.cache');

return $config->setFinder($finder);
