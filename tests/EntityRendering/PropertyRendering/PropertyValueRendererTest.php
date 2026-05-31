<?php

namespace Jmf\EntityRendering\EntityRendering\PropertyRendering;

use Jmf\EntityRendering\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exceptions\PropertyValueTemplateRenderingException;
use Jmf\EntityRendering\Exceptions\UnexpectedValueTypeException;
use Jmf\EntityRendering\Exceptions\UnreadablePropertyValueException;
use Jmf\TemplateRendering\Exception\TemplateRenderingException;
use Jmf\TemplateRendering\TemplateInterface;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stringable;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class PropertyValueRendererTest extends TestCase
{
    private TemplateRendererInterface&Stub $templateRenderer;

    private PropertyAccessor&Stub $propertyAccessor;

    private PropertyValueRenderer $renderer;

    #[Override]
    protected function setUp(): void
    {
        $this->templateRenderer = $this->createStub(TemplateRendererInterface::class);
        $this->propertyAccessor = $this->createStub(PropertyAccessor::class);
        $this->renderer         = new PropertyValueRenderer($this->templateRenderer, $this->propertyAccessor);
    }

    public function testRenderReturnsEmptyStringWhenNoSourceAndNoTemplate(): void
    {
        $definition = new PropertyDefinition(label: null, source: null, template: null);

        self::assertSame('', $this->renderer->render($definition, new stdClass()));
    }

    public function testRenderReadsValueFromSource(): void
    {
        $entity           = new stdClass();
        $propertyAccessor = $this->createMock(PropertyAccessor::class);
        $propertyAccessor
            ->expects(self::once())
            ->method('getValue')
            ->with($entity, 'name')
            ->willReturn('John')
        ;

        $renderer   = new PropertyValueRenderer($this->templateRenderer, $propertyAccessor);
        $definition = new PropertyDefinition(label: null, source: 'name', template: null);

        self::assertSame('John', $renderer->render($definition, $entity));
    }

    public function testRenderTrimsStringValue(): void
    {
        $this->propertyAccessor
            ->method('getValue')
            ->willReturn('  trimmed  ')
        ;

        $definition = new PropertyDefinition(label: null, source: 'field', template: null);

        self::assertSame('trimmed', $this->renderer->render($definition, new stdClass()));
    }

    public function testRenderConvertsNullValueToEmptyString(): void
    {
        $this->propertyAccessor
            ->method('getValue')
            ->willReturn(null)
        ;

        $definition = new PropertyDefinition(label: null, source: 'field', template: null);

        self::assertSame('', $this->renderer->render($definition, new stdClass()));
    }

    public function testRenderConvertsScalarIntValueToString(): void
    {
        $this->propertyAccessor
            ->method('getValue')
            ->willReturn(42)
        ;

        $definition = new PropertyDefinition(label: null, source: 'count', template: null);

        self::assertSame('42', $this->renderer->render($definition, new stdClass()));
    }

    public function testRenderConvertsStringableValueToString(): void
    {
        $stringable = new class implements Stringable {
            public function __toString(): string
            {
                return 'stringable result';
            }
        };

        $this->propertyAccessor
            ->method('getValue')
            ->willReturn($stringable)
        ;

        $definition = new PropertyDefinition(label: null, source: 'field', template: null);

        self::assertSame('stringable result', $this->renderer->render($definition, new stdClass()));
    }

    public function testRenderThrowsUnexpectedValueTypeExceptionForArrayValue(): void
    {
        $this->propertyAccessor
            ->method('getValue')
            ->willReturn(
                [
                    'array',
                    'value',
                ],
            )
        ;

        $definition = new PropertyDefinition(label: null, source: 'field', template: null);

        $this->expectException(UnexpectedValueTypeException::class);

        $this->renderer->render($definition, new stdClass());
    }

    public function testRenderThrowsUnreadablePropertyValueExceptionWhenPropertyAccessorFails(): void
    {
        $this->propertyAccessor
            ->method('getValue')
            ->willThrowException(new \RuntimeException('Access denied'))
        ;

        $definition = new PropertyDefinition(label: null, source: 'field', template: null);

        $this->expectException(UnreadablePropertyValueException::class);

        $this->renderer->render($definition, new stdClass());
    }

    public function testRenderCallsTemplateRenderer(): void
    {
        $entity           = new stdClass();
        $template         = $this->createStub(TemplateInterface::class);
        $templateRenderer = $this->createMock(TemplateRendererInterface::class);
        $templateRenderer
            ->expects(self::once())
            ->method('render')
            ->with(
                $template,
                [
                    '_item'  => $entity,
                    '_value' => '',
                ],
            )
            ->willReturn('rendered')
        ;

        $renderer   = new PropertyValueRenderer($templateRenderer, $this->propertyAccessor);
        $definition = new PropertyDefinition(label: null, source: null, template: $template);

        self::assertSame('rendered', $renderer->render($definition, $entity));
    }

    public function testRenderPassesSourceValueToTemplate(): void
    {
        $entity           = new stdClass();
        $template         = $this->createStub(TemplateInterface::class);
        $propertyAccessor = $this->createStub(PropertyAccessor::class);
        $propertyAccessor->method('getValue')->willReturn('raw value');

        $templateRenderer = $this->createMock(TemplateRendererInterface::class);
        $templateRenderer
            ->expects(self::once())
            ->method('render')
            ->with(
                $template,
                [
                    '_item'  => $entity,
                    '_value' => 'raw value',
                ],
            )
            ->willReturn('processed')
        ;

        $renderer   = new PropertyValueRenderer($templateRenderer, $propertyAccessor);
        $definition = new PropertyDefinition(label: null, source: 'field', template: $template);

        self::assertSame('processed', $renderer->render($definition, $entity));
    }

    public function testRenderThrowsPropertyValueTemplateRenderingExceptionWhenTemplateFails(): void
    {
        $template         = $this->createStub(TemplateInterface::class);
        $templateRenderer = $this->createStub(TemplateRendererInterface::class);
        $templateRenderer
            ->method('render')
            ->willThrowException(new TemplateRenderingException('Template error'))
        ;

        $renderer   = new PropertyValueRenderer($templateRenderer, $this->propertyAccessor);
        $definition = new PropertyDefinition(label: null, source: null, template: $template);

        $this->expectException(PropertyValueTemplateRenderingException::class);

        $renderer->render($definition, new stdClass());
    }
}
