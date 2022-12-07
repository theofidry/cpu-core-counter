<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\FinderRegistry;

require_once __DIR__.'/../vendor/autoload.php';

$results = array_map(
    static function (CpuCoreFinder $finder): string {
        return implode(
            '',
            [
                $finder->toString(),
                ': ',
                null !== $finder->find() ? '.' : 'F',
            ]
        );
    },
    FinderRegistry::getAllVariants()
);

echo implode(PHP_EOL, $results).PHP_EOL;
