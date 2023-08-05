<?php

namespace NystronSolar\ElectricBillExtractorTests\Unit\Helper;

use NystronSolar\ElectricBillExtractor\Helper\StringHelper;
use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    public function testRemoveRepeatedWhitespace(): void
    {
        $this->assertSame('', StringHelper::removeRepeatedWhitespace('  '));
        $this->assertSame('', StringHelper::removeRepeatedWhitespace(' '));
        $this->assertSame('', StringHelper::removeRepeatedWhitespace(''));

        $this->assertSame('a', StringHelper::removeRepeatedWhitespace('a'));
        $this->assertSame('a', StringHelper::removeRepeatedWhitespace(' a'));
        $this->assertSame('a', StringHelper::removeRepeatedWhitespace('a '));

        $this->assertSame('a b', StringHelper::removeRepeatedWhitespace(' a  b '));
        $this->assertSame('a b', StringHelper::removeRepeatedWhitespace(' a  b'));
        $this->assertSame('a b', StringHelper::removeRepeatedWhitespace('a  b '));
        $this->assertSame('a b', StringHelper::removeRepeatedWhitespace('a  b'));
        $this->assertSame('a b', StringHelper::removeRepeatedWhitespace(' a b '));
        $this->assertSame('a b', StringHelper::removeRepeatedWhitespace('a b'));
        $this->assertSame('ab', StringHelper::removeRepeatedWhitespace(' ab '));
        $this->assertSame('ab', StringHelper::removeRepeatedWhitespace(' ab'));
        $this->assertSame('ab', StringHelper::removeRepeatedWhitespace('ab '));
    }
}