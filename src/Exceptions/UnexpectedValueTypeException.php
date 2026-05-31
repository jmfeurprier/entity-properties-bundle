<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Exceptions;

use Jmf\TemplateRendering\TemplateInterface;
use Throwable;

class UnexpectedValueTypeException extends PropertyValueRenderingException
{
    public function __construct(
        private readonly object $entity,
        private readonly ?string $source,
        private readonly ?TemplateInterface $template,
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
        $tokens = [
            $this->entity::class,
        ];

        $details  = [
            'type: %s',
        ];
        $tokens[] = gettype($this->value);

        if (null !== $this->source) {
            $details[] = 'source: %s';
            $tokens[]  = $this->source;
        }

        $detailsString = implode(', ', $details);

        return vsprintf(
            "Failed rendering property value for entity of type %s: unexpected value type ({$detailsString}).",
            $tokens,
        );
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getTemplate(): ?TemplateInterface
    {
        return $this->template;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
