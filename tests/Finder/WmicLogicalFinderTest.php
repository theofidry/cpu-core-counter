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

use Fidry\CpuCoreCounter\Executor\ProcessExecutor;
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;
use Fidry\CpuCoreCounter\Finder\WmicLogicalFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\WmicLogicalFinder
 *
 * @internal
 */
final class WmicLogicalFinderTest extends ProcOpenBasedFinderTestCase
{
    protected function createFinder(ProcessExecutor $executor): ProcOpenBasedFinder
    {
        return new WmicLogicalFinder($executor);
    }

    public static function processResultProvider(): iterable
    {
        yield from parent::processResultProvider();

        yield 'example from the GitHub Actions machine' => [
            [
                <<<'EOF'
NumberOfLogicalProcessors  

2  
EOF
                ,
                '',
            ],
            2,
        ];

        yield 'another example from the GitHub Actions machine' => [
            [
                <<<'EOF'
NumberOfLogicalProcessors  
8  
EOF
                ,
                '',
            ],
            8,
        ];
    }
}
