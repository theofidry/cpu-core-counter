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
use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;
use Fidry\CpuCoreCounter\Test\Executor\DummyExecutor;
use PHPUnit\Framework\TestCase;

abstract class ProcOpenBasedFinderTestCase extends TestCase
{
    /**
     * @var DummyExecutor
     */
    private $executor;

    /**
     * @var CpuCoreFinder
     */
    private $finder;

    protected function setUp(): void
    {
        $this->executor = new DummyExecutor();
        $this->finder = $this->createFinder($this->executor);
    }

    protected function tearDown(): void
    {
        unset($this->executor, $this->finder);
    }

    /**
     * @dataProvider diagnosisProvider
     */
    public function test_it_can_do_a_diagnosis(
        ?array $output,
        string $expectedRegex
    ): void {
        $this->executor->setOutput($output);

        $actual = $this->finder->diagnose();

        self::assertMatchesRegularExpression($expectedRegex, $actual);
    }

    public static function diagnosisProvider(): iterable
    {
        yield 'could not execute command' => [
            null,
            '/^Failed to execute the command ".*"\.$/',
        ];

        yield 'only written in the stdout' => [
            ['smth in stdout', ''],
            '/^Executed the command ".*" and got the following \(STDOUT\) output:\nsmth in stdout$/',
        ];

        yield 'only written in the stderr' => [
            ['', 'smth in stderr'],
            '/^Executed the command ".*" which wrote the following output to the STDERR:\nsmth in stderr$/',
        ];

        yield 'only written in the stdout and stderr' => [
            ['smth in stdout', 'smth in stderr'],
            '/^Executed the command ".*" which wrote the following output to the STDERR:\nsmth in stderr$/',
        ];
    }

    /**
     * @dataProvider processResultProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        string $processResult,
        ?int $expected
    ): void {
        $this->executor->setOutput([$processResult, '']);

        $actual = $this->finder->find();

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
            FinderShortClassName::get($this->finder),
            $this->finder->toString()
        );
    }

    abstract protected function createFinder(ProcessExecutor $executor): ProcOpenBasedFinder;
}
