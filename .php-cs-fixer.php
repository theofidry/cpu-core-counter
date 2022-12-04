<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/theofidry/php-cs-fixer-config/src/FidryConfig.php';

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
    74_000,
);
$config->addRules(['mb_str_functions' => false]);
$config->setCacheFile(__DIR__ . '/.build/php-cs-fixer/.php-cs-fixer.cache');

return $config->setFinder($finder);
