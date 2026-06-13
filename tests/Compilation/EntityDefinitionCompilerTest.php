<?php

namespace Jmf\EntityRendering\Compilation;

use Jmf\ClassList\ClassesResolverInterface;
use Jmf\EntityRendering\Definition\EntityDefinition;
use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exception\PropertyDefinitionCompilationException;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class EntityDefinitionCompilerTest extends TestCase
{
    private PropertyDefinitionCompiler&Stub $propertyDefinitionCompiler;

    private ClassesResolverInterface&Stub $classesResolver;

    #[Override]
    protected function setUp(): void
    {
        $this->propertyDefinitionCompiler = $this->createStub(PropertyDefinitionCompiler::class);
        $this->classesResolver            = $this->createStub(ClassesResolverInterface::class);
    }

    public function testCompileReturnsEntityDefinitionWithPropertyDefinitions(): void
    {
        $entity = new stdClass();

        $this->classesResolver
            ->method('resolveForObject')
            ->willReturn([stdClass::class])
        ;

        $this->propertyDefinitionCompiler
            ->method('compile')
            ->willReturnCallback(
                function (
                    array $config,
                ): PropertyDefinition {
                    /** @var array{label?: string, source?: string} $config */
                    return new PropertyDefinition(
                        label:    $config['label'] ?? null,
                        source:   $config['source'] ?? null,
                        template: null,
                    );
                },
            )
        ;

        $entityConfigurations = [
            stdClass::class => [
                'properties' => [
                    [
                        'label'  => 'Name',
                        'source' => 'name',
                    ],
                    [
                        'label'  => 'Age',
                        'source' => 'age',
                    ],
                ],
            ],
        ];

        $entityDefinition = $this->whenCompile($entityConfigurations, $entity);

        $propertyDefinitions = iterator_to_array($entityDefinition->getPropertyDefinitions());

        self::assertCount(2, $propertyDefinitions);
        self::assertSame('Name', $propertyDefinitions[0]->getLabel());
        self::assertSame('Age', $propertyDefinitions[1]->getLabel());
    }

    public function testCompileThrowsEntityConfigurationNotFoundExceptionForUnknownEntity(): void
    {
        $entity = new stdClass();

        $this->classesResolver
            ->method('resolveForObject')
            ->willReturn([stdClass::class])
        ;

        $this->expectException(EntityConfigurationNotFoundException::class);

        $this->whenCompile([], $entity);
    }

    public function testCompileThrowsPropertyDefinitionCompilationExceptionWhenPropertyDefinitionFails(): void
    {
        $entity = new stdClass();

        $this->classesResolver
            ->method('resolveForObject')
            ->willReturn([stdClass::class])
        ;

        $this->propertyDefinitionCompiler
            ->method('compile')
            ->willThrowException(new RuntimeException('Definition error'))
        ;

        $entityConfigurations = [
            stdClass::class => [
                'properties' => [
                    [
                        'label'  => 'Name',
                        'source' => 'name',
                    ],
                ],
            ],
        ];

        $this->expectException(PropertyDefinitionCompilationException::class);

        $this->whenCompile($entityConfigurations, $entity);
    }

    public function testCompileUsesFirstMatchingClassFromHierarchy(): void
    {
        $entity = new stdClass();

        $this->classesResolver
            ->method('resolveForObject')
            ->willReturn(
                [
                    'NonExistentParent',
                    stdClass::class,
                ],
            )
        ;

        $this->propertyDefinitionCompiler
            ->method('compile')
            ->willReturn(new PropertyDefinition(label: null, source: null, template: null))
        ;

        $entityConfigurations = [
            stdClass::class => [
                'properties' => [
                    ['source' => 'field'],
                ],
            ],     ];

        $definition = $this->whenCompile($entityConfigurations, $entity);

        self::assertCount(1, iterator_to_array($definition->getPropertyDefinitions()));
    }

    /**
     * @param array<class-string, array<string, mixed>> $entityConfigurations
     */
    private function whenCompile(
        array $entityConfigurations,
        object $entity,
    ): EntityDefinition {
        $compiler = new EntityDefinitionCompiler(
            $this->propertyDefinitionCompiler,
            $this->classesResolver,
            $entityConfigurations,
        );

        return $compiler->compile($entity);
    }
}
