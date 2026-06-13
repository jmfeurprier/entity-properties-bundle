<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Definition;

use Jmf\TemplateRendering\TemplateInterface;

readonly class PropertyDefinition
{
    public function __construct(
        private ?string $label,
        private ?string $source,
        private ?TemplateInterface $template,
        private ?string $presetId = null,
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

    public function getTemplate(): ?TemplateInterface
    {
        return $this->template;
    }

    public function getPresetId(): ?string
    {
        return $this->presetId;
    }
}
