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

        yield 'example with unknown physical core' => [
            [
                <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting usually from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2
0,0,0,0,,0,0,0
1,,1,0,,1,1,1

EOF
                ,
                ''
            ],
            1
        ];

        yield 'example with only unknown physical cores' => [
            [
                <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting usually from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2
0,,0,0,,0,0,0
1,,1,0,,1,1,1

EOF
                ,
                ''
            ],
            null
        ];

        yield 'example with a line which is not a CPU' => [
            [
                <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting usually from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2
0,0,0,0,,0,0,0
1a,1,0,0,,1,1,1

EOF
                ,
                ''
            ],
            1
        ];

        // Known limitation: lscpu numbers the cores of each CPU type from
        // zero, so this 8-core CPU is reported as 3 cores.
        // See https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/expected/lscpu/lscpu-arm-A510-A710-A715-X3
        yield 'example with several CPU types (ARM Cortex-A510/A710/A715/X3)' => [
            [
                <<<'EOF'
# The following is the parsable format, which can be fed to other
# programs. Each different item in every column has an unique ID
# starting usually from zero.
# CPU,Core,Socket,Node,,L1d,L1i,L2,L3
0,0,0,,,0,0,0,0
1,1,0,,,1,1,1,0
2,2,0,,,2,2,1,0
3,0,0,,,3,3,2,0
4,1,0,,,4,4,3,0
5,0,0,,,5,5,4,0
6,1,0,,,6,6,5,0
7,0,0,,,7,7,6,0

EOF
                ,
                ''
            ],
            3
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
