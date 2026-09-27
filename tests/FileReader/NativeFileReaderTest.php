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

use Fidry\CpuCoreCounter\FileReader\NativeFileReader;
use PHPUnit\Framework\TestCase;
use function file_get_contents;

/**
 * @covers \Fidry\CpuCoreCounter\FileReader\NativeFileReader
 *
 * @internal
 */
final class NativeFileReaderTest extends TestCase
{
    /**
     * @var NativeFileReader
     */
    private $fileReader;

    protected function setUp(): void
    {
        $this->fileReader = new NativeFileReader();
    }

    protected function tearDown(): void
    {
        unset($this->fileReader);
    }

    /**
     * @dataProvider fileProvider
     */
    public function test_it_can_read_a_file(string $path, ?string $expected): void
    {
        $actual = $this->fileReader->read($path);

        self::assertSame($expected, $actual);
    }

    public static function fileProvider(): iterable
    {
        yield 'existing file' => [
            __FILE__,
            file_get_contents(__FILE__),
        ];

        yield 'missing file' => [
            __DIR__.'/unknown',
            null,
        ];

        yield 'directory' => [
            __DIR__,
            null,
        ];
    }
}
