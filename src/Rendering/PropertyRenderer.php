<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\PropertyLabelRenderingException;
use Jmf\EntityRendering\Exception\PropertyRenderingException;
use Jmf\EntityRendering\Exception\PropertyValueTemplateRenderingException;
use Jmf\EntityRendering\Exception\UnexpectedValueTypeException;
use Jmf\EntityRendering\Exception\UnreadablePropertyValueException;
use Jmf\RenderingPreset\Exception\HtmlEscapingException;
use Throwable;

readonly class PropertyRenderer
{
    public function __construct(
        private PropertyLabelRenderer $propertyLabelRenderer,
        private PropertyValueRenderer $propertyValueRenderer,
    ) {
    }

    /**
     * @throws PropertyRenderingException
     */
    public function render(
        PropertyDefinition $propertyDefinition,
        object $entity,
    ): RenderedProperty {
        try {
            $label = $this->getPropertyLabel($propertyDefinition, $entity);
            $value = $this->getPropertyValue($propertyDefinition, $entity);
        } catch (Throwable $e) {
            throw new PropertyRenderingException(
                propertyDefinition: $propertyDefinition,
                entity:             $entity,
                previous:           $e,
            );
        }

        return new RenderedProperty($label, $value);
    }

    /**
     * @throws PropertyLabelRenderingException
     */
    private function getPropertyLabel(
        PropertyDefinition $propertyDefinition,
        object $entity,
    ): string {
        return $this->propertyLabelRenderer->render($propertyDefinition, $entity);
    }

    /**
     * @throws HtmlEscapingException
     * @throws PropertyValueTemplateRenderingException
     * @throws UnexpectedValueTypeException
     * @throws UnreadablePropertyValueException
     */
    private function getPropertyValue(
        PropertyDefinition $propertyDefinition,
        object $entity,
    ): string {
        return $this->propertyValueRenderer->render($propertyDefinition, $entity);
    }
}
