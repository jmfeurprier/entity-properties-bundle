<?php

declare(strict_types=1);

namespace Jmf\EntityRendering;

use Jmf\EntityRendering\Configuration\EntityConfigurationLoader;
use Jmf\EntityRendering\Exception\DuplicateEntityException;
use Override;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class JmfEntityRenderingBundle extends AbstractBundle
{
    /**
     * @const array<string, string>
     */
    private const array PARAMETERS_MAPPING = [
        'entities'              => 'entity_configurations',
        'presets'               => 'preset_configurations',
        'template_path'         => 'template_path',
        'twig_functions_prefix' => 'twig_functions_prefix',
    ];

    protected string $extensionAlias = 'jmf_entity_rendering';

    public function __construct(
        private readonly EntityConfigurationLoader $entityConfigurationLoader = new EntityConfigurationLoader(),
    ) {
    }

    #[Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws DuplicateEntityException
     */
    #[Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->import('../config/services.yaml');

        $config['entities'] = $this->entityConfigurationLoader->load($config, $builder, $this->extensionAlias);

        $this->loadParameters($config, $container);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function loadParameters(
        array $config,
        ContainerConfigurator $container,
    ): void {
        foreach (self::PARAMETERS_MAPPING as $configKey => $property) {
            $container->parameters()->set(
                "{$this->extensionAlias}.{$property}",
                $config[$configKey],
            );
        }
    }
}
