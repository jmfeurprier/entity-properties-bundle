<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\PropertyValueTemplateRenderingException;
use Jmf\EntityRendering\Exception\UnexpectedValueTypeException;
use Jmf\EntityRendering\Exception\UnreadablePropertyValueException;
use Jmf\TemplateRendering\TemplateInterface;
use Jmf\TemplateRendering\TemplateRendererInterface;
use Stringable;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Throwable;

readonly class PropertyValueRenderer
{
    public function __construct(
        private TemplateRendererInterface $templateRenderer,
        private PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    /**
     * @throws PropertyValueTemplateRenderingException
     * @throws UnexpectedValueTypeException
     * @throws UnreadablePropertyValueException
     */
    public function render(
        PropertyDefinition $propertyDefinition,
        object $entity,
    ): string {
        $source   = $propertyDefinition->getSource();
        $template = $propertyDefinition->getTemplate();

        if ((null === $source) && (null === $template)) {
            return '';
        }

        $sourceValue = $this->tryGetValueFromSource($entity, $source);
        $templateValue = $this->tryGetValueFromTemplate($template, $entity, $sourceValue);

        // @todo HTML-escape value?

        if ($value instanceof Stringable) {
            $value = (string) $value;
        } elseif (null === $value) {
            $value = '';
        } elseif (is_scalar($value)) {
            $value = (string) $value;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueTypeException(
                entity:   $entity,
                source:   $source,
                template: $template,
                value:    $value,
            );
        }

        return trim($value);
    }

    /**
     * @throws UnreadablePropertyValueException
     */
    private function tryGetValueFromSource(
        object $entity,
        ?string $source,
    ): mixed {
        if (null === $source) {
            return '';
        }

        try {
            return $this->propertyAccessor->getValue($entity, $source);
        } catch (Throwable $e) {
            throw new UnreadablePropertyValueException(
                entity:   $entity,
                source:   $source,
                previous: $e,
            );
        }
    }

    /**
     * @throws PropertyValueTemplateRenderingException
     */
    private function tryGetValueFromTemplate(
        ?TemplateInterface $template,
        object $entity,
        mixed $value,
    ): mixed {
        if (null === $template) {
            return $value;
        }

        try {
            return $this->templateRenderer->render(
                $template,
                [
                    '_item'  => $entity,
                    '_value' => $value,
                ],
            );
        } catch (Throwable $e) {
            throw new PropertyValueTemplateRenderingException(
                entity:   $entity,
                template: $template,
                value:    $value,
                previous: $e,
            );
        }
    }
}
