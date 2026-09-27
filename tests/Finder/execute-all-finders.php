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

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\EnvVariableFinder;
use Fidry\CpuCoreCounter\Finder\FinderRegistry;
use Fidry\CpuCoreCounter\Finder\OnlyInPowerShellFinder;

require_once __DIR__.'/../../vendor/autoload.php';

set_error_handler(
    static function (int $level, string $message): bool {
        if (0 !== (error_reporting() & $level)) {
            echo 'Error: ', $message, PHP_EOL;
        }

        return true;
    }
);

$finders = array_merge(
    FinderRegistry::getAllVariants(),
    [
        FinderRegistry::getDefaultCountLimitFinder(),
        new EnvVariableFinder('KUBERNETES_CPU_LIMIT'),
        // @phpstan-ignore method.deprecatedClass, new.deprecatedClass
        new OnlyInPowerShellFinder(new DummyCpuCoreFinder(1)),
    ]
);

foreach ($finders as $finder) {
    /** @var CpuCoreFinder $finder */
    try {
        $finder->find();
        $finder->diagnose();
    } catch (Throwable $throwable) {
        echo $finder->toString(), ': ', get_class($throwable), ': ', $throwable->getMessage(), PHP_EOL;
    }
}
