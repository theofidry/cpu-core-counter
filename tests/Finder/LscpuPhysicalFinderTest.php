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
use Fidry\CpuCoreCounter\Finder\LscpuPhysicalFinder;
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\LscpuPhysicalFinder
 *
 * @internal
 */
final class LscpuPhysicalFinderTest extends ProcOpenBasedFinderTestCase
{
    public static function processResultProvider(): iterable
    {
        yield 'command could not be executed' => [
            null,
            null,
        ];

        yield 'empty stdout & stderr' => [
            ['', ''],
            null,
        ];

        yield 'whitespace stdout' => [
            [' ', ''],
            null,
        ];

        yield 'whitespace stderr' => [
            ['', ' '],
            null,
        ];

        yield 'whitespace stdout & stderr' => [
            [' ', ' '],
            null,
        ];

        yield 'linux line return for stdout' => [
            ["\n", ''],
            null,
        ];

        yield 'linux line return for stderr' => [
            ['', "\n"],
            null,
        ];

        yield 'linux line return for stdout & stderr' => [
            ["\n", "\n"],
            null,
        ];

        yield 'windows line return for stdout' => [
            ["\r\n", ''],
            null,
        ];

        yield 'windows line return for stderr' => [
            ['', "\r\n"],
            null,
        ];

        yield 'windows line return for stdout & stderr' => [
            ["\r\n", "\r\n"],
            null,
        ];

        yield 'no processor' => [
            ['0', ''],
            null,
        ];

        yield 'valid result with stderr' => [
            ['3', 'something'],
            null,
        ];

        yield 'example with four logical but two physical' => [
            [
                <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2,L3
0,0,0,0,,0,0,0,0
1,1,0,0,,1,1,1,0
2,0,0,0,,0,0,0,0
3,1,0,0,,1,1,1,0

EOF
                ,
                ''
            ],
            2
        ];

        yield 'example with two cores' => [
            [
                <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2
0,0,0,0,,0,0,0
1,1,0,0,,1,1,1

EOF
                ,
                ''
            ],
            2
        ];

        yield 'example with unrecognized physical core' => [
            [
                <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2
0,0,0,0,,0,0,0
1,-,0,0,,1,1,1

EOF
                ,
                ''
            ],
            1
        ];

        yield 'handling lscpu failure' => [
            [
                '',
                <<<'EOF'
lscpu: failed to determine number of CPUs: /sys/devices/system/cpu/possible: No such file or directory

EOF
            ],
            null
        ];
    }

    protected function createFinder(ProcessExecutor $executor): ProcOpenBasedFinder
    {
        return new LscpuPhysicalFinder($executor);
    }
}
