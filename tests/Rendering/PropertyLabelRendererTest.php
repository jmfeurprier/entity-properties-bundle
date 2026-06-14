<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\PropertyLabelRenderingException;
use Jmf\TemplateRendering\Exception\TemplateRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class PropertyLabelRendererTest extends TestCase
{
    public function testRenderReturnsEmptyStringWhenLabelIsNull(): void
    {
        $templateRenderer = $this->createMock(TemplateRendererInterface::class);
        $templateRenderer->expects(self::never())->method('renderFromString');

        $renderer = new PropertyLabelRenderer($templateRenderer);

        $definition = new PropertyDefinition(label: null, source: null, template: null);

        self::assertSame('', $renderer->render($definition, new stdClass()));
    }

    public function testRenderDelegatesToTemplateRendererWhenLabelIsSet(): void
    {
        $entity = new stdClass();
        $label  = 'My {{ _item|class }}';

        $templateRenderer = $this->createMock(TemplateRendererInterface::class);
        $templateRenderer
            ->expects(self::once())
            ->method('renderFromString')
            ->with($label, ['_item' => $entity])
            ->willReturn('Rendered Label');

        $renderer   = new PropertyLabelRenderer($templateRenderer);
        $definition = new PropertyDefinition(label: $label, source: null, template: null);

        self::assertSame('Rendered Label', $renderer->render($definition, $entity));
    }

    public function testRenderWrapsTemplateExceptionInPropertyLabelRenderingException(): void
    {
        $entity = new stdClass();
        $label  = 'Some Label';

        $templateRenderer = $this->createStub(TemplateRendererInterface::class);
        $templateRenderer
            ->method('renderFromString')
            ->willThrowException(new TemplateRenderingException('Template failed'));

        $renderer   = new PropertyLabelRenderer($templateRenderer);
        $definition = new PropertyDefinition(label: $label, source: null, template: null);

        $this->expectException(PropertyLabelRenderingException::class);

        $renderer->render($definition, $entity);
    }
}
