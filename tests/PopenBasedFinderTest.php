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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCoreCounter\PopenBasedFinder
 *
 * @internal
 */
final class PopenBasedFinderTest extends TestCase
{
    /**
     * @dataProvider popenFgetsProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $nproc,
        ?int $expected
    ): void {
        $actual = DummyPopenBasedFinder::countCpuCores($nproc);

        self::assertSame($expected, $actual);
    }

    public static function popenFgetsProvider(): iterable
    {
        yield 'empty' => [
            <<<'EOF'

EOF
            ,
            null,
        ];

        yield 'whitespace' => [
            <<<'EOF'
 
EOF
            ,
            null,
        ];

        yield 'example from a Windows machine' => [
            <<<'EOF'
3

EOF
            ,
            3,
        ];

        yield 'example from a Windows machine with extra spaces' => [
            <<<'EOF'
 3 

EOF
            ,
            3,
        ];

        yield 'no processor' => [
            <<<'EOF'
0

EOF
            ,
            null,
        ];
    }
}
