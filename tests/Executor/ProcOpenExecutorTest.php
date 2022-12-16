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

namespace Fidry\CpuCoreCounter\Test\Executor;

use Fidry\CpuCoreCounter\Executor\ProcOpen;
<<<<<<<< HEAD:tests/Executor/ProcOpenExecutorTest.php
use Fidry\CpuCoreCounter\Executor\ProcOpenExecutor;
========
>>>>>>>> upstream/main:tests/Executor/ProcOpenTest.php
use PHPUnit\Framework\TestCase;
use const PHP_EOL;

/**
<<<<<<<< HEAD:tests/Executor/ProcOpenExecutorTest.php
 * @covers \Fidry\CpuCoreCounter\Executor\ProcOpenExecutor
========
 * @covers \Fidry\CpuCoreCounter\Executor\ProcOpen
>>>>>>>> upstream/main:tests/Executor/ProcOpenTest.php
 *
 * @internal
 */
final class ProcOpenExecutorTest extends TestCase
{
    /**
     * @var ProcOpenExecutor
     */
    private $executor;

    protected function setUp(): void
    {
        $this->executor = new ProcOpenExecutor();
    }
    
    protected function tearDown(): void
    {
        unset($this->executor);
    }

    public function test_it_can_execute_a_command_writing_output_to_the_stdout(): void
    {
        $command = 'echo "Hello world!"';

        $expected = ['Hello world!'.PHP_EOL, ''];
        $actual = $this->executor->execute($command);

        self::assertSame($expected, $actual);
    }

    public function test_it_can_execute_a_command_writing_output_to_the_stderr_instead_of_the_stdout(): void
    {
        $command = 'echo "Hello world!" 1>&2';

        $expected = ['', 'Hello world!'.PHP_EOL];
        $actual = $this->executor->execute($command);

        self::assertSame($expected, $actual);
    }

    public function test_it_can_execute_a_command_writing_output_to_the_stdout_instead_of_the_stderr(): void
    {
        $command = 'echoerr() { echo "$@" 1>&2; }; echoerr "Hello world!" 2>&1';

        $expected = ['Hello world!'.PHP_EOL, ''];
<<<<<<<< HEAD:tests/Executor/ProcOpenExecutorTest.php
        $actual = $this->executor->execute($command);
========
        $actual = \Fidry\CpuCoreCounter\Executor\ProcOpen::execute($command);
>>>>>>>> upstream/main:tests/Executor/ProcOpenTest.php

        self::assertSame($expected, $actual);
    }
}
