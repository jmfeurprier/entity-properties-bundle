<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\EntityRendering\Definition;

use Jmf\EntityRendering\EntityRendering\Preset\PresetsApplier;
use Jmf\RenderingPreset\Exception\InvalidConfigurationException;
use Jmf\RenderingPreset\Exception\PresetNotFoundException;
use Jmf\TemplateRendering\StringTemplate;
use Webmozart\Assert\Assert;

readonly class PropertyDefinitionResolver
{
    public function __construct(
        private PresetsApplier $presetsApplier,
    ) {
    }

    /**
     * @param array<string, mixed> $propertyConfiguration
     *
     * @throws InvalidConfigurationException
     * @throws PresetNotFoundException
     */
    public function resolve(array $propertyConfiguration): PropertyDefinition
    {
        $template = null;
        $string   = $propertyConfiguration['template'] ?? null;

        if (null !== $string) {
            Assert::stringNotEmpty($string);

            // @todo
            $template = new StringTemplate($string);
        }

        $propertyDefinition = new PropertyDefinition(
            label:    $this->getOptionalString($propertyConfiguration, 'label'),
            source:   $this->getOptionalString($propertyConfiguration, 'source'),
            template: $template,
            presetId: $this->getOptionalString($propertyConfiguration, 'preset'),
        );

        return $this->presetsApplier->apply($propertyDefinition);
    }

    /**
     * @param array<string, mixed> $propertyConfiguration
     * @param non-empty-string     $key
     *
     * @return null|non-empty-string
     */
    private function getOptionalString(
        array $propertyConfiguration,
        string $key,
    ): ?string {
        $string = $propertyConfiguration[$key] ?? null;

        if (null === $string) {
            return null;
        }

        Assert::stringNotEmpty($string);

        return $string;
    }
}
