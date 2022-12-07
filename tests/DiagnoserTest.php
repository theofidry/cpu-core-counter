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

use Fidry\CpuCoreCounter\Diagnoser;
use Fidry\CpuCoreCounter\Finder\CpuInfoFinder;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Fidry\CpuCoreCounter\Diagnoser
 *
 * @internal
 */
final class DiagnoserTest extends TestCase
{
    /**
     * @dataProvider diagnosisProvider
     *
     * @param list<CpuInfoFinder> $finders
     */
    public function test_it_can_run_a_diagnosis(array $finders, string $expected): void
    {
        $actual = Diagnoser::diagnose($finders);

        self::assertSame($expected, $actual);
    }

    public static function diagnosisProvider(): iterable
    {
        yield 'no finder' => [
            [],
            '',
        ];

        yield 'single finder' => [
            [new NullCpuCoreFinder()],
            <<<'EOF'
NullCpuCoreFinder:
-------------------
Will return "null".
-------------------

EOF
        ];

        yield 'multiple finders' => [
            [
                new NullCpuCoreFinder(),
                new NullCpuCoreFinder(),
            ],
            <<<'EOF'
NullCpuCoreFinder:
-------------------
Will return "null".
-------------------

NullCpuCoreFinder:
-------------------
Will return "null".
-------------------

EOF
        ];

        yield 'multiple finders with variable outputs' => [
            [
                new NullCpuCoreFinder(),
                new DummyCpuCoreFinder(1000000),
                new NullCpuCoreFinder(),
            ],
            <<<'EOF'
NullCpuCoreFinder:
-------------------
Will return "null".
-------------------

DummyCpuCoreFinder(value=1000000):
----------------------
Will return "1000000".
----------------------

NullCpuCoreFinder:
-------------------
Will return "null".
-------------------

EOF
        ];
    }

    /**
     * @dataProvider findersToExecuteProvider
     *
     * @param list<CpuInfoFinder> $finders
     */
    public function test_it_can_execute_finders(array $finders, string $expected): void
    {
        $actual = Diagnoser::execute($finders);

        self::assertSame($expected, $actual);
    }

    public static function findersToExecuteProvider(): iterable
    {
        yield 'no finder' => [
            [],
            '',
        ];

        yield 'single finder' => [
            [new NullCpuCoreFinder()],
            <<<'EOF'
NullCpuCoreFinder: NULL
EOF
        ];

        yield 'multiple finders' => [
            [
                new NullCpuCoreFinder(),
                new NullCpuCoreFinder(),
            ],
            <<<'EOF'
NullCpuCoreFinder: NULL
NullCpuCoreFinder: NULL
EOF
        ];

        yield 'multiple finders with variable outputs' => [
            [
                new NullCpuCoreFinder(),
                new DummyCpuCoreFinder(2),
                new NullCpuCoreFinder(),
            ],
            <<<'EOF'
NullCpuCoreFinder: NULL
DummyCpuCoreFinder(value=2): 2
NullCpuCoreFinder: NULL
EOF
        ];
    }
}
