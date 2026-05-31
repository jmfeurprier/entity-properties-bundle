<?php

namespace Jmf\EntityRendering\EntityRendering\Definition;

use Jmf\TemplateRendering\TemplateInterface;
use PHPUnit\Framework\TestCase;

class PropertyDefinitionTest extends TestCase
{
    public function testGettersReturnConstructorValues(): void
    {
        $template = $this->createStub(TemplateInterface::class);

        $definition = new PropertyDefinition(
            label:    'My Label',
            source:   'myProperty',
            template: $template,
            presetId: 'my_preset',
        );

        self::assertSame('My Label', $definition->getLabel());
        self::assertSame('myProperty', $definition->getSource());
        self::assertSame($template, $definition->getTemplate());
        self::assertSame('my_preset', $definition->getPresetId());
    }

    public function testNullValues(): void
    {
        $definition = new PropertyDefinition(
            label:    null,
            source:   null,
            template: null,
        );

        self::assertNull($definition->getLabel());
        self::assertNull($definition->getSource());
        self::assertNull($definition->getTemplate());
        self::assertNull($definition->getPresetId());
    }

    public function testPresetIdDefaultsToNull(): void
    {
        $definition = new PropertyDefinition(
            label:    'label',
            source:   'source',
            template: null,
        );

        self::assertNull($definition->getPresetId());
    }
}
