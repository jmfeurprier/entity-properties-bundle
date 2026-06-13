<?php

namespace Jmf\EntityRendering\EntityRendering;

use Jmf\EntityRendering\EntityRendering\Definition\EntityDefinitionResolver;
use Jmf\EntityRendering\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\EntityRendering\PropertyRendering\PropertyRenderer;
use Jmf\EntityRendering\Exception\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exception\EntityRenderingException;
use Jmf\EntityRendering\Exception\PresetNotFoundException;
use Jmf\EntityRendering\Exception\PropertyRenderingException;

readonly class EntityRenderer
{
    public function __construct(
        private EntityDefinitionResolver $entityDefinitionResolver,
        private PropertyRenderer $propertyRenderer,
    ) {
    }

    /**
     * @throws EntityConfigurationNotFoundException
     * @throws PropertyRenderingException
     * @throws PresetNotFoundException
     * @throws EntityRenderingException
     */
    public function render(object $entity): RenderedEntity
    {
        $renderedProperties = [];

        foreach ($this->getPropertyDefinitions($entity) as $propertyDefinition) {
            $renderedProperties[] = $this->propertyRenderer->render(
                $propertyDefinition,
                $entity,
            );
        }

        return new RenderedEntity($renderedProperties);
    }

    /**
     * @return PropertyDefinition[]
     *
     * @throws EntityConfigurationNotFoundException
     * @throws EntityRenderingException
     */
    private function getPropertyDefinitions(object $entity): iterable
    {
        return $this->entityDefinitionResolver->resolve($entity)->getPropertyDefinitions();
    }
}
