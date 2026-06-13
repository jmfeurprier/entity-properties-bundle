<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Exception;

use Throwable;

class UnreadablePropertyValueException extends PropertyValueRenderingException
{
    public function __construct(
        private readonly object $entity,
        private readonly string $source,
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
            "Failed reading property value for entity of type %s (source: %s).",
            $this->entity::class,
            $this->source,
        );
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getSource(): string
    {
        return $this->source;
    }
}
