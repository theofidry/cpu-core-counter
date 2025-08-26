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
use Fidry\CpuCoreCounter\Finder\CmiCmdletLogicalFinder;
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\CmiCmdletLogicalFinder
 *
 * @internal
 */
final class CmiCmdletLogicalFinderTest extends ProcOpenBasedFinderTestCase
{
    protected function createFinder(ProcessExecutor $executor): ProcOpenBasedFinder
    {
        return new CmiCmdletLogicalFinder($executor);
    }

    public static function processResultProvider(): iterable
    {
        yield from parent::processResultProvider();

        yield 'example #1' => [
            [
                <<<'EOF'
NumberOfLogicalProcessors
-------------------------
8

EOF
                ,
                '',
            ],
            8,
        ];

        yield 'example #1 without empty line return' => [
            [
                <<<'EOF'
NumberOfLogicalProcessors
-------------------------
8
EOF
                ,
                '',
            ],
            8,
        ];
    }
}
