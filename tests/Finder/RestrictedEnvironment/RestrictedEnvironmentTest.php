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

namespace Fidry\CpuCoreCounter\Test\Finder\RestrictedEnvironment;

use PHPUnit\Framework\TestCase;
use function escapeshellarg;
use function exec;
use function implode;
use function sprintf;
use function sys_get_temp_dir;
use const PATH_SEPARATOR;
use const PHP_BINARY;
use const PHP_EOL;

/**
 * @coversNothing
 *
 * @internal
 */
final class RestrictedEnvironmentTest extends TestCase
{
    /**
     * @dataProvider iniSettingsProvider
     */
    public function test_the_finders_do_not_fail_in_a_restricted_environment(
        string $iniName,
        string $iniValue
    ): void {
        $command = sprintf(
            '%s -d %s %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($iniName.'='.$iniValue),
            escapeshellarg(__DIR__.'/execute-all-finders.php')
        );

        exec($command, $output, $exitCode);

        self::assertSame('', implode(PHP_EOL, $output));
        self::assertSame(0, $exitCode);
    }

    public static function iniSettingsProvider(): iterable
    {
        $disabledFunctions = [
            'fclose',
            'file_get_contents',
            'getenv',
            'is_file',
            'proc_close',
            'proc_open',
            'rewind',
            'stream_get_contents',
            'tmpfile',
        ];

        foreach ($disabledFunctions as $function) {
            yield 'disabled function: '.$function => [
                'disable_functions',
                $function,
            ];
        }

        yield 'open_basedir' => [
            'open_basedir',
            __DIR__.'/../../..'.PATH_SEPARATOR.sys_get_temp_dir(),
        ];
    }
}
