<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Exception;

use Throwable;

class PropertyLabelRenderingException extends EntityRenderingException
{
    public function __construct(
        private readonly object $entity,
        private readonly string $label,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message:  $this->buildMessage(),
            previous: $previous,
        );
    }

    private function buildMessage(): string
    {
        return sprintf(
            "Failed rendering property value for entity of type %s (label: %s).",
            $this->entity::class,
            $this->label,
        );
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
