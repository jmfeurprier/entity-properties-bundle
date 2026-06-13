<?php

namespace Jmf\EntityRendering\Definition;

use Jmf\ClassList\ClassesResolverInterface;
use Jmf\EntityRendering\Exception\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exception\EntityRenderingException;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class EntityDefinitionResolverTest extends TestCase
{
    private PropertyDefinitionResolver&Stub $propertyDefinitionResolver;

    private ClassesResolverInterface&Stub $classesResolver;

    #[Override]
    protected function setUp(): void
    {
        $this->propertyDefinitionResolver = $this->createStub(PropertyDefinitionResolver::class);
        $this->classesResolver            = $this->createStub(ClassesResolverInterface::class);
    }

    public function testResolveReturnsEntityDefinitionWithPropertyDefinitions(): void
    {
        $entity = new stdClass();

        $this->classesResolver
            ->method('resolveForObject')
            ->willReturn([stdClass::class])
        ;

        $this->propertyDefinitionResolver
            ->method('resolve')
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

        $entityDefinition = $this->whenResolve($entityConfigurations, $entity);

        $propertyDefinitions = iterator_to_array($entityDefinition->getPropertyDefinitions());

        self::assertCount(2, $propertyDefinitions);
        self::assertSame('Name', $propertyDefinitions[0]->getLabel());
        self::assertSame('Age', $propertyDefinitions[1]->getLabel());
    }

    public function testResolveThrowsEntityConfigurationNotFoundExceptionForUnknownEntity(): void
    {
        $entity = new stdClass();

        $this->classesResolver
            ->method('resolveForObject')
            ->willReturn([stdClass::class])
        ;

        $this->expectException(EntityConfigurationNotFoundException::class);

        $this->whenResolve([], $entity);
    }

    public function testResolveThrowsEntityRenderingExceptionWhenPropertyDefinitionFails(): void
    {
        $entity = new stdClass();

        $this->classesResolver
            ->method('resolveForObject')
            ->willReturn([stdClass::class])
        ;

        $this->propertyDefinitionResolver
            ->method('resolve')
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

        $this->expectException(EntityRenderingException::class);

        $this->whenResolve($entityConfigurations, $entity);
    }

    public function testResolveUsesFirstMatchingClassFromHierarchy(): void
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

        $this->propertyDefinitionResolver
            ->method('resolve')
            ->willReturn(new PropertyDefinition(label: null, source: null, template: null))
        ;

        $entityConfigurations = [
            stdClass::class => [
                'properties' => [
                    ['source' => 'field'],
                ],
            ],     ];

        $definition = $this->whenResolve($entityConfigurations, $entity);

        self::assertCount(1, iterator_to_array($definition->getPropertyDefinitions()));
    }

    /**
     * @param array<class-string, array<string, mixed>> $entityConfigurations
     */
    private function whenResolve(
        array $entityConfigurations,
        object $entity,
    ): EntityDefinition {
        $resolver   = new EntityDefinitionResolver(
            $this->propertyDefinitionResolver,
            $this->classesResolver,
            $entityConfigurations,
        );

        return $resolver->resolve($entity);
    }
}
