<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Definition;

use Webmozart\Assert\Assert;

readonly class EntityDefinition
{
    /**
     * @param PropertyDefinition[] $propertyDefinitions
     */
    public function __construct(
        private iterable $propertyDefinitions,
    ) {
        Assert::allIsInstanceOf($propertyDefinitions, PropertyDefinition::class);
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getPropertyDefinitions(): iterable
    {
        return $this->propertyDefinitions;
    }
}
