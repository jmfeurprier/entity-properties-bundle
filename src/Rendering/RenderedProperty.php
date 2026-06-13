<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Rendering;

readonly class RenderedProperty
{
    public function __construct(
        private string $label,
        private string $value,
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
