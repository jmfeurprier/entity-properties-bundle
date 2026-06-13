<?php

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\PropertyLabelRenderingException;
use Jmf\EntityRendering\Exception\PropertyRenderingException;
use PHPUnit\Framework\TestCase;
use stdClass;

class PropertyRendererTest extends TestCase
{
    public function testRenderReturnsRenderedPropertyWithLabelAndValue(): void
    {
        $entity     = new stdClass();
        $definition = new PropertyDefinition(label: 'Title', source: 'name', template: null);

        $labelRenderer = $this->createMock(PropertyLabelRenderer::class);
        $labelRenderer
            ->expects(self::once())
            ->method('render')
            ->with($definition, $entity)
            ->willReturn('Rendered Label');

        $valueRenderer = $this->createMock(PropertyValueRenderer::class);
        $valueRenderer
            ->expects(self::once())
            ->method('render')
            ->with($definition, $entity)
            ->willReturn('Rendered Value');

        $renderer = new PropertyRenderer($labelRenderer, $valueRenderer);
        $result   = $renderer->render($definition, $entity);

        self::assertSame('Rendered Label', $result->getLabel());
        self::assertSame('Rendered Value', $result->getValue());
    }

    public function testRenderWrapsLabelExceptionInPropertyRenderingException(): void
    {
        $entity     = new stdClass();
        $definition = new PropertyDefinition(label: 'Title', source: null, template: null);

        $labelRenderer = $this->createStub(PropertyLabelRenderer::class);
        $labelRenderer
            ->method('render')
            ->willThrowException(new PropertyLabelRenderingException(entity: $entity, label: 'Title'));

        $renderer = new PropertyRenderer($labelRenderer, $this->createStub(PropertyValueRenderer::class));

        $this->expectException(PropertyRenderingException::class);

        $renderer->render($definition, $entity);
    }

    public function testRenderWrapsValueExceptionInPropertyRenderingException(): void
    {
        $entity     = new stdClass();
        $definition = new PropertyDefinition(label: null, source: 'field', template: null);

        $labelRenderer = $this->createStub(PropertyLabelRenderer::class);
        $labelRenderer->method('render')->willReturn('');

        $valueRenderer = $this->createStub(PropertyValueRenderer::class);
        $valueRenderer
            ->method('render')
            ->willThrowException(new \RuntimeException('Value error'));

        $renderer = new PropertyRenderer($labelRenderer, $valueRenderer);

        $this->expectException(PropertyRenderingException::class);

        $renderer->render($definition, $entity);
    }
}
