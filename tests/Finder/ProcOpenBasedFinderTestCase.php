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
use const PHP_VERSION_ID;

abstract class ProcOpenBasedFinderTestCase extends TestCase
{
    /**
     * @var DummyExecutor
     */
    protected $executor;

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
        if (PHP_VERSION_ID < 70300) {
            self::markTestSkipped();
        }

        $this->executor->setOutput($output);

        $actual = $this->finder->diagnose();

        self::assertMatchesRegularExpression($expectedRegex, $actual);
    }

    public static function diagnosisProvider(): iterable
    {
        $stdoutResultRegex = '/^Executed the command ".*" and got the following \(STDOUT\) output:\nsmth in stdout$/';
        $stderrResultRegex = '/^Executed the command ".*" which wrote the following output to the STDERR:\nsmth in stderr$/';

        yield 'could not execute command' => [
            null,
            '/^Failed to execute the command ".*"\.$/',
        ];

        yield 'only written in the stdout' => [
            ['smth in stdout', ''],
            $stdoutResultRegex,
        ];

        yield 'only written in the stderr' => [
            ['', 'smth in stderr'],
            $stderrResultRegex,
        ];

        yield 'written in the stdout and stderr' => [
            ['smth in stdout', 'smth in stderr'],
            $stderrResultRegex,
        ];

        yield 'written in the stdout and stderr with stderr being blank' => [
            ['smth in stdout', ' '],
            $stdoutResultRegex,
        ];
    }

    /**
     * @dataProvider processResultProvider
     */
    public function test_it_can_count_the_number_of_cpu_cores(
        ?array $processResult,
        ?int $expected
    ): void {
        $this->executor->setOutput($processResult);

        $actual = $this->finder->find();

        self::assertSame($expected, $actual);
    }

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

        yield 'nominal' => [
            ['3', ''],
            3,
        ];

        yield 'example from linux' => [
            ["3\n", ''],
            3,
        ];

        yield 'example with extra blank lines and carriage return from linux' => [
            ["  \n  3  \n  \n", ''],
            3,
        ];

        yield 'example from windows' => [
            ["3\r\n", ''],
            3,
        ];

        yield 'example with extra blank lines and carriage return from windows' => [
            ["  \r\n  3  \r\n  \r\n", ''],
            3,
        ];

        yield 'no processor' => [
            ['0', ''],
            null,
        ];

        yield 'valid result with stderr' => [
            ['3', 'something'],
            null,
        ];

        yield 'valid result with blank stderr' => [
            ['3', ' '],
            3,
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
