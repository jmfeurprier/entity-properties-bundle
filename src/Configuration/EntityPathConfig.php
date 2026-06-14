<?php

declare(strict_types=1);

namespace Jmf\CrudEngine\Configuration;

readonly class EntityPathConfig
{
    /**
     * @param non-empty-string $path
     * @param non-empty-string $namespace
     */
    public function __construct(
        public string $path,
        public string $namespace,
    ) {
    }
}
