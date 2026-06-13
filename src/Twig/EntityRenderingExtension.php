<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Twig;

use Jmf\EntityRendering\Rendering\EntityRenderer;
use Jmf\EntityRendering\Rendering\RenderedEntity;
use Jmf\EntityRendering\Exception\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exception\EntityRenderingException;
use Jmf\EntityRendering\Exception\PresetNotFoundException;
use Jmf\EntityRendering\Exception\PropertyRenderingException;
use Jmf\TemplateRendering\Exception\TemplateRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @psalm-type Context array<string, mixed>
 */
class EntityRenderingExtension extends AbstractExtension
{
    public final const string PREFIX_DEFAULT = '';

    public function __construct(
        private readonly EntityRenderer $entityRenderer,
        private readonly TemplateRendererInterface $templateRenderer,
        private readonly string $templatePath,
        private readonly string $prefix = self::PREFIX_DEFAULT,
    ) {
    }

    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                "{$this->prefix}entity_render",
                $this->renderEntity(...),
                [
                    'is_safe' => ['html'],
                ],
            ),
            new TwigFunction(
                "{$this->prefix}entity_render_get",
                $this->getRenderedEntity(...),
                [
                    'is_safe' => ['html'],
                ],
            ),
        ];
    }

    /**
     * @throws EntityConfigurationNotFoundException
     * @throws PresetNotFoundException
     * @throws PropertyRenderingException
     * @throws TemplateRenderingException
     * @throws EntityRenderingException
     */
    public function renderEntity(
        object $entity,
    ): string {
        return $this->templateRenderer->renderFromFile(
            $this->templatePath,
            [
                'renderedEntity' => $this->entityRenderer->render($entity),
            ],
        );
    }

    /**
     * @throws EntityConfigurationNotFoundException
     * @throws PresetNotFoundException
     * @throws PropertyRenderingException
     * @throws EntityRenderingException
     */
    public function getRenderedEntity(
        object $entity,
    ): RenderedEntity {
        return $this->entityRenderer->render($entity);
    }
}
