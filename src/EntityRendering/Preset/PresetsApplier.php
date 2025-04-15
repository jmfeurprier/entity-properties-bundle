<?php

namespace Jmf\EntityRendering\EntityRendering\Preset;

use Jmf\EntityRendering\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exceptions\PresetNotFoundException;

readonly class PresetsApplier
{
    public function __construct(
        private PresetDefinitionRepository $definitionRepository,
    ) {
    }

    /**
     * @throws PresetNotFoundException
     */
    public function apply(PropertyDefinition $propertyDefinition): PropertyDefinition
    {
        $presetId = $propertyDefinition->getPresetId();

        if (null === $presetId) {
            return $propertyDefinition;
        }

        $presetDefinition = $this->definitionRepository->get($presetId);

        $newPropertyDefinition = new PropertyDefinition(
            label:    $propertyDefinition->getLabel() ?? $presetDefinition->getLabel(),
            source:   $propertyDefinition->getSource() ?? $presetDefinition->getSource(),
            template: $propertyDefinition->getTemplate() ?? $presetDefinition->getTemplate(),
            presetId: $presetDefinition->getPresetId(),
        );

        return $this->apply($newPropertyDefinition);
    }
}
