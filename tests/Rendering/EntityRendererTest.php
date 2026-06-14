<?php

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Repository\EntityDefinitionRepository;
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

        $entityDefinitionRepository = $this->createMock(EntityDefinitionRepository::class);
        $entityDefinitionRepository
            ->expects(self::once())
            ->method('get')
            ->with($entity)
            ->willReturn($entityDefinition);

        $propertyRenderer = $this->createMock(PropertyRenderer::class);
        $propertyRenderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnOnConsecutiveCalls($renderedProp1, $renderedProp2);

        $renderer = new EntityRenderer($entityDefinitionRepository, $propertyRenderer);
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

        $entityDefinitionRepository = $this->createStub(EntityDefinitionRepository::class);
        $entityDefinitionRepository->method('get')->willReturn($entityDefinition);

        $propertyRenderer = $this->createMock(PropertyRenderer::class);
        $propertyRenderer->expects(self::never())->method('render');

        $renderer = new EntityRenderer($entityDefinitionRepository, $propertyRenderer);
        $result   = $renderer->render($entity);

        self::assertSame([], iterator_to_array($result->getRenderedProperties()));
    }

    public function testRenderPropagatesEntityConfigurationNotFoundException(): void
    {
        $entity = new stdClass();

        $entityDefinitionRepository = $this->createStub(EntityDefinitionRepository::class);
        $entityDefinitionRepository
            ->method('get')
            ->willThrowException(new EntityConfigurationNotFoundException(entity: $entity));

        $renderer = new EntityRenderer($entityDefinitionRepository, $this->createStub(PropertyRenderer::class));

        $this->expectException(EntityConfigurationNotFoundException::class);

        $renderer->render($entity);
    }
}
