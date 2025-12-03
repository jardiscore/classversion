<?php

declare(strict_types=1);

namespace JardisCore\ClassVersion\Tests\unit\Data;

use InvalidArgumentException;
use JardisCore\ClassVersion\Data\ClassVersionConfig;
use PHPUnit\Framework\TestCase;

final class ClassVersionConfigTest extends TestCase
{
    public function testVersionReturnsConfigKeyForKnownLabel(): void
    {
        $config = new ClassVersionConfig([
            'v1' => ['alpha', 'a1', ' ALPHA '],
            'v2' => ['beta'],
        ]);

        $this->assertEquals('v1', $config->version('alpha'));
        $this->assertEquals('v1', $config->version('a1'));
        $this->assertEquals('v1', $config->version('ALPHA'));
        $this->assertEquals('v2', $config->version('beta'));
    }

    public function testVersionReturnsNullForUnknownLabelOrNull(): void
    {
        $config = new ClassVersionConfig([
            'v1' => ['alpha'],
        ]);

        $this->assertEmpty($config->version(null));
        $this->assertEmpty($config->version(''));
        $this->assertSame('unknown', $config->version('unknown'));
        $this->assertEmpty($config->version('   '));
    }

    public function testConstructorNormalizesAndDeduplicatesLabels(): void
    {
        $config = new ClassVersionConfig([
            'v1' => [' alpha ', 'alpha', 'ALPHA', 'alpha ', '  ALPHA  '],
        ]);

        $this->assertEquals('v1', $config->version('alpha'));
        $this->assertEquals('v1', $config->version('ALPHA'));
        $this->assertEquals('v1', $config->version(' ALPHA '));
    }

    public function testInvalidTopLevelShapeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ClassVersionConfig([
            123 => ['ok'],
        ]);
    }

    public function testInvalidLabelTypeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore-next-line */
        new ClassVersionConfig([
            'v1' => ['alpha', 5],
        ]);
    }

    public function testEmptyLabelThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ClassVersionConfig([
            'v1' => ['alpha', '   '],
        ]);
    }
}
