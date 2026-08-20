<?php

declare(strict_types=1);

namespace Jmf\EntityRendering\Repository;

use Jmf\EntityRendering\Compilation\EntityDefinitionCompiler;
use Jmf\EntityRendering\Definition\EntityDefinition;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EntityDefinitionRepositoryTest extends TestCase
{
    private EntityDefinitionCompiler&MockObject $compiler;

    private EntityDefinitionRepository $repository;

    #[Override]
    protected function setUp(): void
    {
        $this->compiler   = $this->createMock(EntityDefinitionCompiler::class);
        $this->repository = new EntityDefinitionRepository($this->compiler);
    }

    public function testGetCompilesOnFirstCall(): void
    {
        $entity     = new stdClass();
        $definition = new EntityDefinition([]);

        $this->compiler
            ->expects(self::once())
            ->method('compile')
            ->with($entity)
            ->willReturn($definition)
        ;

        $result = $this->repository->get($entity);

        self::assertSame($definition, $result);
    }

    public function testGetMemoizesOnSecondCallForSameClass(): void
    {
        $entity1    = new stdClass();
        $entity2    = new stdClass();
        $definition = new EntityDefinition([]);

        $this->compiler
            ->expects(self::once())
            ->method('compile')
            ->willReturn($definition)
        ;

        $this->repository->get($entity1);
        $result = $this->repository->get($entity2);

        self::assertSame($definition, $result);
    }

    public function testGetCompilesSeparatelyForDifferentClasses(): void
    {
        $entity1     = new stdClass();
        $entity2     = new class {
        };
        $definition1 = new EntityDefinition([]);
        $definition2 = new EntityDefinition([]);

        $this->compiler
            ->expects(self::exactly(2))
            ->method('compile')
            ->willReturnOnConsecutiveCalls($definition1, $definition2)
        ;

        $result1 = $this->repository->get($entity1);
        $result2 = $this->repository->get($entity2);

        self::assertSame($definition1, $result1);
        self::assertSame($definition2, $result2);
    }
}
