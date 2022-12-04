<?php

declare(strict_types=1);

namespace Fidry\CpuCounter\Exec;

use Fidry\CpuCounter\Exec\ExecException;
use Fidry\CpuCounter\Exec\ExecException;
use function shell_exec;

/**
 * Mimicks Safe\shell_exec: this is to avoid to add an extra dependency to this
 * micro package.
 *
 * @see https://github.com/thecodingmachine/safe/blob/0653752f6c2d45e0640fa24bf789cae367a501d3/generated/exec.php#L114
 */
final class ShellExec
{
    private function __construct()
    {
    }

    /**
     * @throws ExecException
     */
    public static function execute(string $command): string
    {
        error_clear_last();

        $safeResult = shell_exec($command);

        if ($safeResult === null || $safeResult === false) {
            throw ExecException::createFromPhpError();
        }

        return $safeResult;
    }
}
