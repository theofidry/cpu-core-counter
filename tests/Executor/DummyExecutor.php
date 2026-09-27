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

namespace Fidry\CpuCoreCounter\Test\Executor;

use Fidry\CpuCoreCounter\Executor\DiagnosableProcessExecutor;

final class DummyExecutor implements DiagnosableProcessExecutor
{
    /**
     * @var string|null
     */
    private $unavailabilityReason;

    /**
     * @var array{string, string}|null
     */
    private $output;

    /**
     * @var string|null
     */
    private $lastCommand;

    /**
     * @param array{string, string}|null $output
     */
    public function setOutput(?array $output): void
    {
        $this->output = $output;
    }

    public function setUnavailabilityReason(?string $unavailabilityReason): void
    {
        $this->unavailabilityReason = $unavailabilityReason;
    }

    public function getUnavailabilityReason(): ?string
    {
        return $this->unavailabilityReason;
    }

    public function execute(string $command): ?array
    {
        $this->lastCommand = $command;

        return $this->output ?? null;
    }

    public function getLastCommand(): ?string
    {
        return $this->lastCommand;
    }
}
