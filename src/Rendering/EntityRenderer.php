<?php

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Compilation\EntityDefinitionCompiler;
use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exception\EntityRenderingException;
use Jmf\EntityRendering\Exception\PresetNotFoundException;
use Jmf\EntityRendering\Exception\PropertyRenderingException;

readonly class EntityRenderer
{
    public function __construct(
        private EntityDefinitionCompiler $entityDefinitionCompiler,
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
        return $this->entityDefinitionCompiler->compile($entity)->getPropertyDefinitions();
    }
}
