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

namespace Fidry\CpuCoreCounter\Test;

use Fidry\CpuCoreCounter\HwFinder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCoreCounter\HwFinder
 *
 * @internal
 */
final class HwFinderTest extends TestCase
{
    /**
     * @dataProvider processProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $process,
        ?int $expected
    ): void {
        $actual = HwFinder::countCpuCores($process);

        self::assertSame($expected, $actual);
    }

    public static function processProvider(): iterable
    {
        yield 'empty' => [
            <<<'EOF'

EOF,
            null,
        ];

        yield 'whitespace' => [
            <<<'EOF'
 
EOF,
            null,
        ];

        // MyMachine™
        yield 'example from an OSX machine' => [
            <<<'EOF'
3

EOF,
            3,
        ];
        yield 'example with extra spaces' => [
            <<<'EOF'
 3 

EOF,
            3,
        ];

        yield 'no processor' => [
            <<<'EOF'
0

EOF,
            null,
        ];
    }
}
