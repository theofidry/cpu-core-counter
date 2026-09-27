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

use function function_exists;
use function getenv;
use function sprintf;

/**
 * @deprecated Use OnlyOnOSFamilyFinder::forWindows() instead. The PSModulePath
 *             environment variable is set system-wide on Windows and is not a
 *             sign that commands run in PowerShell.
 */
final class OnlyInPowerShellFinder implements CpuCoreFinder
{
    /**
     * @var CpuCoreFinder
     */
    private $decoratedFinder;

    public function __construct(CpuCoreFinder $decoratedFinder)
    {
        $this->decoratedFinder = $decoratedFinder;
    }

    public function diagnose(): string
    {
        $powerShellModulePath = self::getEnv('PSModulePath');

        return $this->skip()
            ? sprintf(
                'Skipped; no power shell module path detected ("%s").',
                $powerShellModulePath
            )
            : $this->decoratedFinder->diagnose();
    }

    public function find(): ?int
    {
        return $this->skip()
            ? null
            : $this->decoratedFinder->find();
    }

    public function toString(): string
    {
        return sprintf(
            'OnlyInPowerShellFinder(%s)',
            $this->decoratedFinder->toString()
        );
    }

    private function skip(): bool
    {
        return false === self::getEnv('PSModulePath');
    }

    /**
     * @return string|false
     */
    private static function getEnv(string $name)
    {
        // The function may be disabled, e.g. with disable_functions.
        return function_exists('getenv') ? getenv($name) : false;
    }
}
