<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Exception;

use Throwable;

class HtmlEscapingException extends EntityRenderingException
{
    public function __construct(
        private readonly string $value,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message:  'Failed HTML-escaping provided value.',
            previous: $previous,
        );
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
