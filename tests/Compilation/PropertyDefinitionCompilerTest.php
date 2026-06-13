<?php

namespace Jmf\EntityRendering\Compilation;

use Jmf\RenderingPreset\Preset\PresetRepositoryInterface;
use Override;
use PHPUnit\Framework\TestCase;

class PropertyDefinitionCompilerTest extends TestCase
{
    private PresetsApplier $presetsApplier;

    private PropertyDefinitionCompiler $compiler;

    #[Override]
    protected function setUp(): void
    {
        $repository = $this->createStub(PresetRepositoryInterface::class);

        $this->presetsApplier = new PresetsApplier($repository);
        $this->compiler       = new PropertyDefinitionCompiler($this->presetsApplier);
    }

    public function testCompileWithAllFields(): void
    {
        $config = [
            'label'    => 'My Label',
            'source'   => 'myProperty',
            'template' => '{{ _value|upper }}',
        ];

        $definition = $this->compiler->compile($config);

        self::assertSame('My Label', $definition->getLabel());
        self::assertSame('myProperty', $definition->getSource());
        self::assertNotNull($definition->getTemplate());
    }

    public function testCompileWithEmptyConfigReturnsNullFields(): void
    {
        $definition = $this->compiler->compile([]);

        self::assertNull($definition->getLabel());
        self::assertNull($definition->getSource());
        self::assertNull($definition->getTemplate());
        self::assertNull($definition->getPresetId());
    }

    public function testCompileWithLabelOnly(): void
    {
        $definition = $this->compiler->compile(['label' => 'Only Label']);

        self::assertSame('Only Label', $definition->getLabel());
        self::assertNull($definition->getSource());
        self::assertNull($definition->getTemplate());
    }

    public function testCompileWithSourceOnly(): void
    {
        $definition = $this->compiler->compile(['source' => 'someField']);

        self::assertNull($definition->getLabel());
        self::assertSame('someField', $definition->getSource());
        self::assertNull($definition->getTemplate());
    }
}
