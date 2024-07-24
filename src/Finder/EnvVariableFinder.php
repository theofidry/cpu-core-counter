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

use function array_map;
use function getenv;
use function implode;
use function preg_match;
use function sprintf;
use function var_export;
use const PHP_EOL;

final class EnvVariableFinder implements CpuCoreFinder
{
    private const ENVIRONMENT_VARIABLE_NAMES = [
        'KUBERNETES_CPU_LIMIT',
    ];

    public function diagnose(): string
    {
        $values = array_map(
            static function (string $name): string {
                $value = getenv($name);

                return sprintf(
                    'getenv(%s)=%s; parsed: %s',
                    $name,
                    var_export($value, true),
                    self::isInteger($value) ? $value : 'null'
                );
            },
            self::ENVIRONMENT_VARIABLE_NAMES
        );

        return implode(PHP_EOL, $values);
    }

    public function find(): ?int
    {
        foreach (self::ENVIRONMENT_VARIABLE_NAMES as $name) {
            $value = getenv($name);

            if (self::isInteger($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    public function toString(): string
    {
        return sprintf(
            'getenv(%s)',
            implode(', ', self::ENVIRONMENT_VARIABLE_NAMES)
        );
    }

    /**
     * @param string|false $value
     */
    private static function isInteger($value): bool
    {
        return false !== $value && 1 === preg_match('/^\d+$/', $value);
    }
}
