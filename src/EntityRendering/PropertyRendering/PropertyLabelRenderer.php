<?php

namespace Jmf\EntityRendering\EntityRendering\PropertyRendering;

use Jmf\EntityRendering\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exceptions\PropertyLabelRenderingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Throwable;

readonly class PropertyLabelRenderer
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer,
    ) {
    }

    /**
     * @throws PropertyLabelRenderingException
     */
    public function render(
        PropertyDefinition $propertyDefinition,
        object $entity,
    ): string {
        $label = $propertyDefinition->getLabel();

        if (null === $label) {
            return '';
        }

        try {
            return $this->templateRenderer->renderFromString(
                $label,
                [
                    '_item' => $entity,
                ],
            );
        } catch (Throwable $e) {
            throw new PropertyLabelRenderingException(
                entity:   $entity,
                label:    $label,
                previous: $e,
            );
        }
    }
}
