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

namespace Fidry\CpuCoreCounter\Test\Finder;

use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;
use Fidry\CpuCoreCounter\Finder\WmicPhysicalFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\WmicPhysicalFinder
 *
 * @internal
 */
final class WmicPhysicalFinderTest extends ProcOpenBasedFinderTestCase
{
    protected function getFinder(): ProcOpenBasedFinder
    {
        return new WmicPhysicalFinder();
    }

    public static function processResultProvider(): iterable
    {
        yield from parent::processResultProvider();

        yield 'example from the GitHub Actions machine' => [
            <<<'EOF'
NumberOfCores  

2  
EOF
            ,
            2,
        ];
    }
}
