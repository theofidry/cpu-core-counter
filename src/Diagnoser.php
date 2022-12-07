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

namespace Fidry\CpuCoreCounter;

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\FinderRegistry;
use function array_map;
use function implode;
use const PHP_EOL;

/**
 * Provides an explanation which may offer some insight as to what each finders
 * will be able to find.
 *
 * This is practical to have an idea of what each finder will find collect
 * information for the unit tests, since integration tests are quite complicated
 * as dependent on complex infrastructures.
 *
 * @private
 */
final class Diagnoser
{
    public static function diagnose(): string
    {
        $diagnoses = array_map(
            static function (CpuCoreFinder $finder): string {
                return implode(
                    '',
                    [
                        $finder->toString(),
                        ': ',
                        PHP_EOL,
                        $finder->diagnose(),
                    ]
                );
            },
            FinderRegistry::getAllVariants()
        );

        return implode(PHP_EOL, $diagnoses);
    }

    public static function execute(): string
    {
        $diagnoses = array_map(
            static function (CpuCoreFinder $finder): string {
                return implode(
                    '',
                    [
                        $finder->toString(),
                        ': ',
                        null !== $finder->find() ? '.' : 'F',
                    ]
                );
            },
            FinderRegistry::getAllVariants()
        );

        return implode(PHP_EOL, $diagnoses);
    }
}
