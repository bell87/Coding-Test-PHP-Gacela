<?php

declare(strict_types=1);

namespace GacelaTest\Unit\Framework\ClassResolver;

use Gacela\Framework\AbstractFacade;
use Gacela\Framework\ClassResolver\ClassInfo;
use GacelaTest\Fixtures\ClassInfoTestingFacade;
use PHPUnit\Framework\TestCase;

final class ClassInfoTest extends TestCase
{
    public function test_anonymous_class(): void
    {
        $facade = new class() extends AbstractFacade {
        };
        $actual = ClassInfo::from($facade, 'Factory');

        self::assertSame('module-name@anonymous\ClassInfoTest', $actual->getModule());
        self::assertSame('module-name@anonymous\ClassInfoTest', $actual->getFullNamespace());
        self::assertSame('\module-name@anonymous\ClassInfoTest\Factory', $actual->getCacheKey());
    }

    public function test_object_real_class(): void
    {
        $facade = new ClassInfoTestingFacade();
        $actual = ClassInfo::from($facade, 'Factory');

        self::assertSame('Fixtures', $actual->getModule());
        self::assertSame('GacelaTest\Fixtures', $actual->getFullNamespace());
        self::assertSame('\GacelaTest\Fixtures\Factory', $actual->getCacheKey());
    }

    public function test_string_real_class(): void
    {
        $actual = ClassInfo::from(ClassInfoTestingFacade::class, 'Factory');

        self::assertSame('Fixtures', $actual->getModule());
        self::assertSame('GacelaTest\Fixtures', $actual->getFullNamespace());
        self::assertSame('\GacelaTest\Fixtures\Factory', $actual->getCacheKey());
    }
}
