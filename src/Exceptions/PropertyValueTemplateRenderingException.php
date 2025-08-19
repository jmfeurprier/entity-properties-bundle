<?php

namespace Jmf\EntityRendering\Exceptions;

use Jmf\TemplateRendering\TemplateInterface;
use Throwable;

class PropertyValueTemplateRenderingException extends PropertyValueRenderingException
{
    public function __construct(
        private readonly object $entity,
        private readonly TemplateInterface $template,
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
            "Failed rendering property template for entity of type %s.",
            $this->entity::class,
        );
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getTemplate(): TemplateInterface
    {
        return $this->template;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
