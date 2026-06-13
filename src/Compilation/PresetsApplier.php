<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Compilation;

use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\RenderingPreset\Exception\InvalidConfigurationException;
use Jmf\RenderingPreset\Exception\PresetNotFoundException;
use Jmf\RenderingPreset\Preset\Preset;
use Jmf\RenderingPreset\Preset\PresetRepositoryInterface;
use Webmozart\Assert\Assert;

readonly class PresetsApplier
{
    public function __construct(
        private PresetRepositoryInterface $presetRepository,
    ) {
    }

    /**
     * @throws InvalidConfigurationException
     * @throws PresetNotFoundException
     */
    public function apply(PropertyDefinition $propertyDefinition): PropertyDefinition
    {
        $preset = $this->getPreset($propertyDefinition);

        if (null === $preset) {
            return $propertyDefinition;
        }

        return new PropertyDefinition(
            label:    $propertyDefinition->getLabel() ?? $this->getPresetLabel($preset),
            source:   $propertyDefinition->getSource() ?? $preset->getSource(),
            template: $propertyDefinition->getTemplate() ?? $preset->getTemplate(),
        );
    }

    /**
     * @throws InvalidConfigurationException
     * @throws PresetNotFoundException
     */
    private function getPreset(PropertyDefinition $propertyDefinition): ?Preset
    {
        $presetId = $propertyDefinition->getPresetId();

        if (null === $presetId) {
            return null;
        }

        return $this->presetRepository->get($presetId);
    }

    private function getPresetLabel(Preset $preset): ?string
    {
        $properties  = $preset->getProperties();
        $presetLabel = $properties->tryGetValue('label');

        Assert::nullOrString($presetLabel);

        return $presetLabel;
    }
}
