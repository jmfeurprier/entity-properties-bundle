<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\EntityRendering\Definition;

use PHPUnit\Framework\TestCase;

class EntityDefinitionTest extends TestCase
{
    public function testGetPropertyDefinitionsEmpty(): void
    {
        $definition = new EntityDefinition([]);

        self::assertSame([], iterator_to_array($definition->getPropertyDefinitions()));
    }

    public function testGetPropertyDefinitionsReturnsAll(): void
    {
        $prop1 = new PropertyDefinition(label: 'l1', source: null, template: null);
        $prop2 = new PropertyDefinition(label: 'l2', source: null, template: null);

        $definition = new EntityDefinition(
            [
                $prop1,
                $prop2,
            ],
        );

        $definitions = iterator_to_array($definition->getPropertyDefinitions());

        self::assertCount(2, $definitions);
        self::assertSame($prop1, $definitions[0]);
        self::assertSame($prop2, $definitions[1]);
    }
}
