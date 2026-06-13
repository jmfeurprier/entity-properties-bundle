<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Exception;

class DuplicateEntityException extends EntityRenderingException
{
    /**
     * @param non-empty-list<non-empty-string> $entityTypes
     */
    public function __construct(
        private readonly array $entityTypes,
    ) {
        parent::__construct(
            sprintf(
                'Duplicate entity configuration for %s.',
                implode(', ', $this->entityTypes),
            ),
        );
    }

    /**
     * @return non-empty-list<string>
     */
    public function getEntityTypes(): array
    {
        return $this->entityTypes;
    }
}
