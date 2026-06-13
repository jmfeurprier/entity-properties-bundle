<?php

namespace Jmf\EntityRendering\Compilation;

use Jmf\ClassList\ClassesResolverInterface;
use Jmf\EntityRendering\Definition\EntityDefinition;
use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exception\PropertyDefinitionCompilationException;
use Throwable;
use Webmozart\Assert\Assert;

readonly class EntityDefinitionCompiler
{
    /**
     * @param array<class-string, array<string, mixed>> $entityConfigurations
     */
    public function __construct(
        private PropertyDefinitionCompiler $propertyDefinitionCompiler,
        private ClassesResolverInterface $classesResolver,
        private array $entityConfigurations,
    ) {
    }

    /**
     * @throws EntityConfigurationNotFoundException
     * @throws PropertyDefinitionCompilationException
     */
    public function compile(object $entity): EntityDefinition
    {
        $propertyDefinitions = [];

        foreach ($this->getPropertyConfigurations($entity) as $propertyConfiguration) {
            $propertyDefinitions[] = $this->getPropertyDefinition($propertyConfiguration);
        }

        return new EntityDefinition($propertyDefinitions);
    }

    /**
     * @return array<string, mixed>[]
     *
     * @throws EntityConfigurationNotFoundException
     */
    private function getPropertyConfigurations(object $entity): iterable
    {
        $entityConfiguration = $this->getEntityConfiguration($entity);

        Assert::keyExists($entityConfiguration, 'properties');

        $propertyConfigurations = $entityConfiguration['properties'];

        Assert::isIterable($propertyConfigurations);
        Assert::allIsMap($propertyConfigurations);

        return $propertyConfigurations;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws EntityConfigurationNotFoundException
     */
    private function getEntityConfiguration(object $entity): array
    {
        foreach ($this->classesResolver->resolveForObject($entity) as $class) {
            if (array_key_exists($class, $this->entityConfigurations)) {
                $entityConfig = $this->entityConfigurations[$class];

                Assert::isMap($entityConfig);

                return $entityConfig;
            }
        }

        throw new EntityConfigurationNotFoundException(entity: $entity);
    }

    /**
     * @param array<string, mixed> $propertyConfiguration
     *
     * @throws PropertyDefinitionCompilationException
     */
    private function getPropertyDefinition(array $propertyConfiguration): PropertyDefinition
    {
        try {
            return $this->propertyDefinitionCompiler->compile($propertyConfiguration);
        } catch (Throwable $e) {
            throw new PropertyDefinitionCompilationException(
                message:  $e->getMessage(),
                code:     $e->getCode(),
                previous: $e,
            );
        }
    }
}
