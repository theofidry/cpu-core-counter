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
use Fidry\CpuCoreCounter\Finder\ProcOpenBasedFinder;
use Fidry\CpuCoreCounter\Finder\WindowsRegistryLogicalFinder;
use function implode;
use const PHP_EOL;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\WindowsRegistryLogicalFinder
 *
 * @internal
 */
final class WindowsRegistryLogicalFinderTest extends ProcOpenBasedFinderTestCase
{
    protected function createFinder(ProcessExecutor $executor): ProcOpenBasedFinder
    {
        return new WindowsRegistryLogicalFinder($executor);
    }

    public static function processResultProvider(): iterable
    {
        yield 'empty output' => [
            [
                '',
                '',
            ],
            null,
        ];

        yield 'example from the GitHub Actions machine' => [
            [
                <<<'EOF'
HKEY_LOCAL_MACHINE\HARDWARE\DESCRIPTION\System\CentralProcessor\0
HKEY_LOCAL_MACHINE\HARDWARE\DESCRIPTION\System\CentralProcessor\1
HKEY_LOCAL_MACHINE\HARDWARE\DESCRIPTION\System\CentralProcessor\2
HKEY_LOCAL_MACHINE\HARDWARE\DESCRIPTION\System\CentralProcessor\3

EOF
                ,
                '',
            ],
            4,
        ];

        yield 'another example from the GitHub Actions machine' => [
            [
                <<<'EOF'
[HKEY_LOCAL_MACHINE\HARDWARE\DESCRIPTION\System\CentralProcessor\0]
[HKEY_LOCAL_MACHINE\HARDWARE\DESCRIPTION\System\CentralProcessor\2]

EOF
                ,
                '',
            ],
            2,
        ];

        yield 'non trimmed lines' => [
            [
                implode(
                    PHP_EOL,
                    [
                        '',
                        ' HKEY_LOCAL_MACHINE\HARDWARE\DESCRIPTION\System\CentralProcessor\0 ',
                        ' ',
                        '',
                    ]
                ),
                ''
            ],
            1,
        ];
    }
}
