<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Exceptions;

use Exception;
use Jmf\EntityRendering\EntityRendering\Definition\PropertyDefinition;
use Throwable;

class PropertyRenderingException extends Exception
{
    public function __construct(
        private readonly PropertyDefinition $propertyDefinition,
        private readonly object $entity,
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
            'Failed resolving property for entity of type: %s.',
            $this->entity::class,
        );
    }

    public function getPropertyDefinition(): PropertyDefinition
    {
        return $this->propertyDefinition;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
