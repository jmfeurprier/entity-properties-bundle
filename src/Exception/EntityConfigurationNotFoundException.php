<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Exception;

use Exception;

class EntityConfigurationNotFoundException extends Exception
{
    public function __construct(
        private readonly object $entity,
    ) {
        parent::__construct(
            message: $this->buildMessage(),
        );
    }

    private function buildMessage(): string
    {
        return sprintf(
            'No configuration found for entity of type: %s.',
            $this->entity::class,
        );
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
