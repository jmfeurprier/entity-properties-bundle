<?php

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Definition\EntityDefinition;
use Jmf\EntityRendering\Definition\EntityDefinitionResolver;
use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\EntityConfigurationNotFoundException;
use PHPUnit\Framework\TestCase;
use stdClass;

class EntityRendererTest extends TestCase
{
    public function testRenderReturnsRenderedEntityWithAllProperties(): void
    {
        $entity = new stdClass();

        $definition1 = new PropertyDefinition(label: 'Name', source: 'name', template: null);
        $definition2 = new PropertyDefinition(label: 'Age', source: 'age', template: null);

        $entityDefinition = new EntityDefinition([$definition1, $definition2]);

        $renderedProp1 = new RenderedProperty('Name', 'John');
        $renderedProp2 = new RenderedProperty('Age', '30');

        $entityDefinitionResolver = $this->createMock(EntityDefinitionResolver::class);
        $entityDefinitionResolver
            ->expects(self::once())
            ->method('resolve')
            ->with($entity)
            ->willReturn($entityDefinition);

        $propertyRenderer = $this->createMock(PropertyRenderer::class);
        $propertyRenderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnOnConsecutiveCalls($renderedProp1, $renderedProp2);

        $renderer = new EntityRenderer($entityDefinitionResolver, $propertyRenderer);
        $result   = $renderer->render($entity);

        $properties = iterator_to_array($result->getRenderedProperties());

        self::assertCount(2, $properties);
        self::assertSame($renderedProp1, $properties[0]);
        self::assertSame($renderedProp2, $properties[1]);
    }

    public function testRenderWithNoPropertiesReturnsEmptyRenderedEntity(): void
    {
        $entity           = new stdClass();
        $entityDefinition = new EntityDefinition([]);

        $entityDefinitionResolver = $this->createStub(EntityDefinitionResolver::class);
        $entityDefinitionResolver->method('resolve')->willReturn($entityDefinition);

        $propertyRenderer = $this->createMock(PropertyRenderer::class);
        $propertyRenderer->expects(self::never())->method('render');

        $renderer = new EntityRenderer($entityDefinitionResolver, $propertyRenderer);
        $result   = $renderer->render($entity);

        self::assertSame([], iterator_to_array($result->getRenderedProperties()));
    }

    public function testRenderPropagatesEntityConfigurationNotFoundException(): void
    {
        $entity = new stdClass();

        $entityDefinitionResolver = $this->createStub(EntityDefinitionResolver::class);
        $entityDefinitionResolver
            ->method('resolve')
            ->willThrowException(new EntityConfigurationNotFoundException(entity: $entity));

        $renderer = new EntityRenderer($entityDefinitionResolver, $this->createStub(PropertyRenderer::class));

        $this->expectException(EntityConfigurationNotFoundException::class);

        $renderer->render($entity);
    }
}
