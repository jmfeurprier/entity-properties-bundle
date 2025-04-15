<?php

namespace Jmf\EntityRendering\EntityRendering\Definition;

readonly class PropertyDefinition
{
    public function __construct(
        private ?string $label,
        private ?string $source,
        private ?string $template,
        private ?string $presetId,
    ) {
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getPresetId(): ?string
    {
        return $this->presetId;
    }
}
