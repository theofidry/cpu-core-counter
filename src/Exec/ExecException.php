<?php

declare(strict_types=1);

namespace Fidry\CpuCounter\Exec;

use ErrorException;

// see https://github.com/thecodingmachine/safe/blob/master/generated/Exceptions/ExecException.php
class ExecException extends ErrorException
{
    public static function createFromPhpError(): self
    {
        $error = error_get_last();
        return new self($error['message'] ?? 'An error occured', 0, $error['type'] ?? 1);
    }
}
