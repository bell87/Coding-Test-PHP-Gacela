<?php

declare(strict_types=1);

namespace GacelaTest\Unit\Framework\Config\GacelaConfigBuilder;

use Gacela\Framework\Config\ConfigReaderInterface;
use Gacela\Framework\Config\GacelaConfigBuilder\ConfigBuilder;
use Gacela\Framework\Config\GacelaFileConfig\GacelaConfigItem;
use GacelaTest\Fixtures\SimpleEnvConfigReader;
use PHPUnit\Framework\TestCase;

final class ConfigBuilderTest extends TestCase
{
    public function test_empty_then_default(): void
    {
        $builder = new ConfigBuilder();

        self::assertEquals([GacelaConfigItem::withDefaults()], $builder->build());
    }

    public function test_custom_path(): void
    {
        $builder = new ConfigBuilder();
        $builder->add('custom/*.php');

        self::assertEquals(
            [new GacelaConfigItem('custom/*.php', '')],
            $builder->build()
        );
    }

    public function test_custom_path_local(): void
    {
        $builder = new ConfigBuilder();
        $builder->add('', 'custom/local.php');

        self::assertEquals(
            [new GacelaConfigItem('', 'custom/local.php')],
            $builder->build()
        );
    }

    public function test_custom_reader(): void
    {
        $reader = new class() implements ConfigReaderInterface {
            public function read(string $absolutePath): array
            {
                return ['key' => 'value'];
            }
        };

        $builder = new ConfigBuilder();
        $builder->add('custom/*.php', 'custom/local.php', $reader);

        self::assertEquals(
            [new GacelaConfigItem('custom/*.php', 'custom/local.php', $reader)],
            $builder->build()
        );
    }

    public function test_custom_reader_by_class_name(): void
    {
        $builder = new ConfigBuilder();
        $builder->add('custom/*.php', 'custom/local.php', SimpleEnvConfigReader::class);

        self::assertEquals(
            [new GacelaConfigItem('custom/*.php', 'custom/local.php', new SimpleEnvConfigReader())],
            $builder->build()
        );
    }
}
