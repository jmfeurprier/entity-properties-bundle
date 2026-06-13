<?php

namespace Jmf\EntityRendering\Compilation;

use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\RenderingPreset\Preset\Preset;
use Jmf\RenderingPreset\Preset\PresetRepositoryInterface;
use Jmf\RenderingPreset\Preset\Property\PresetProperty;
use Jmf\RenderingPreset\Preset\Property\PresetPropertyCollection;
use Jmf\TemplateRendering\TemplateInterface;
use PHPUnit\Framework\TestCase;

class PresetsApplierTest extends TestCase
{
    public function testApplyWithNoPresetIdReturnsDefinitionUnchanged(): void
    {
        $repository = $this->createMock(PresetRepositoryInterface::class);
        $repository->expects(self::never())->method('get');

        $applier    = new PresetsApplier($repository);
        $definition = new PropertyDefinition(label: 'Label', source: 'field', template: null);

        $result = $applier->apply($definition);

        self::assertSame($definition, $result);
    }

    public function testApplyWithPresetMergesPresetSourceAndTemplate(): void
    {
        $template = $this->createStub(TemplateInterface::class);
        $preset   = new Preset(
            id:         'my_preset',
            source:     'preset_source',
            template:   $template,
            properties: new PresetPropertyCollection([]),
        );

        $repository = $this->createStub(PresetRepositoryInterface::class);
        $repository->method('get')->willReturn($preset);

        $applier    = new PresetsApplier($repository);
        $definition = new PropertyDefinition(label: null, source: null, template: null, presetId: 'my_preset');

        $result = $applier->apply($definition);

        self::assertNotSame($definition, $result);
        self::assertNull($result->getLabel());
        self::assertSame('preset_source', $result->getSource());
        self::assertSame($template, $result->getTemplate());
    }

    public function testApplyDefinitionPropertiesTakePrecedenceOverPreset(): void
    {
        $presetTemplate     = $this->createStub(TemplateInterface::class);
        $definitionTemplate = $this->createStub(TemplateInterface::class);

        $preset = new Preset(
            id:         'my_preset',
            source:     'preset_source',
            template:   $presetTemplate,
            properties: new PresetPropertyCollection([]),
        );

        $repository = $this->createStub(PresetRepositoryInterface::class);
        $repository->method('get')->willReturn($preset);

        $applier    = new PresetsApplier($repository);
        $definition = new PropertyDefinition(
            label:    'My Label',
            source:   'definition_source',
            template: $definitionTemplate,
            presetId: 'my_preset',
        );

        $result = $applier->apply($definition);

        self::assertSame('My Label', $result->getLabel());
        self::assertSame('definition_source', $result->getSource());
        self::assertSame($definitionTemplate, $result->getTemplate());
    }

    public function testApplyUsesPresetLabelWhenDefinitionHasNoLabel(): void
    {
        $preset = new Preset(
            id:         'my_preset',
            source:     null,
            template:   null,
            properties: new PresetPropertyCollection([
                new PresetProperty('label', 'Preset Label'),
            ]),
        );

        $repository = $this->createStub(PresetRepositoryInterface::class);
        $repository->method('get')->willReturn($preset);

        $applier    = new PresetsApplier($repository);
        $definition = new PropertyDefinition(label: null, source: null, template: null, presetId: 'my_preset');

        $result = $applier->apply($definition);

        self::assertSame('Preset Label', $result->getLabel());
    }
}
