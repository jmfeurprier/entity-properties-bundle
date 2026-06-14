<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Repository;

use Jmf\EntityRendering\Compilation\EntityDefinitionCompiler;
use Jmf\EntityRendering\Definition\EntityDefinition;
use Jmf\EntityRendering\Exception\EntityConfigurationNotFoundException;
use Jmf\EntityRendering\Exception\PropertyDefinitionCompilationException;

class EntityDefinitionRepository
{
    /** @var array<class-string, EntityDefinition> */
    private array $cache = [];

    public function __construct(
        private readonly EntityDefinitionCompiler $compiler,
    ) {
    }

    /**
     * @throws EntityConfigurationNotFoundException
     * @throws PropertyDefinitionCompilationException
     */
    public function get(object $entity): EntityDefinition
    {
        $class = $entity::class;

        if (!array_key_exists($class, $this->cache)) {
            $this->cache[$class] = $this->compiler->compile($entity);
        }

        return $this->cache[$class];
    }
}
