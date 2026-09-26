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

use Fidry\CpuCoreCounter\Executor\ProcOpenExecutor;
use PHPUnit\Framework\TestCase;
use function escapeshellarg;
use function sprintf;
use function strlen;
use const PHP_BINARY;
use const PHP_EOL;

/**
 * @covers \Fidry\CpuCoreCounter\Executor\ProcOpenExecutor
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
        $actual = $this->executor->execute($command);

        self::assertSame($expected, $actual);
    }

    public function test_it_does_not_deadlock_when_the_command_writes_more_than_the_pipe_buffer_to_the_stderr(): void
    {
        // Must be bigger than the pipe buffer, which can grow up to 1MB on Linux.
        $stderrSize = 4 * 1024 * 1024;

        // The child stops writing after a few seconds instead of blocking
        // forever, so a deadlock fails the test instead of hanging the suite.
        $script = sprintf(
            <<<'PHP'
stream_set_blocking(STDERR, false);
$remaining = %d;
$deadline = microtime(true) + 5;

while ($remaining > 0) {
    if (microtime(true) > $deadline) {
        echo "timeout";

        exit(1);
    }

    $written = (int) fwrite(STDERR, str_repeat("x", min(8192, $remaining)));
    $remaining -= $written;

    if (0 === $written) {
        usleep(1000);
    }
}

echo "ok";
PHP
            ,
            $stderrSize
        );

        $command = sprintf(
            '%s -r %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($script)
        );

        $output = $this->executor->execute($command);

        self::assertNotNull($output);

        [$stdout, $stderr] = $output;

        self::assertSame(
            'ok',
            $stdout,
            'The command could not write all its output to the STDERR because nothing was reading it.'
        );
        self::assertSame($stderrSize, strlen($stderr));
    }
}
