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

namespace Fidry\CpuCounter\Exec;

use ErrorException;
use function error_get_last;

// see https://github.com/thecodingmachine/safe/blob/master/generated/Exceptions/ExecException.php
final class ExecException extends ErrorException
{
    public static function createFromPhpError(): self
    {
        $error = error_get_last();

        return new self($error['message'] ?? 'An error occured', 0, $error['type'] ?? 1);
    }
}
