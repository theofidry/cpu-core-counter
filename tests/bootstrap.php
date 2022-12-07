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

use Composer\InstalledVersions;

$autoloader = require __DIR__.'/../vendor/autoload.php';

if (!InstalledVersions::isInstalled('fidry/makefile')) {
    require_once __DIR__.'/../stubs/BaseMakefileTestCase.php';
}

return $autoloader;
