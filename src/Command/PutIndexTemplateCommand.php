<?php


namespace ATSearchBundle\Command;

use ATSearchBundle\Search\Mapper\SchemaMapper;
use ATSearchBundle\Search\ValueObject\Document;
use Elastic\Elasticsearch\Client as ElasticClient;
use Exception;
use OpenSearch\Client as OpenSearchClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'at_search:elastic:put_index_template')]
class PutIndexTemplateCommand extends Command
{
    public function __construct(private readonly OpenSearchClient|ElasticClient $client)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Put index template - start');

        $maxWindow = (int)$io->ask(
            'Please specify `max_result_window` setting for the template', 10000
        );

        $alreadyExists = false;

        try {
            $request = $this->buildRequest(false, $maxWindow);
            $this->client->indices()->putIndexTemplate($request);
        } catch (Exception $e) {
            if (!str_contains($e->getMessage(), 'already exists') && !str_contains($e->getMessage(), 'default')) {
                $io->error('Put index template - error');
            } else {
                $alreadyExists = true;
                $io->warning('Index template already exists');
            }
        }

        if ($alreadyExists && $io->confirm('Index template already exists, overwrite?', false)) {
            $io->warning('Index template already exists, overwriting');
            $request = $this->buildRequest(true, $maxWindow);
            $this->client->indices()->putIndexTemplate($request);
            $io->success('Index template overwritten');
        }

        $maxResultWindow = $this->client->indices()->getSettings([
            'index' => Document::$indexPrefix . '*',
        ]);

        foreach ($maxResultWindow as $key => $value) {
            $io->writeln('Current max_result_window setting for ' . $key . ': ' . ($value['settings']['index']['max_result_window'] ?? 'not set (default: 10000))'));
        }

        if (!$io->confirm('Do you want to update max_result_window setting for all indices to ' . $maxWindow . '?', false)) {
            return Command::SUCCESS;
        }

        try {
            $this->client->indices()->putSettings([
                'index' => Document::$indexPrefix . '*',
                'body' => [
                    'max_result_window' => $maxWindow,
                ],
            ]);
            $io->success('Max result window setting updated.');
        } catch (Exception $e) {
            $io->error('Error updating max_result_window setting.' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function buildRequest(bool $overwrite, int $maxWindow): array
    {
        $dynamicTemplates = [];
        foreach (SchemaMapper::getAvailableFieldTypes() as $name => $type) {
            $element = [
                'ats_' . $name => [
                    'match' => '*' . SchemaMapper::getSuffixByCustomType($name),
                    'mapping' => [
                        'type' => $type,
                    ],
                ],
            ];
            if (in_array($name, ['string', 'mstring'])) {
                $element['ats_' . $name]['mapping']['normalizer'] = 'lowercase_normalizer';
            }
            $dynamicTemplates[] = $element;
        }

        return [
            'name' => 'default',
            'create' => !$overwrite,
            'body' => [
                'index_patterns' => [
                    Document::$indexPrefix . '*',
                ],
                'template' => [
                    'settings' => [
                        'max_result_window' => $maxWindow,
                        'analysis' => [
                            'normalizer' => [
                                'lowercase_normalizer' => [
                                    'type' => 'custom',
                                    'char_filter' => [],
                                    'filter' => [
                                        'lowercase',
                                    ],
                                ],
                            ],
                        ],
                        'refresh_interval' => '-1',
                    ],
                    'mappings' => [
                        'dynamic_templates' => $dynamicTemplates,
                    ],
                ],
            ],
        ];
    }

}