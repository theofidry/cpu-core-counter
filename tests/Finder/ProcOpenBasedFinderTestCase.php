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
use PHPUnit\Framework\TestCase;

abstract class ProcOpenBasedFinderTestCase extends TestCase
{
    /**
     * @dataProvider processResultProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $processResult,
        ?int $expected
    ): void {
        $actual = $this->getFinder()::countCpuCores($processResult);

        self::assertSame($expected, $actual);
    }

    public static function processResultProvider(): iterable
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

    public function test_it_can_describe_itself(): void
    {
        self::assertSame(
            FinderShortClassName::get($this->getFinder()),
            $this->getFinder()->toString()
        );
    }

    abstract protected function getFinder(): ProcOpenBasedFinder;
}
