<?php

namespace ATernovtsii\SearchBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use ATernovtsii\SearchBundle\Elastic\Generator\IndexDocumentBuilder;
use ATernovtsii\SearchBundle\Elastic\Generator\IndexDocumentMetadataGenerator;

class ATSearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->loadConfigFiles($container);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('at_search.elastic.enabled', $config['elastic']['enabled']);
        if (!$config['elastic']['enabled']) {
            $container->removeAlias('at_search.cache_compiler');

            return;
        }

        $this->loadIndexDocumentGenerator($container, $config['elastic']['mappings']);
    }

    private function loadConfigFiles(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('aliases.yaml');
    }

    private function loadIndexDocumentGenerator(ContainerBuilder $container, $mappings): void
    {
        $definition = $container->getDefinition(IndexDocumentMetadataGenerator::class);
        $definition->setArgument(2, $mappings);
        $definition->setArgument(3, $container->getParameter('kernel.cache_dir'));
        $definition->setArgument(4, $container->getParameter('at_search.elastic.enabled'));

        $indexDocumentMetadataGenerator = new IndexDocumentMetadataGenerator(
            new Filesystem(),
            new IndexDocumentBuilder(),
            $mappings,
            $container->getParameter('kernel.cache_dir')
        );

        $generatedClasses = $indexDocumentMetadataGenerator->compile(true);
        foreach ($generatedClasses as $class => $file) {
            $definition = $container->register($class);
            $definition->setPublic(false);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);
            $definition->addTag('at_search.elastic.index_document');
        }
    }

}
