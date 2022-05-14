<?php

declare(strict_types=1);

use Gacela\Framework\Config\GacelaConfigBuilder\MappingInterfacesBuilder;
use Gacela\Framework\Setup\SetupGacela;
use GacelaTest\Feature\Framework\BindingInterfacesWithInnerDependencies\LocalConfig\Domain\Greeter\CorrectCompanyGenerator;
use GacelaTest\Feature\Framework\BindingInterfacesWithInnerDependencies\LocalConfig\Domain\Greeter\IncorrectCompanyGenerator;
use GacelaTest\Feature\Framework\BindingInterfacesWithInnerDependencies\LocalConfig\Domain\GreeterGeneratorInterface;

/**
 * This Feature-test does two things:
 *
 * - 1: Check the "externalService" variable was properly defined in the 'Gacela::bootstrap()' with the key `isWorking?`.
 *
 * - 2: Let Gacela resolve in the factory the mapping from `GreeterGeneratorInterface` to `CorrectCompanyGenerator`
 *      AND auto-resolve the class `CustomNameGenerator` from the `CorrectCompanyGenerator` constructor.
 */
return static fn () => (new SetupGacela())
    ->setMappingInterfaces(static function (
        MappingInterfacesBuilder $mappingInterfacesBuilder,
        array $externalServices
    ): void {
        $mappingInterfacesBuilder->bind(GreeterGeneratorInterface::class, IncorrectCompanyGenerator::class);

        if ($externalServices['isWorking?'] === 'yes!') {
            $mappingInterfacesBuilder->bind(GreeterGeneratorInterface::class, CorrectCompanyGenerator::class);
        }
    });
