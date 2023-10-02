<?php

namespace ATSearchBundle\DependencyInjection;

use ATSearchBundle\Command\PutIndexTemplateCommand;
use ATSearchBundle\Search\EventListener\DoctrineEventListener;
use ATSearchBundle\Search\Generator\IndexDocumentBuilder;
use ATSearchBundle\Search\Generator\IndexDocumentMetadataGenerator;
use ATSearchBundle\Search\Handler\SearchHandler;
use ATSearchBundle\Search\Service\IndexManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        $container->setParameter('at_search.search.enabled', $config['search']['enabled']);
        if (!$config['search']['enabled']) {
            $container->removeAlias('at_search.cache_compiler');

            return;
        }

        $this->loadSearchServicesFiles($container);
        $this->loadIndexDocumentGenerator($container, $config['search']['mappings']);

        if (!$config['search']['enable_update_events']) {
            $this->removeSearchIndexerListener($container);
        }
        $this->setClient($container, $config['search']['client']);
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
        $definition->setArgument(4, $container->getParameter('at_search.search.enabled'));

        $indexDocumentMetadataGenerator = new IndexDocumentMetadataGenerator(
            new Filesystem(),
            new IndexDocumentBuilder(),
            $mappings,
            $container->getParameter('kernel.cache_dir')
        );

        $generatedClasses = $indexDocumentMetadataGenerator->compileClassesForTags();
        foreach ($generatedClasses as $class => $priority) {
            $definition = $container->register($class);
            $definition->setPublic(false);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);
            $definition->addTag('at_search.search.index_document', ['priority' => $priority]);
        }
    }

    private function loadSearchServicesFiles(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/search'));
        $loader->load('services.yaml');
    }

    private function removeSearchIndexerListener(ContainerBuilder $container): void
    {
        $container->removeDefinition(DoctrineEventListener::class);
    }

    private function setClient(ContainerBuilder $container, string $client): void
    {
        $clientReference = new Reference($client);
        $container->getDefinition(SearchHandler::class)->setArgument(0, $clientReference);
        $container->getDefinition(IndexManager::class)->setArgument(0, $clientReference);
        $container->getDefinition(PutIndexTemplateCommand::class)->setArgument(0, $clientReference);
    }

}
