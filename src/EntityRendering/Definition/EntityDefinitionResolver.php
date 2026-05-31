<?php

namespace Jmf\EntityRendering\EntityRendering\Definition;

use Jmf\ClassList\ClassesResolverInterface;
use Jmf\EntityRendering\Exceptions\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exceptions\EntityRenderingException;
use Throwable;
use Webmozart\Assert\Assert;

readonly class EntityDefinitionResolver
{
    /**
     * @param array<class-string, array<string, mixed>> $entityConfigurations
     */
    public function __construct(
        private PropertyDefinitionResolver $propertyDefinitionResolver,
        private ClassesResolverInterface $classesResolver,
        private array $entityConfigurations,
    ) {
    }

    /**
     * @throws EntityConfigurationNotFoundException
     * @throws EntityRenderingException
     */
    public function resolve(object $entity): EntityDefinition
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
     * @throws EntityRenderingException
     */
    private function getPropertyDefinition(array $propertyConfiguration): PropertyDefinition
    {
        try {
            return $this->propertyDefinitionResolver->resolve($propertyConfiguration);
        } catch (Throwable $e) {
            // @todo
            throw new EntityRenderingException(message: $e->getMessage(), code: $e->getCode(), previous: $e);
        }
    }
}
