<?php

namespace Jmf\EntityRendering\Exceptions;

use Throwable;

class PropertyValueTemplateRenderingException extends PropertyValueRenderingException
{
    public function __construct(
        private readonly object $entity,
        private readonly string $template,
        private readonly mixed $value,
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
            "Failed rendering property template for entity of type %s (template: %s).",
            $this->entity::class,
            $this->template,
        );
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
