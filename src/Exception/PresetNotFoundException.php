<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Exception;

use Throwable;

class PresetNotFoundException extends EntityRenderingException
{
    public function __construct(
        private readonly string $presetId,
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
            "Requested preset '%s' was not found.",
            $this->presetId,
        );
    }

    public function getPresetId(): string
    {
        return $this->presetId;
    }
}
