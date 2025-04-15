<?php

namespace Jmf\EntityRendering\EntityRendering\Preset;

use Jmf\EntityRendering\Exceptions\PresetNotFoundException;
use Webmozart\Assert\Assert;

readonly class PresetDefinitionRepository
{
    /**
     * @param array<string, array<string, mixed>> $presetConfigurations
     */
    public function __construct(
        private array $presetConfigurations,
    ) {
    }

    /**
     * @throws PresetNotFoundException
     */
    public function get(string $presetId): PresetDefinition
    {
        if (!array_key_exists($presetId, $this->presetConfigurations)) {
            throw new PresetNotFoundException($presetId);
        }

        $presetConfiguration = $this->presetConfigurations[$presetId];

        return new PresetDefinition(
            label:    $this->getOptionalString($presetConfiguration, 'label'),
            source:   $this->getOptionalString($presetConfiguration, 'source'),
            template: $this->getOptionalString($presetConfiguration, 'template'),
            presetId: $this->getOptionalString($presetConfiguration, 'preset'),
        );
    }

    /**
     * @param array<string, mixed> $presetConfiguration
     * @param non-empty-string     $key
     *
     * @return null|non-empty-string
     */
    private function getOptionalString(
        array $presetConfiguration,
        string $key,
    ): ?string {
        $string = $presetConfiguration[$key] ?? null;

        if (null === $string) {
            return null;
        }

        Assert::stringNotEmpty($string);

        return $string;
    }
}
