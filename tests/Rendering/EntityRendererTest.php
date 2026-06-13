<?php

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Compilation\EntityDefinitionCompiler;
use Jmf\EntityRendering\Definition\EntityDefinition;
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

        $entityDefinitionCompiler = $this->createMock(EntityDefinitionCompiler::class);
        $entityDefinitionCompiler
            ->expects(self::once())
            ->method('compile')
            ->with($entity)
            ->willReturn($entityDefinition);

        $propertyRenderer = $this->createMock(PropertyRenderer::class);
        $propertyRenderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnOnConsecutiveCalls($renderedProp1, $renderedProp2);

        $renderer = new EntityRenderer($entityDefinitionCompiler, $propertyRenderer);
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

        $entityDefinitionCompiler = $this->createStub(EntityDefinitionCompiler::class);
        $entityDefinitionCompiler->method('compile')->willReturn($entityDefinition);

        $propertyRenderer = $this->createMock(PropertyRenderer::class);
        $propertyRenderer->expects(self::never())->method('render');

        $renderer = new EntityRenderer($entityDefinitionCompiler, $propertyRenderer);
        $result   = $renderer->render($entity);

        self::assertSame([], iterator_to_array($result->getRenderedProperties()));
    }

    public function testRenderPropagatesEntityConfigurationNotFoundException(): void
    {
        $entity = new stdClass();

        $entityDefinitionCompiler = $this->createStub(EntityDefinitionCompiler::class);
        $entityDefinitionCompiler
            ->method('compile')
            ->willThrowException(new EntityConfigurationNotFoundException(entity: $entity));

        $renderer = new EntityRenderer($entityDefinitionCompiler, $this->createStub(PropertyRenderer::class));

        $this->expectException(EntityConfigurationNotFoundException::class);

        $renderer->render($entity);
    }
}
