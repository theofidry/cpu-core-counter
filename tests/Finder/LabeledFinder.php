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

namespace Fidry\CpuCoreCounter\Test\Finder;

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use function get_class;

final class LabeledFinder implements CpuCoreFinder
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var CpuCoreFinder
     */
    private $decoratedFinder;

    public function __construct(
        CpuCoreFinder $decoratedFinder,
        ?string $label = null
    ) {
        $this->decoratedFinder = $decoratedFinder;
        $this->label = $label ?? self::getShortClassName($decoratedFinder);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function diagnose(): string
    {
        return $this->decoratedFinder->diagnose();
    }

    public function find(): ?int
    {
        return $this->decoratedFinder->find();
    }

    /**
     * Strips the namespace off a fully-qualified class name. E.g.:
     * "Acme\Foo\Bar" -> "Bar".
     */
    private static function getShortClassName(CpuCoreFinder $finder): string
    {
        $className = get_class($finder);

        if (false !== ($pos = mb_strrpos($className, '\\'))) {
            return substr($className, $pos + 1);
        }

        return $className;
    }
}
