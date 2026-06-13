<?php

namespace Jmf\EntityRendering\EntityRendering\Definition;

use Jmf\EntityRendering\EntityRendering\Preset\PresetsApplier;
use Jmf\RenderingPreset\Preset\PresetRepositoryInterface;
use Override;
use PHPUnit\Framework\TestCase;

class PropertyDefinitionResolverTest extends TestCase
{
    private PresetsApplier $presetsApplier;

    private PropertyDefinitionResolver $resolver;

    #[Override]
    protected function setUp(): void
    {
        $repository = $this->createStub(PresetRepositoryInterface::class);

        $this->presetsApplier = new PresetsApplier($repository);
        $this->resolver       = new PropertyDefinitionResolver($this->presetsApplier);
    }

    public function testResolveWithAllFields(): void
    {
        $config = [
            'label'    => 'My Label',
            'source'   => 'myProperty',
            'template' => '{{ _value|upper }}',
        ];

        $definition = $this->resolver->resolve($config);

        self::assertSame('My Label', $definition->getLabel());
        self::assertSame('myProperty', $definition->getSource());
        self::assertNotNull($definition->getTemplate());
    }

    public function testResolveWithEmptyConfigReturnsNullFields(): void
    {
        $definition = $this->resolver->resolve([]);

        self::assertNull($definition->getLabel());
        self::assertNull($definition->getSource());
        self::assertNull($definition->getTemplate());
        self::assertNull($definition->getPresetId());
    }

    public function testResolveWithLabelOnly(): void
    {
        $definition = $this->resolver->resolve(['label' => 'Only Label']);

        self::assertSame('Only Label', $definition->getLabel());
        self::assertNull($definition->getSource());
        self::assertNull($definition->getTemplate());
    }

    public function testResolveWithSourceOnly(): void
    {
        $definition = $this->resolver->resolve(['source' => 'someField']);

        self::assertNull($definition->getLabel());
        self::assertSame('someField', $definition->getSource());
        self::assertNull($definition->getTemplate());
    }
}
