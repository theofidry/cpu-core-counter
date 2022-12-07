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

use Fidry\CpuCoreCounter\Finder\CpuInfoFinder;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\HwLogicalFinder;
use Fidry\CpuCoreCounter\Finder\HwPhysicalFinder;
use Fidry\CpuCoreCounter\Finder\NProcFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\WindowsWmicLogicalFinder;
use Fidry\CpuCoreCounter\Finder\WindowsWmicPhysicalFinder;
use Fidry\CpuCoreCounter\Test\Finder\LabeledFinder;

require_once __DIR__.'/../vendor/autoload.php';

$finders = [
    new LabeledFinder(new CpuInfoFinder()),
    new LabeledFinder(new DummyCpuCoreFinder(11)),
    new LabeledFinder(new HwLogicalFinder()),
    new LabeledFinder(new HwPhysicalFinder()),
    new LabeledFinder(new NProcFinder(), 'NProcFinder{all=true}'),
    new LabeledFinder(new NProcFinder(false), 'NProcFinder{all=false}'),
    new LabeledFinder(new NullCpuCoreFinder()),
    new LabeledFinder(new WindowsWmicLogicalFinder()),
    new LabeledFinder(new WindowsWmicPhysicalFinder()),
];

$results = array_map(
    static function (LabeledFinder $finder): string {
        return implode(
            '',
            [
                $finder->getLabel(),
                ': ',
                null !== $finder->find() ? '.' : 'F',
            ]
        );
    },
    $finders
);

echo implode(PHP_EOL, $results).PHP_EOL;
