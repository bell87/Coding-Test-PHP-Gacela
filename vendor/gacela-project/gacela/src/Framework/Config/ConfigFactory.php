<?php

declare(strict_types=1);

namespace Gacela\Framework\Config;

use Gacela\Framework\AbstractFactory;
use Gacela\Framework\Config\GacelaFileConfig\Factory\GacelaConfigFromBootstrapFactory;
use Gacela\Framework\Config\GacelaFileConfig\Factory\GacelaConfigUsingGacelaPhpFileFactory;
use Gacela\Framework\Config\GacelaFileConfig\GacelaConfigFileInterface;
use Gacela\Framework\Config\PathNormalizer\AbsolutePathNormalizer;
use Gacela\Framework\Config\PathNormalizer\WithoutSuffixAbsolutePathStrategy;
use Gacela\Framework\Config\PathNormalizer\WithSuffixAbsolutePathStrategy;
use Gacela\Framework\Setup\SetupGacelaInterface;

final class ConfigFactory extends AbstractFactory
{
    private const GACELA_PHP_CONFIG_FILENAME = 'gacela.php';

    private string $appRootDir;

    private SetupGacelaInterface $setup;

    public function __construct(string $appRootDir, SetupGacelaInterface $setup)
    {
        $this->appRootDir = $appRootDir;
        $this->setup = $setup;
    }

    public function createConfigLoader(): ConfigLoader
    {
        return new ConfigLoader(
            $this->createGacelaFileConfig(),
            $this->createPathFinder(),
            $this->createPathNormalizer(),
        );
    }

    public function createGacelaFileConfig(): GacelaConfigFileInterface
    {
        $gacelaPhpPath = $this->appRootDir . '/' . self::GACELA_PHP_CONFIG_FILENAME;
        $fileIo = $this->createFileIo();

        if ($fileIo->existsFile($gacelaPhpPath)) {
            $factoryFromGacelaPhp = new GacelaConfigUsingGacelaPhpFileFactory($gacelaPhpPath, $this->setup, $fileIo);
            $gacelaSetupFromGacelaPhp = $factoryFromGacelaPhp->createGacelaFileConfig();
        }

        $factoryFromBootstrap = new GacelaConfigFromBootstrapFactory($this->setup);
        $gacelaSetupFromBootstrap = $factoryFromBootstrap->createGacelaFileConfig();

        if (isset($gacelaSetupFromGacelaPhp) && $gacelaSetupFromGacelaPhp instanceof GacelaConfigFileInterface) {
            return $gacelaSetupFromBootstrap->combine($gacelaSetupFromGacelaPhp);
        }

        return $gacelaSetupFromBootstrap;
    }

    private function createFileIo(): FileIoInterface
    {
        return new FileIo();
    }

    private function createPathFinder(): PathFinderInterface
    {
        return new PathFinder();
    }

    private function createPathNormalizer(): PathNormalizerInterface
    {
        return new AbsolutePathNormalizer([
            AbsolutePathNormalizer::WITHOUT_SUFFIX => new WithoutSuffixAbsolutePathStrategy($this->appRootDir),
            AbsolutePathNormalizer::WITH_SUFFIX => new WithSuffixAbsolutePathStrategy($this->appRootDir, $this->env()),
        ]);
    }

    private function env(): string
    {
        return getenv('APP_ENV') ?: '';
    }
}
