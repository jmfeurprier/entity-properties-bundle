<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Configuration;

use Jmf\EntityRendering\Definition\EntityDefinition;
use Jmf\EntityRendering\Definition\PropertyDefinition;
use Jmf\EntityRendering\Exception\DuplicateEntityException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EntityConfigurationLoaderTest extends TestCase
{
    private const string NAMESPACE    = 'Jmf\\EntityRendering\\Definition';
    private const string FIXTURES_DIR = __DIR__ . '/fixtures';

    private ContainerBuilder $container;

    private EntityConfigurationLoader $loader;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->loader    = new EntityConfigurationLoader();
    }

    public function testLoadReturnsEmptyArrayWhenNoFilesAndNoInlineEntities(): void
    {
        $config = [
            'paths'    => [['path' => '/nonexistent/path', 'namespace' => self::NAMESPACE]],
            'entities' => [],
        ];

        self::assertSame([], $this->loader->load($config, $this->container, 'test'));
    }

    public function testLoadReturnsInlineEntitiesWhenNoPathFiles(): void
    {
        $config = [
            'paths'    => [['path' => '/nonexistent/path', 'namespace' => self::NAMESPACE]],
            'entities' => [
                EntityDefinition::class => ['properties' => []],
            ],
        ];

        $result = $this->loader->load($config, $this->container, 'test');

        self::assertSame([EntityDefinition::class => ['properties' => []]], $result);
    }

    public function testLoadReadsEntityFromYamlFile(): void
    {
        $config = [
            'paths'    => [['path' => self::FIXTURES_DIR, 'namespace' => self::NAMESPACE]],
            'entities' => [],
        ];

        $result = $this->loader->load($config, $this->container, 'test');

        self::assertSame([EntityDefinition::class => ['properties' => []]], $result);
    }

    public function testLoadMergesPathEntitiesWithInlineEntities(): void
    {
        $config = [
            'paths'    => [['path' => self::FIXTURES_DIR, 'namespace' => self::NAMESPACE]],
            'entities' => [
                PropertyDefinition::class => ['properties' => []],
            ],
        ];

        $result = $this->loader->load($config, $this->container, 'test');

        self::assertArrayHasKey(EntityDefinition::class, $result);
        self::assertArrayHasKey(PropertyDefinition::class, $result);
    }

    public function testLoadThrowsDuplicateEntityExceptionForClassInBothPathAndInline(): void
    {
        $config = [
            'paths'    => [['path' => self::FIXTURES_DIR, 'namespace' => self::NAMESPACE]],
            'entities' => [
                EntityDefinition::class => ['properties' => []],
            ],
        ];

        $this->expectException(DuplicateEntityException::class);

        $this->loader->load($config, $this->container, 'test');
    }

    public function testLoadThrowsDuplicateEntityExceptionForSameClassAcrossTwoPaths(): void
    {
        $config = [
            'paths' => [
                ['path' => self::FIXTURES_DIR, 'namespace' => self::NAMESPACE],
                ['path' => self::FIXTURES_DIR, 'namespace' => self::NAMESPACE],
            ],
            'entities' => [],
        ];

        $this->expectException(DuplicateEntityException::class);

        $this->loader->load($config, $this->container, 'test');
    }
}
