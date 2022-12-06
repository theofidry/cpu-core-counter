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

namespace Fidry\Makefile\Test;

use Composer\InstalledVersions;
use PHPUnit\Framework\TestCase;

// When testing 7.2 or 7.3, fidry/makefile is not installed hence this stub.
if (InstalledVersions::isInstalled('fidry/makefile')) {
    return;
}

/**
 * @internal
 */
class BaseMakefileTestCase extends TestCase
{
    public function test_dummy(): void
    {
        $this->addToAssertionCount(1);
    }
}
