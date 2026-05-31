<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\EntityRendering\PropertyRendering;

use Jmf\EntityRendering\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\EntityRendering\RenderedProperty;
use Jmf\EntityRendering\Exceptions\PropertyLabelRenderingException;
use Jmf\EntityRendering\Exceptions\PropertyRenderingException;
use Jmf\EntityRendering\Exceptions\PropertyValueTemplateRenderingException;
use Jmf\EntityRendering\Exceptions\UnexpectedValueTypeException;
use Jmf\EntityRendering\Exceptions\UnreadablePropertyValueException;
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
