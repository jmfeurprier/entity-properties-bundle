<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Rendering;

use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\HtmlEscapingException;
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
        private HtmlEscaper $htmlEscaper,
    ) {
    }

    /**
     * @throws HtmlEscapingException
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

        if (null !== $template) {
            return $this->renderWithTemplate($template, $entity, $source);
        }

        if (null === $source) {
            return '';
        }

        return $this->htmlEscaper->escape(
            trim(
                $this->readSourceAsString(
                    $entity,
                    $source,
                ),
            ),
        );
    }

    /**
     * @throws PropertyValueTemplateRenderingException
     * @throws UnreadablePropertyValueException
     */
    private function renderWithTemplate(
        TemplateInterface $template,
        object $entity,
        ?string $source,
    ): string {
        $sourceValue = '';

        if (null !== $source) {
            $sourceValue = $this->getEntityValue($entity, $source);
        }

        try {
            return $this->templateRenderer->render(
                $template,
                [
                    '_item'  => $entity,
                    '_value' => $sourceValue,
                ],
            );
        } catch (Throwable $e) {
            throw new PropertyValueTemplateRenderingException(
                entity:   $entity,
                template: $template,
                value:    $sourceValue,
                previous: $e,
            );
        }
    }

    /**
     * @throws UnexpectedValueTypeException
     * @throws UnreadablePropertyValueException
     */
    private function readSourceAsString(
        object $entity,
        string $source,
    ): string {
        $value = $this->getEntityValue($entity, $source);

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if (null === $value) {
            return '';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        throw new UnexpectedValueTypeException(
            entity:   $entity,
            source:   $source,
            template: null,
            value:    $value,
        );
    }

    /**
     * @throws UnreadablePropertyValueException
     */
    private function getEntityValue(
        object $entity,
        string $source,
    ): mixed {
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
}
