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

namespace Fidry\CpuCoreCounter\Executor;

/**
 * A process executor that can tell whether it can be used in the current
 * environment, and why not when it cannot.
 *
 * This is a separate interface so that adding the method does not break
 * the existing ProcessExecutor implementations. It will be merged into
 * ProcessExecutor in the next major version.
 */
interface DiagnosableProcessExecutor extends ProcessExecutor
{
    /**
     * @return string|null The reason why the executor cannot be used, or null if it can
     */
    public function getUnavailabilityReason(): ?string;
}
