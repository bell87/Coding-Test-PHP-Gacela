<?php

declare(strict_types=1);

namespace GacelaTest\Integration\Framework\Config\ConfigFactory;

use Gacela\Framework\Config\ConfigFactory;
use Gacela\Framework\Config\GacelaConfigBuilder\ConfigBuilder;
use Gacela\Framework\Config\GacelaConfigBuilder\MappingInterfacesBuilder;
use Gacela\Framework\Config\GacelaConfigBuilder\SuffixTypesBuilder;
use Gacela\Framework\Config\GacelaFileConfig\GacelaConfigFile;
use Gacela\Framework\Config\GacelaFileConfig\GacelaConfigItem;
use Gacela\Framework\Setup\SetupGacela;
use GacelaTest\Fixtures\AbstractCustom;
use GacelaTest\Fixtures\CustomClass;
use GacelaTest\Fixtures\CustomInterface;
use PHPUnit\Framework\TestCase;

final class ConfigFactoryTest extends TestCase
{
    public function test_empty_setup_then_default_gacela_config_file(): void
    {
        $bootstrapSetup = new SetupGacela();

        $actual = (new ConfigFactory(__DIR__ . '/WithoutGacelaFile', $bootstrapSetup))
            ->createGacelaFileConfig();

        $expected = GacelaConfigFile::withDefaults();

        self::assertEquals($expected, $actual);
    }

    public function test_only_gacela_file_exists(): void
    {
        $bootstrapSetup = new SetupGacela();

        $actual = (new ConfigFactory(__DIR__ . '/WithGacelaFile', $bootstrapSetup))
            ->createGacelaFileConfig();

        $expected = (new GacelaConfigFile())
            ->setConfigItems([
                GacelaConfigItem::withDefaults(),
                new GacelaConfigItem('config/from-gacela-file.php', ''),
            ])
            ->setMappingInterfaces([
                CustomInterface::class => CustomClass::class,
            ])
            ->setSuffixTypes([
                'Facade' => ['Facade', 'FacadeFromGacelaFile'],
                'Factory' => ['Factory', 'FactoryFromGacelaFile'],
                'Config' => ['Config', 'ConfigFromGacelaFile'],
                'DependencyProvider' => ['DependencyProvider', 'DependencyProviderFromGacelaFile'],
            ]);

        self::assertEquals($expected, $actual);
    }

    public function test_only_bootstrap_setup_gacela_exists(): void
    {
        $bootstrapSetup = (new SetupGacela())
            ->setConfig(static function (ConfigBuilder $builder): void {
                $builder->add('config/from-bootstrap.php');
            })
            ->setExternalServices(['CustomClassFromBootstrap' => CustomClass::class])
            ->setMappingInterfaces(
                static function (MappingInterfacesBuilder $mappingInterfacesBuilder, array $externalServices): void {
                    $mappingInterfacesBuilder->bind(
                        CustomInterface::class,
                        $externalServices['CustomClassFromBootstrap']
                    );
                },
            )
            ->setSuffixTypes(static function (SuffixTypesBuilder $suffixTypesBuilder): void {
                $suffixTypesBuilder
                    ->addFacade('FacadeFromBootstrap')
                    ->addFactory('FactoryFromBootstrap')
                    ->addConfig('ConfigFromBootstrap')
                    ->addDependencyProvider('DependencyProviderFromBootstrap');
            });

        $actual = (new ConfigFactory(__DIR__ . '/WithoutGacelaFile', $bootstrapSetup))
            ->createGacelaFileConfig();

        $expected = (new GacelaConfigFile())
            ->setConfigItems([
                new GacelaConfigItem('config/from-bootstrap.php', ''),
            ])
            ->setMappingInterfaces([
                CustomInterface::class => CustomClass::class,
            ])
            ->setSuffixTypes([
                'Facade' => ['Facade', 'FacadeFromBootstrap'],
                'Factory' => ['Factory', 'FactoryFromBootstrap'],
                'Config' => ['Config', 'ConfigFromBootstrap'],
                'DependencyProvider' => ['DependencyProvider', 'DependencyProviderFromBootstrap'],
            ]);

        self::assertEquals($expected, $actual);
    }

    public function test_combine_bootstrap_setup_with_gacela_file(): void
    {
        $bootstrapSetup = (new SetupGacela())
            ->setConfig(static function (ConfigBuilder $builder): void {
                $builder->add('config/from-bootstrap.php');
            })
            ->setExternalServices(['CustomClassFromBootstrap' => CustomClass::class])
            ->setMappingInterfaces(
                static function (MappingInterfacesBuilder $mappingInterfacesBuilder, array $externalServices): void {
                    $mappingInterfacesBuilder->bind(AbstractCustom::class, $externalServices['CustomClassFromBootstrap']);
                },
            )
            ->setSuffixTypes(static function (SuffixTypesBuilder $suffixTypesBuilder): void {
                $suffixTypesBuilder
                    ->addFacade('FacadeFromBootstrap')
                    ->addFactory('FactoryFromBootstrap')
                    ->addConfig('ConfigFromBootstrap')
                    ->addDependencyProvider('DependencyProviderFromBootstrap');
            });

        $actual = (new ConfigFactory(__DIR__ . '/WithGacelaFile', $bootstrapSetup))
            ->createGacelaFileConfig();

        $expected = (new GacelaConfigFile())
            ->setConfigItems([
                new GacelaConfigItem('config/from-bootstrap.php', ''),
                new GacelaConfigItem('config/from-gacela-file.php', ''),
            ])
            ->setMappingInterfaces([
                CustomInterface::class => CustomClass::class,
                AbstractCustom::class => CustomClass::class,
            ])
            ->setSuffixTypes([
                'Facade' => [
                    'Facade',
                    'FacadeFromBootstrap',
                    'FacadeFromGacelaFile',
                ],
                'Factory' => [
                    'Factory',
                    'FactoryFromBootstrap',
                    'FactoryFromGacelaFile',
                ],
                'Config' => [
                    'Config',
                    'ConfigFromBootstrap',
                    'ConfigFromGacelaFile',
                ],
                'DependencyProvider' => [
                    'DependencyProvider',
                    'DependencyProviderFromBootstrap',
                    'DependencyProviderFromGacelaFile',
                ],
            ]);

        self::assertEquals($expected, $actual);
    }
}
