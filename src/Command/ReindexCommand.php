<?php


namespace ATSearchBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\{Attribute\AsCommand,
    Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface
};
use ATSearchBundle\Search\Resolver\DocumentResolver;
use ATSearchBundle\Search\Service\IndexManager;

#[AsCommand(name: 'at_search:reindex')]
class ReindexCommand extends Command
{
    public function __construct(
        private readonly IndexManager $indexManager,
        private readonly EntityManagerInterface $em,
        private readonly DocumentResolver $documentResolver,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'tenant',
                't',
                InputArgument::OPTIONAL,
                'Tenant identifier',
                0
            )
            ->addOption(
                'no-purge',
                null,
                InputArgument::OPTIONAL,
                'No purge',
                0
            )
            ->addOption(
                'type',
                'c',
                InputArgument::OPTIONAL,
                'Type of content for reindex.',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $executedAt = microtime(true);

        $purge = !$input->getOption('no-purge');
        $tenantId = $input->getOption('tenant');
        $contentType = $input->getOption('type');
        if (!$contentType) {
            $output->writeln('Valid content type is required');

            return Command::FAILURE;
        }

        $entityClass = $this->documentResolver->getEntityClassNameByIndex($contentType);
        if ($contentType !== 'all' && !$entityClass) {
            $output->writeln('Valid content type is required');

            return Command::FAILURE;
        }

        $output->writeln('Reindex - start');
        if ($purge) {
            try {
                $this->indexManager->purge($tenantId, $entityClass);
            } catch (\Throwable $th) {
                $output->writeln('Purge failed');
                $output->writeln($th->getMessage());
            }
        }

        if ($contentType === 'all') {
            foreach ($this->documentResolver->getAvailableEntityClasses() as $availableEntityClass) {
                $this->reindex($availableEntityClass);
            }
        } else {
            $this->reindex($entityClass);
        }

        $output->writeln('Time - ' . (microtime(true) - $executedAt));

        return Command::SUCCESS;
    }

    private function reindex(string $entityClassName): void
    {
        $entities = $this->em->getRepository($entityClassName)->findBy([]);

        $ids = array_map(static fn(mixed $entity) => $entity->id, $entities);

        $this->indexManager->indexBulk($ids, $entityClassName);
    }
}