<?php

declare(strict_types=1);

namespace GacelaTest\Unit\Framework\Config\GacelaFileConfig\Factory;

use Gacela\Framework\Config\ConfigReader\PhpConfigReader;
use Gacela\Framework\Config\GacelaConfigBuilder\ConfigBuilder;
use Gacela\Framework\Config\GacelaConfigBuilder\MappingInterfacesBuilder;
use Gacela\Framework\Config\GacelaConfigBuilder\SuffixTypesBuilder;
use Gacela\Framework\Config\GacelaFileConfig\Factory\GacelaConfigFromBootstrapFactory;
use Gacela\Framework\Config\GacelaFileConfig\GacelaConfigFile;
use Gacela\Framework\Config\GacelaFileConfig\GacelaConfigItem;
use Gacela\Framework\Setup\SetupGacela;
use Gacela\Framework\Setup\SetupGacelaInterface;
use GacelaTest\Fixtures\CustomClass;
use GacelaTest\Fixtures\CustomInterface;
use PHPUnit\Framework\TestCase;

final class GacelaConfigFromBootstrapFactoryTest extends TestCase
{
    public function test_no_global_services_then_default(): void
    {
        $factory = new GacelaConfigFromBootstrapFactory(
            $this->createStub(SetupGacelaInterface::class)
        );

        self::assertEquals(GacelaConfigFile::withDefaults(), $factory->createGacelaFileConfig());
    }

    public function test_no_special_global_services_then_default(): void
    {
        $setupGacela = $this->createStub(SetupGacelaInterface::class);
        $setupGacela->method('externalServices')->willReturn([
            'randomKey' => 'randomValue',
        ]);

        $factory = new GacelaConfigFromBootstrapFactory($setupGacela);

        self::assertEquals(GacelaConfigFile::withDefaults(), $factory->createGacelaFileConfig());
    }

    public function test_global_service_config(): void
    {
        $factory = new GacelaConfigFromBootstrapFactory((new SetupGacela())->setConfig(
            static function (ConfigBuilder $configBuilder): void {
                $configBuilder->add('custom-path.php', 'custom-path_local.php');
            }
        ));

        $expected = GacelaConfigFile::withDefaults()
            ->setConfigItems([new GacelaConfigItem('custom-path.php', 'custom-path_local.php', new PhpConfigReader())]);

        self::assertEquals($expected, $factory->createGacelaFileConfig());
    }

    public function test_global_service_mapping_interfaces_with_global_services(): void
    {
        $factory = new GacelaConfigFromBootstrapFactory(
            (new SetupGacela())
                ->setExternalServices(['externalServiceKey' => 'externalServiceValue'])
                ->setMappingInterfaces(static function (
                    MappingInterfacesBuilder $interfacesBuilder,
                    array $externalServices
                ): void {
                    self::assertSame($externalServices['externalServiceKey'], 'externalServiceValue');
                    $interfacesBuilder->bind(CustomInterface::class, CustomClass::class);
                })
        );

        $expected = GacelaConfigFile::withDefaults()
            ->setMappingInterfaces([CustomInterface::class => CustomClass::class]);

        self::assertEquals($expected, $factory->createGacelaFileConfig());
    }

    public function test_global_service_suffix_types(): void
    {
        $factory = new GacelaConfigFromBootstrapFactory(
            (new SetupGacela())
                ->setSuffixTypes(
                    static function (SuffixTypesBuilder $suffixTypesBuilder): void {
                        $suffixTypesBuilder->addDependencyProvider('DPCustom');
                    },
                )
        );

        $expected = GacelaConfigFile::withDefaults()
            ->setSuffixTypes([
                'DependencyProvider' => ['DependencyProvider', 'DPCustom'],
                'Factory' => ['Factory'],
                'Config' => ['Config'],
                'Facade' => ['Facade'],
            ]);

        self::assertEquals($expected, $factory->createGacelaFileConfig());
    }
}
