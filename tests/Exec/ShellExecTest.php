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

namespace Fidry\CpuCoreCounter\Test\Exec;

use Fidry\CpuCoreCounter\Exec\ExecException;
use Fidry\CpuCoreCounter\Exec\ShellExec;
use PHPUnit\Framework\TestCase;
use const PHP_EOL;

/**
 * @covers \Fidry\CpuCoreCounter\Exec\ExecException
 * @covers \Fidry\CpuCoreCounter\Exec\ShellExec
 *
 * @internal
 */
final class ShellExecTest extends TestCase
{
    public function test_it_can_execute_a_command_writing_output_to_the_stdout(): void
    {
        $command = 'echo "Hello world!"';

        $expected = 'Hello world!'.PHP_EOL;
        $actual = ShellExec::execute($command);

        self::assertSame($expected, $actual);
    }

    public function test_it_can_execute_a_command_writing_output_to_the_stderr_instead_of_the_stdout(): void
    {
        self::markTestSkipped('This is messing up with the output.');
        $command = 'echo "Hello world!" 1>&2';

        $this->expectException(ExecException::class);
        $this->expectExceptionMessage('The command "echo "Hello world!" 1>&2" exited without writing to the STDOUT.');

        ShellExec::execute($command);
    }

    public function test_it_can_execute_a_command_writing_output_to_the_stdout_instead_of_the_stderr(): void
    {
        $command = 'echoerr() { echo "$@" 1>&2; }; echoerr "Hello world!" 2>&1';

        $expected = 'Hello world!'.PHP_EOL;
        $actual = ShellExec::execute($command);

        self::assertSame($expected, $actual);
    }
}
