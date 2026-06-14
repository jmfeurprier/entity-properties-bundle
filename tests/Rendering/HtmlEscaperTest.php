<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Rendering;

use Jmf\RenderingPreset\Exception\HtmlEscapingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class HtmlEscaperTest extends TestCase
{
    private TemplateRendererInterface&MockObject $templateRenderer;

    private HtmlEscaper $htmlEscaper;

    #[Override]
    protected function setUp(): void
    {
        $this->templateRenderer = $this->createMock(TemplateRendererInterface::class);
        $this->htmlEscaper      = new HtmlEscaper($this->templateRenderer);
    }

    public function testEscapeRendersValueWithTwigAutoEscape(): void
    {
        $this->templateRenderer
            ->expects(self::once())
            ->method('renderFromString')
            ->with('{{ _value }}', ['_value' => 'hello'])
            ->willReturn('hello')
        ;

        $result = $this->htmlEscaper->escape('hello');

        self::assertSame('hello', $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testEscapeThrowsHtmlEscapingExceptionWhenRendererFails(): void
    {
        $this->templateRenderer
            ->method('renderFromString')
            ->willThrowException(new RuntimeException('render failed'))
        ;

        $this->expectException(HtmlEscapingException::class);

        $this->htmlEscaper->escape('hello');
    }
}
