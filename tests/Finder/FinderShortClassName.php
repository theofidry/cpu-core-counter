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

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use function get_class;
use function strrpos;
use function substr;

final class FinderShortClassName
{
    public static function get(CpuCoreFinder $finder): string
    {
        $class = get_class($finder);

        return substr($class, strrpos($class, '\\') + 1);
    }

    private function __construct()
    {
    }
}
