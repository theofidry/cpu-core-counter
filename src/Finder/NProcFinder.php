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

namespace Fidry\CpuCoreCounter\Finder;

use Fidry\CpuCoreCounter\Exec\ExecException;
use Fidry\CpuCoreCounter\Exec\ShellExec;
use function filter_var;
use function function_exists;
use function is_int;
use function sprintf;
use function strrpos;
use function substr;
use function trim;
use const FILTER_VALIDATE_INT;
use const PHP_EOL;

/**
 * The number of (logical) cores.
 *
 * @see https://github.com/infection/infection/blob/fbd8c44/src/Resource/Processor/CpuCoresCountProvider.php#L69-L82
 * @see https://unix.stackexchange.com/questions/146051/number-of-processors-in-proc-cpuinfo
 */
final class NProcFinder implements CpuCoreFinder
{
    private const DETECT_NPROC_COMMAND = 'command -v nproc';

    /**
     * @var bool
     */
    private $all;

    /**
     * @param bool $all If disabled will give the number of cores available for the current process only.
     */
    public function __construct(bool $all = true)
    {
        $this->all = $all;
    }

    public function diagnose(): string
    {
        if (!function_exists('shell_exec')) {
            return 'The function "shell_exec" is not available.';
        }

        try {
            $commandNproc = ShellExec::execute(self::DETECT_NPROC_COMMAND);
        } catch (ExecException $nprocCommandNotFound) {
            return sprintf(
                'The command nproc was not detected. The command "%s" failed: %s',
                self::DETECT_NPROC_COMMAND,
                $nprocCommandNotFound->getMessage()
            );
        }

        if ('' === trim($commandNproc)) {
            return sprintf(
                'The command nproc was not detected. The command "%s" gave an empty output.',
                self::DETECT_NPROC_COMMAND
            );
        }

        $nprocCommand = 'nproc'.($this->all ? ' --all' : '');

        try {
            // TODO: clarify what happens with the STDERR here
            $nproc = ShellExec::execute($nprocCommand);
        } catch (ExecException $nprocFailed) {
            return sprintf(
                'The command "%s" failed: %s',
                $nprocCommand,
                $nprocFailed->getMessage()
            );
        }

        return sprintf(
            'The command "%s" gave the following output:%s%s',
            $nprocCommand,
            PHP_EOL,
            $nproc
        );
    }

    /**
     * @return positive-int|null
     */
    public function find(): ?int
    {
        if (!self::supportsNproc()) {
            return null;
        }

        try {
            $nproc = ShellExec::execute('nproc'.($this->all ? ' --all' : ''));
        } catch (ExecException $nprocFailed) {
            return null;
        }

        return self::countCpuCores($nproc);
    }

    public function toString(): string
    {
        return sprintf(
            '%s(all=%s)',
            /** @phpstan-ignore-next-line */
            substr(__CLASS__, strrpos(__CLASS__, '\\') + 1),
            $this->all ? 'true' : 'false'
        );
    }

    private static function supportsNproc(): bool
    {
        if (!function_exists('shell_exec')) {
            return false;
        }

        try {
            $commandNproc = ShellExec::execute(self::DETECT_NPROC_COMMAND);
        } catch (ExecException $nprocCommandNotFound) {
            return false;
        }

        return '' !== trim($commandNproc);
    }

    /**
     * @return positive-int|null
     */
    public static function countCpuCores(string $nproc): ?int
    {
        $cpuCount = filter_var($nproc, FILTER_VALIDATE_INT);

        return is_int($cpuCount) && $cpuCount > 0 ? $cpuCount : null;
    }
}
