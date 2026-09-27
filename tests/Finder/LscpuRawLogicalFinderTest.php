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
use Fidry\CpuCoreCounter\Finder\LscpuRawLogicalFinder;
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;
use const PHP_OS_FAMILY;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\LscpuRawLogicalFinder
 *
 * @internal
 */
final class LscpuRawLogicalFinderTest extends ProcOpenBasedFinderTestCase
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

        yield 'linux line return for stdout' => [
            ["\n", ''],
            null,
        ];

        yield 'windows line return for stdout' => [
            ["\r\n", ''],
            null,
        ];

        yield 'number only' => [
            ['3', ''],
            null,
        ];

        yield 'BSD lscpu called with an option' => [
            ['', "lscpu: illegal option -- p\nusage: lscpu [-h|--help]\n"],
            null,
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

        // See https://github.com/NanXiao/lscpu/blob/master/README.md
        yield 'BSD lscpu' => [
            [
                <<<'EOF'
Architecture:            amd64
Byte Order:              Little Endian
Total CPU(s):            8
Thread(s) per core:      2
Core(s) per socket:      4
Socket(s):               1
Vendor:                  GenuineIntel
CPU family:              6
Model:                   94
Model name:              Intel(R) Core(TM) i7-6700 CPU @ 3.40GHz
Stepping:                3
L1d cache:               32K
L1i cache:               32K
L2 cache:                256K
L3 cache:                8M
Flags:                   fpu vme de pse tsc msr

EOF
                ,
                ''
            ],
            8
        ];

        yield 'BSD lscpu with Windows line returns' => [
            [
                "Total CPU(s):            8\r\nThread(s) per core:      2\r\nCore(s) per socket:      4\r\nSocket(s):               1\r\n",
                ''
            ],
            8
        ];

        yield 'OpenBSD lscpu with inactive CPUs' => [
            [
                <<<'EOF'
Architecture:            amd64
Byte Order:              Little Endian
Active CPU(s):           4
Total CPU(s):            8
Thread(s) per core:      2
Core(s) per socket:      4
Socket(s):               1
Vendor:                  GenuineIntel

EOF
                ,
                ''
            ],
            4
        ];

        yield 'BSD lscpu on a non x86 architecture' => [
            [
                <<<'EOF'
Architecture:            aarch64
Byte Order:              Little Endian
Total CPU(s):            4
Model name:              ARM Cortex-A72 r0p3

EOF
                ,
                ''
            ],
            4
        ];

        // See https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/expected/lscpu/lscpu-x86_64-epyc_7451
        yield 'util-linux lscpu' => [
            [
                <<<'EOF'
Architecture:                    x86_64
CPU op-mode(s):                  32-bit, 64-bit
Byte Order:                      Little Endian
CPU(s):                          96
On-line CPU(s) list:             0-95
Vendor ID:                       AuthenticAMD
Model name:                      AMD EPYC 7451 24-Core Processor
CPU family:                      23
Model:                           1
Thread(s) per core:              2
Core(s) per socket:              24
Socket(s):                       2
Stepping:                        2
Frequency boost:                 enabled
CPU(s) scaling MHz:              126%
NUMA node(s):                    8

EOF
                ,
                ''
            ],
            96
        ];

        // See https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/expected/lscpu/lscpu-s390-lpar
        yield 'util-linux lscpu with offline CPUs and books' => [
            [
                <<<'EOF'
Architecture:         s390x
CPU op-mode(s):       32-bit, 64-bit
Byte Order:           Big Endian
CPU(s):               20
On-line CPU(s) list:  1-5,8-19
Off-line CPU(s) list: 0,6,7
Vendor ID:            IBM/S390
Model name:           -
Machine type:         2817
Thread(s) per core:   1
Core(s) per socket:   4
Socket(s) per book:   6
Book(s):              4

EOF
                ,
                ''
            ],
            17
        ];

        // See https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/expected/lscpu/lscpu-arm-A510-A710-A715-X3
        yield 'util-linux lscpu with several CPU types (ARM Cortex-A510/A710/A715/X3)' => [
            [
                <<<'EOF'
Architecture:                    aarch64
CPU op-mode(s):                  32-bit, 64-bit
Byte Order:                      Little Endian
CPU(s):                          8
On-line CPU(s) list:             0-7
Vendor ID:                       ARM
Model name:                      Cortex-A510
Model:                           0xd46
Thread(s) per core:              1
Core(s) per socket:              3
Socket(s):                       1
Stepping:                        r1p1
Model name:                      Cortex-A710
Model:                           0xd47
Thread(s) per core:              1
Core(s) per socket:              2
Socket(s):                       1
Stepping:                        r2p0
Model name:                      Cortex-A715
Model:                           0xd4d
Thread(s) per core:              1
Core(s) per socket:              2
Socket(s):                       1
Stepping:                        r1p0
Model name:                      Cortex-X3
Model:                           0xd4e
Thread(s) per core:              1
Core(s) per socket:              1
Socket(s):                       1
Stepping:                        r1p0

EOF
                ,
                ''
            ],
            8
        ];

        yield 'util-linux lscpu with clusters and an unknown socket count' => [
            [
                <<<'EOF'
Architecture:                            aarch64
CPU op-mode(s):                          64-bit
Byte Order:                              Little Endian
CPU(s):                                  11
On-line CPU(s) list:                     0-10
Vendor ID:                               Apple
Model name:                              -
Model:                                   0
Thread(s) per core:                      1
Core(s) per cluster:                     11
Socket(s):                               -
Cluster(s):                              1
Stepping:                                0x0

EOF
                ,
                ''
            ],
            11
        ];

        yield 'util-linux lscpu with a single online CPU' => [
            ["CPU(s):              1\nOn-line CPU(s) list: 0\n", ''],
            1
        ];

        yield 'util-linux lscpu with an invalid online CPU list' => [
            ["CPU(s):              4\nOn-line CPU(s) list: 3-0\n", ''],
            null
        ];

        yield 'util-linux lscpu with an unparsable online CPU list' => [
            ["CPU(s):              4\nOn-line CPU(s) list: 0-3,a\n", ''],
            null
        ];

        yield 'BSD lscpu with no CPU' => [
            ["Total CPU(s):            0\n", ''],
            null
        ];

        yield 'valid result with stderr' => [
            ["Total CPU(s):            8\n", 'something'],
            null
        ];

        yield 'util-linux lscpu with an online CPU list label that is not at the start of the line' => [
            ["CPU(s):              4\nX_On-line CPU(s) list: 0-3\n", ''],
            null
        ];

        yield 'util-linux lscpu with an online CPU list followed by another value' => [
            ["CPU(s):              8\nOn-line CPU(s) list: 0-3 4-7\n", ''],
            null
        ];
    }

    public function test_it_sets_the_locale_with_the_posix_shell_syntax(): void
    {
        if ('Windows' === PHP_OS_FAMILY) {
            self::markTestSkipped();
        }

        (new LscpuRawLogicalFinder($this->executor))->find();

        self::assertSame('LC_ALL=C lscpu', $this->executor->getLastCommand());
    }

    public function test_it_sets_the_locale_with_the_cmd_syntax_on_windows(): void
    {
        if ('Windows' !== PHP_OS_FAMILY) {
            self::markTestSkipped();
        }

        (new LscpuRawLogicalFinder($this->executor))->find();

        self::assertSame('set "LC_ALL=C" && lscpu', $this->executor->getLastCommand());
    }

    protected function createFinder(ProcessExecutor $executor): ProcOpenBasedFinder
    {
        return new LscpuRawLogicalFinder($executor);
    }
}
