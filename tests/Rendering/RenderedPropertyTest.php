<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Rendering;

use PHPUnit\Framework\TestCase;

class RenderedPropertyTest extends TestCase
{
    public function testGetLabel(): void
    {
        $property = new RenderedProperty('My Label', 'some value');

        self::assertSame('My Label', $property->getLabel());
    }

    public function testGetValue(): void
    {
        $property = new RenderedProperty('label', 'My Value');

        self::assertSame('My Value', $property->getValue());
    }

    public function testEmptyStrings(): void
    {
        $property = new RenderedProperty('', '');

        self::assertSame('', $property->getLabel());
        self::assertSame('', $property->getValue());
    }
}
