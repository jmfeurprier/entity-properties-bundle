<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Exception\HtmlEscapingException;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Throwable;

readonly class HtmlEscaper
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer,
    ) {
    }

    /**
     * @throws HtmlEscapingException
     */
    public function escape(
        string $value,
    ): string {
        try {
            return $this->templateRenderer->renderFromString(
                '{{ _value }}',
                [
                    '_value' => $value,
                ],
            );
        } catch (Throwable $e) {
            throw new HtmlEscapingException(
                value:    $value,
                previous: $e,
            );
        }
    }
}
