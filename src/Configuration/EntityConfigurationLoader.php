<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Configuration;

use Jmf\CrudEngine\Configuration\EntityPathConfig;
use Jmf\EntityRendering\Exception\DuplicateEntityException;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * Owns the `entities` side of the bundle configuration: assembles the final map from the per-entity
 * files discovered under the configured `paths` (filename + base namespace = FQCN) merged with the
 * inline `entities`, and registers a cache-invalidation resource per directory. A class defined more
 * than once (across files, or against an inline entry) is a configuration error.
 */
final readonly class EntityConfigurationLoader
{
    /**
     * @param array<string, mixed> $config         resolved `jmf_entity_rendering` config (`paths` + `entities`)
     * @param string               $extensionAlias used to derive the default path
     *
     * @return array<string, array<string, mixed>> Entity configs keyed by FQCN
     *
     * @throws DuplicateEntityException
     */
    public function load(
        array $config,
        ContainerBuilder $container,
        string $extensionAlias,
    ): array {
        $pathConfigs = $this->resolvePathConfigs(
            $config,
            $container,
            $extensionAlias,
        );

        $this->registerResources(
            $pathConfigs,
            $container,
        );

        $entitiesFromPaths = $this->loadFromPaths($pathConfigs);

        Assert::keyExists($config, 'entities');
        /** @var array<class-string, array<string, mixed>> $inlineEntities */
        $inlineEntities = $config['entities'];
        Assert::isMap($inlineEntities);
        Assert::allIsMap($inlineEntities);

        $this->detectDuplicates(
            $entitiesFromPaths,
            $inlineEntities,
        );

        return array_merge(
            $entitiesFromPaths,
            $inlineEntities,
        );
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return EntityPathConfig[]
     */
    private function resolvePathConfigs(
        array $config,
        ContainerBuilder $container,
        string $extensionAlias,
    ): iterable {
        Assert::keyExists($config, 'paths');
        $pathConfigs = $config['paths'];
        Assert::isArray($pathConfigs);

        if ([] === $pathConfigs) {
            $pathConfigs = [
                new EntityPathConfig(
                    path:      '%.kernel.config_dir%/packages/' . $extensionAlias,
                    namespace: 'App\\Entity',
                ),
            ];
        }

        $resolved = [];

        foreach ($pathConfigs as $pathConfig) {
            Assert::isArray($pathConfig);
            Assert::stringNotEmpty($pathConfig['path']);
            Assert::stringNotEmpty($pathConfig['namespace']);

            $path = $container->getParameterBag()->resolveValue($pathConfig['path']);
            Assert::stringNotEmpty($path);

            $resolved[] = new EntityPathConfig(
                path:      $path,
                namespace: $pathConfig['namespace'],
            );
        }

        return $resolved;
    }

    /**
     * @param EntityPathConfig[] $pathConfigs
     */
    private function registerResources(
        iterable $pathConfigs,
        ContainerBuilder $container,
    ): void {
        foreach ($pathConfigs as $pathConfig) {
            if (is_dir($pathConfig->path)) {
                $container->addResource(
                    new DirectoryResource(
                        $pathConfig->path,
                        '/\.yaml$/',
                    ),
                );
            }
        }
    }

    /**
     * @param EntityPathConfig[] $pathConfigs
     *
     * @return array<class-string, array<string, mixed>>
     *
     * @throws DuplicateEntityException
     */
    private function loadFromPaths(iterable $pathConfigs): array
    {
        $entities = [];

        foreach ($pathConfigs as $pathConfig) {
            $directory = $pathConfig->path;

            if (!is_dir($directory)) {
                continue;
            }

            $namespace = trim($pathConfig->namespace, '\\');

            foreach ((new Finder())->files()->in($directory)->name('*.yaml')->sortByName() as $file) {
                $relativeName = substr($file->getRelativePathname(), 0, -strlen('.yaml'));
                $class        = str_replace('/', '\\', $relativeName);
                $entityClass  = '' !== $namespace ? "{$namespace}\\{$class}" : $class;
                Assert::stringNotEmpty($entityClass);
                Assert::classExists($entityClass);

                if (isset($entities[$entityClass])) {
                    throw new DuplicateEntityException([$entityClass]);
                }

                $parsed = Yaml::parseFile($file->getRealPath(), Yaml::PARSE_CONSTANT);
                Assert::isMap($parsed);

                $entities[$entityClass] = $parsed;
            }
        }

        return $entities;
    }

    /**
     * @param array<class-string, mixed> $entitiesFromPaths
     * @param array<class-string, mixed> $inlineEntities
     *
     * @throws DuplicateEntityException
     */
    private function detectDuplicates(
        array $entitiesFromPaths,
        mixed $inlineEntities,
    ): void {
        $duplicates = array_intersect_key(
            $entitiesFromPaths,
            $inlineEntities,
        );

        if ([] !== $duplicates) {
            $duplicateTypes = array_keys($duplicates);
            Assert::allStringNotEmpty($duplicateTypes);

            throw new DuplicateEntityException($duplicateTypes);
        }
    }
}
