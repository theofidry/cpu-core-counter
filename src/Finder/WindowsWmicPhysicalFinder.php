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

use function defined;

/**
 * Find the number of physical CPU cores for Windows.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L912-L916
 */
final class WindowsWmicPhysicalFinder extends PopenBasedFinder
{
    /**
     * @return positive-int|null
     */
    public function find(): ?int
    {
        if (!defined('PHP_WINDOWS_VERSION_MAJOR')) {
            // Skip if not on Windows. Rely on PHP to detect the platform
            // rather than reading the platform name or others.
            return null;
        }

        return parent::find();
    }

    protected function getCommand(): string
    {
        return 'wmic cpu get NumberOfProcessors';
    }
}
