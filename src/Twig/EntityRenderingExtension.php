<?php

namespace Jmf\EntityRendering\Twig;

use Jmf\EntityRendering\EntityRendering\EntityRenderer;
use Jmf\EntityRendering\EntityRendering\RenderedEntity;
use Jmf\EntityRendering\Exceptions\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exceptions\PresetNotFoundException;
use Jmf\EntityRendering\Exceptions\PropertyRenderingException;
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
     */
    public function getRenderedEntity(
        object $entity,
    ): RenderedEntity {
        return $this->entityRenderer->render($entity);
    }
}
