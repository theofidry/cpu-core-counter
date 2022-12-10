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

use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\NullCpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\OnlyOnWindowsFinder;
use PHPUnit\Framework\TestCase;
use function define;
use function defined;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\OnlyOnWindowsFinder
 *
 * @internal
 */
final class OnlyOnWindowsFinderTest extends TestCase
{
    public function test_it_can_describe_itself(): void
    {
        $finder = new OnlyOnWindowsFinder(new NullCpuCoreFinder());

        self::assertSame('OnlyOnWindowsFinder(NullCpuCoreFinder)', $finder->toString());
    }

    public function test_it_enriches_the_decorated_finder_diagnosis(): void
    {
        $finder = new OnlyOnWindowsFinder(new NullCpuCoreFinder());

        // Sanity check
        self::assertFalse(defined('PHP_WINDOWS_VERSION_MAJOR'));

        self::assertSame(
            <<<'EOF'
Non-windows platform detected (PHP_WINDOWS_VERSION_MAJOR is not set).
EOF
            ,
            $finder->diagnose()
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function test_it_enriches_the_decorated_finder_diagnosis_on_windows(): void
    {
        define('PHP_WINDOWS_VERSION_MAJOR', 'some_windows_version');
        $finder = new OnlyOnWindowsFinder(new NullCpuCoreFinder());

        self::assertSame(
            <<<'EOF'
Will return "null".
EOF
            ,
            $finder->diagnose()
        );
    }

    public function test_it_skips_its_execution_when_not_on_windows(): void
    {
        $finder = new OnlyOnWindowsFinder(new FakeFinder());

        // Sanity check
        self::assertFalse(defined('PHP_WINDOWS_VERSION_MAJOR'));

        self::assertNull($finder->find());
    }

    /**
     * @runInSeparateProcess
     */
    public function test_it_does_not_skip_its_execution_when_not_on_windows(): void
    {
        define('PHP_WINDOWS_VERSION_MAJOR', 'some_windows_version');
        $finder = new OnlyOnWindowsFinder(new DummyCpuCoreFinder(1));

        self::assertSame(1, $finder->find());
    }

    public function it_handles_finders_with_dynamic_names(): void
    {
        $finder = new OnlyOnWindowsFinder(new DynamicNameFinder(['F1', 'F2']));

        self::assertSame('OnlyOnWindowsFinder(F1)', $finder->toString());
        self::assertSame('OnlyOnWindowsFinder(F2)', $finder->toString());
    }
}
