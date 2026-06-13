<?php

namespace Jmf\EntityRendering\Rendering;

use PHPUnit\Framework\TestCase;

class RenderedEntityTest extends TestCase
{
    public function testGetRenderedPropertiesEmpty(): void
    {
        $entity = new RenderedEntity([]);

        self::assertSame([], iterator_to_array($entity->getRenderedProperties()));
    }

    public function testGetRenderedPropertiesReturnsAllProperties(): void
    {
        $prop1 = new RenderedProperty('label1', 'value1');
        $prop2 = new RenderedProperty('label2', 'value2');

        $entity = new RenderedEntity([$prop1, $prop2]);

        $properties = iterator_to_array($entity->getRenderedProperties());

        self::assertCount(2, $properties);
        self::assertSame($prop1, $properties[0]);
        self::assertSame($prop2, $properties[1]);
    }

    public function testConstructorRejectsNonRenderedPropertyItems(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RenderedEntity(['not a RenderedProperty']); // @phpstan-ignore argument.type
    }
}
