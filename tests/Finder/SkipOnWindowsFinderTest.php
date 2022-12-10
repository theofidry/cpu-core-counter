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
use Fidry\CpuCoreCounter\Finder\SkipOnWindowsFinder;
use PHPUnit\Framework\TestCase;
use function define;
use function defined;
use function sprintf;

/**
 * @covers \Fidry\CpuCoreCounter\Finder\SkipOnWindowsFinder
 *
 * @internal
 */
final class SkipOnWindowsFinderTest extends TestCase
{
    public function test_it_can_describe_itself(): void
    {
        $finder = new SkipOnWindowsFinder(new NullCpuCoreFinder());

        self::assertSame(
            sprintf(
                '%s(NullCpuCoreFinder)',
                FinderShortClassName::get($finder)
            ),
            $finder->toString()
        );
    }

    public function test_it_enriches_the_decorated_finder_diagnosis(): void
    {
        $finder = new SkipOnWindowsFinder(new NullCpuCoreFinder());

        // Sanity check
        self::assertFalse(defined('PHP_WINDOWS_VERSION_MAJOR'));

        self::assertSame(
            'Will return "null".',
            $finder->diagnose()
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function test_it_enriches_the_decorated_finder_diagnosis_on_windows(): void
    {
        define('PHP_WINDOWS_VERSION_MAJOR', 'some_windows_version');
        $finder = new SkipOnWindowsFinder(new NullCpuCoreFinder());

        self::assertSame(
            'Windows platform detected (PHP_WINDOWS_VERSION_MAJOR is set).',
            $finder->diagnose()
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function test_it_skips_its_execution_when_on_windows(): void
    {
        define('PHP_WINDOWS_VERSION_MAJOR', 'some_windows_version');
        $finder = new SkipOnWindowsFinder(new DummyCpuCoreFinder(1));

        self::assertNull($finder->find());
    }

    public function test_it_does_not_skip_its_execution_when_not_on_windows(): void
    {
        $finder = new SkipOnWindowsFinder(new DummyCpuCoreFinder(1));

        // Sanity check
        self::assertFalse(defined('PHP_WINDOWS_VERSION_MAJOR'));

        self::assertSame(1, $finder->find());
    }

    public function it_handles_finders_with_dynamic_names(): void
    {
        $finder = new SkipOnWindowsFinder(new DynamicNameFinder(['F1', 'F2']));

        self::assertSame('SkipOnWindowsFinder(F1)', $finder->toString());
        self::assertSame('SkipOnWindowsFinder(F2)', $finder->toString());
    }
}
