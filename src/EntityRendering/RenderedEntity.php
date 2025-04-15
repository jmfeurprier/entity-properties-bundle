<?php

namespace Jmf\EntityRendering\EntityRendering;

use Webmozart\Assert\Assert;

readonly class RenderedEntity
{
    /**
     * @param RenderedProperty[] $renderedProperties
     */
    public function __construct(
        private iterable $renderedProperties,
    ) {
        Assert::allIsInstanceOf($renderedProperties, RenderedProperty::class);
    }

    /**
     * @return RenderedProperty[]
     */
    public function getRenderedProperties(): iterable
    {
        return $this->renderedProperties;
    }
}
