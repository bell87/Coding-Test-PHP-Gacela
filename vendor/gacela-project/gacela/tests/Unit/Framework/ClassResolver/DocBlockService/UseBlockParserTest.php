<?php

declare(strict_types=1);

namespace GacelaTest\Unit\Framework\ClassResolver\DocBlockService;

use Gacela\Framework\ClassResolver\DocBlockService\UseBlockParser;
use PHPUnit\Framework\TestCase;

final class UseBlockParserTest extends TestCase
{
    private UseBlockParser $parser;

    protected function setUp(): void
    {
        $this->parser = new UseBlockParser();
    }

    public function test_get_class_from_empty_php_code(): void
    {
        $actual = $this->parser->getUseStatement('TestClass', '');

        self::assertSame('', $actual);
    }

    public function test_get_class_from_use(): void
    {
        $actual = $this->parser->getUseStatement('ExistingClassInOtherNs', $this->phpCode());

        self::assertSame('Ns\Test\Other\ExistingClassInOtherNs', $actual);
    }

    public function test_get_class_in_same_namespace(): void
    {
        $actual = $this->parser->getUseStatement('ExistingClassInSameNs', $this->phpCode());

        self::assertSame('Ns\Test\ExistingClassInSameNs', $actual);
    }

    public function test_get_class_with_alias(): void
    {
        $actual = $this->parser->getUseStatement('AliasClass', $this->phpCode());

        self::assertSame('Ns\Test\Other\WithAliasClassInOtherNs', $actual);
    }

    public function test_get_commented_use_with_double_slash_then_uses_current_namespace(): void
    {
        $actual = $this->parser->getUseStatement('CommentedClassInOtherNs', $this->phpCode());

        self::assertSame('Ns\Test\CommentedClassInOtherNs', $actual);
    }

    public function test_get_commented_use_with_hashtag_then_uses_current_namespace(): void
    {
        $actual = $this->parser->getUseStatement('CommentedClassInAnotherNs', $this->phpCode());

        self::assertSame('Ns\Test\CommentedClassInAnotherNs', $actual);
    }

    private function phpCode(): string
    {
        return <<<'PHP'
<?php 

// namespace FailingCommentedLine\Test;
#namespace FailingCommentedAnotherLine\Test;
namespace Ns\Test;

use Ns\Test\Other\ExistingClassInOtherNs;
use Ns\Test\Other\WithAliasClassInOtherNs as AliasClass;
//use Ns\Test\Other\CommentedClassInOtherNs;
# use Ns\Test\Other\CommentedClassInAnotherNs;
use Ns\Test\Duplicated\ExistingClassInOtherNs; // this will be ignored. The first match will win.
                                               // this is also illegal in real code. I place it here 
                                               // just to verify the actual logic.
final class TestClass
{
    public function foo(): void 
    {
    }
}
PHP;
    }
}
