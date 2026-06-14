<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Repository\EntityDefinitionRepository;
use Jmf\EntityRendering\Definition\EntityDefinition;
use Jmf\EntityRendering\Exception\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exception\PresetNotFoundException;
use Jmf\EntityRendering\Exception\PropertyDefinitionCompilationException;
use Jmf\EntityRendering\Exception\PropertyRenderingException;

readonly class EntityRenderer
{
    public function __construct(
        private EntityDefinitionRepository $entityDefinitionRepository,
        private PropertyRenderer $propertyRenderer,
    ) {
    }

    /**
     * @throws EntityConfigurationNotFoundException
     * @throws PresetNotFoundException
     * @throws PropertyRenderingException
     * @throws PropertyDefinitionCompilationException
     */
    public function render(object $entity): RenderedEntity
    {
        $renderedProperties = [];

        foreach ($this->getEntityDefinition($entity)->getPropertyDefinitions() as $propertyDefinition) {
            $renderedProperties[] = $this->propertyRenderer->render(
                $propertyDefinition,
                $entity,
            );
        }

        return new RenderedEntity($renderedProperties);
    }

    /**
     * @throws EntityConfigurationNotFoundException
     * @throws PropertyDefinitionCompilationException
     */
    private function getEntityDefinition(object $entity): EntityDefinition
    {
        return $this->entityDefinitionRepository->get($entity);
    }
}
