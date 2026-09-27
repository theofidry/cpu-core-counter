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

namespace Fidry\CpuCoreCounter\Test\FileReader;

use Fidry\CpuCoreCounter\FileReader\FileReader;

final class DummyFileReader implements FileReader
{
    /**
     * @var array<string, string>
     */
    private $files;

    /**
     * @param array<string, string> $files Contents keyed by path.
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public function read(string $path): ?string
    {
        return $this->files[$path] ?? null;
    }
}
