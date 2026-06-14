<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Twig;

use Jmf\EntityRendering\Rendering\EntityRenderer;
use Jmf\EntityRendering\Rendering\RenderedEntity;
use Jmf\EntityRendering\Rendering\RenderedProperty;
use Jmf\TemplateRendering\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use stdClass;
use Twig\TwigFunction;

final class EntityRenderingExtensionTest extends TestCase
{
    public function testGetFunctionsReturnsTwoFunctions(): void
    {
        $extension = new EntityRenderingExtension(
            $this->createStub(EntityRenderer::class),
            $this->createStub(TemplateRendererInterface::class),
            'template.html.twig',
        );

        $functions = $extension->getFunctions();

        self::assertCount(2, $functions);
        self::assertContainsOnlyInstancesOf(TwigFunction::class, $functions);
    }

    public function testGetFunctionNamesUseDefaultPrefix(): void
    {
        $extension = new EntityRenderingExtension(
            $this->createStub(EntityRenderer::class),
            $this->createStub(TemplateRendererInterface::class),
            'template.html.twig',
        );

        $functions = $extension->getFunctions();

        $names = array_map(fn(
            TwigFunction $f,
        ): string => $f->getName(), $functions);

        self::assertContains('entity_render', $names);
        self::assertContains('entity_render_get', $names);
    }

    public function testGetFunctionNamesUseCustomPrefix(): void
    {
        $extension = new EntityRenderingExtension(
            $this->createStub(EntityRenderer::class),
            $this->createStub(TemplateRendererInterface::class),
            'template.html.twig',
            'foo_',
        );
        $functions = $extension->getFunctions();

        $names = array_map(fn(
            TwigFunction $f,
        ): string => $f->getName(), $functions);

        self::assertContains('foo_entity_render', $names);
        self::assertContains('foo_entity_render_get', $names);
    }

    public function testRenderEntityCallsEntityRendererAndTemplateRenderer(): void
    {
        $entity         = new stdClass();
        $renderedEntity = new RenderedEntity([new RenderedProperty('label', 'value')]);

        $entityRenderer = $this->createMock(EntityRenderer::class);
        $entityRenderer
            ->expects(self::once())
            ->method('render')
            ->with($entity)
            ->willReturn($renderedEntity)
        ;

        $templateRenderer = $this->createMock(TemplateRendererInterface::class);
        $templateRenderer
            ->expects(self::once())
            ->method('renderFromFile')
            ->with('my_template.html.twig', ['renderedEntity' => $renderedEntity])
            ->willReturn('<div>rendered</div>')
        ;

        $extension = new EntityRenderingExtension($entityRenderer, $templateRenderer, 'my_template.html.twig');

        self::assertSame('<div>rendered</div>', $extension->renderEntity($entity));
    }

    public function testGetRenderedEntityReturnsRenderedEntity(): void
    {
        $entity         = new stdClass();
        $renderedEntity = new RenderedEntity([]);

        $entityRenderer = $this->createMock(EntityRenderer::class);
        $entityRenderer
            ->expects(self::once())
            ->method('render')
            ->with($entity)
            ->willReturn($renderedEntity)
        ;

        $extension = new EntityRenderingExtension(
            $entityRenderer,
            $this->createStub(TemplateRendererInterface::class),
            'template.html.twig',
        );

        self::assertSame($renderedEntity, $extension->getRenderedEntity($entity));
    }
}
