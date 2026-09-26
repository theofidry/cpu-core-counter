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
use Fidry\CpuCoreCounter\Finder\CmiCmdletPhysicalFinder;
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\CmiCmdletPhysicalFinder
 *
 * @internal
 */
final class CmiCmdletPhysicalFinderTest extends ProcOpenBasedFinderTestCase
{
    protected function createFinder(ProcessExecutor $executor): ProcOpenBasedFinder
    {
        return new CmiCmdletPhysicalFinder($executor);
    }

    public function test_it_runs_the_cmdlet_through_powershell(): void
    {
        $this->executor->setOutput(['1', '']);

        $this->createFinder($this->executor)->find();

        $command = $this->executor->getLastCommand();

        self::assertNotNull($command);
        self::assertStringStartsWith('powershell ', $command);
        self::assertStringContainsString('Win32_Processor', $command);
    }

    public static function processResultProvider(): iterable
    {
        yield from parent::processResultProvider();

        yield 'example from Windows' => [
            ["4\r\n", ''],
            4,
        ];

        yield 'example from Windows with two sockets' => [
            ["8\r\n8\r\n", ''],
            16,
        ];
    }
}
