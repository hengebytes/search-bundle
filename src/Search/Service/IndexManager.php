<?php

namespace ATSearchBundle\Search\Service;

use OpenSearch\Client;
use ATSearchBundle\Search\ValueObject\Document;

readonly class IndexManager
{
    public function __construct(private Client $client, private DocumentGenerator $documentGenerator)
    {
    }

    public function purge(int|string|null $tenantId, ?string $entityName): void
    {
        $index = Document::$indexPrefix;
        if ($tenantId) {
            $index .= $tenantId . '_';
        } else {
            $index .= '*_';
        }
        if ($entityName) {
            $index .= $this->documentGenerator->getIndexName($entityName);
        } else {
            $index .= '*';
        }

        $this->client->indices()->delete(['index' => $index]);
    }

    public function index(int $id, string $entityName): void
    {
        $document = $this->documentGenerator->generateDocument($id, $entityName);
        $this->processSingleDocument($document);
    }

    public function indexBulk(array $ids, string $entityName): void
    {
        $params = ['body' => []];
        foreach ($ids as $key => $id) {
            $document = $this->documentGenerator->generateDocument($id, $entityName);

            $params = $this->handleDocument($document, $params, $key);
        }

        if (!empty($params['body'])) {
            $this->client->bulk($params);
        }

        if ($ids) {
            $this->client->indices()->refresh(['index' => Document::$indexPrefix . '*']);
        }
    }

    private function handleDocument(Document $document, array $params, int|string $key): array
    {
        if (!$document->body) {
            $actionBody = [
                'delete' => [
                    '_index' => $document->getIndex(),
                    '_id' => $document->id,
                ],
            ];
        } else {
            $actionBody = [
                'index' => [
                    '_index' => $document->getIndex(),
                    '_id' => $document->id,
                ],
            ];
        }

        $params['body'][] = $actionBody;
        $params['body'][] = $document->body;

        if ($key % 1000 === 0) {
            $this->client->bulk($params);

            // erase the old bulk request
            $params = ['body' => []];
        }

        return $params;
    }

    private function processSingleDocument(Document $document): void
    {
        $index = $document->getIndex();
        if (!$document->body) {
            $this->client->deleteByQuery([
                'index' => $index,
                'body' => [
                    'query' => [
                        'match' => [
                            '_id' => $document->id,
                        ],
                    ],
                ],
            ]);
        } else {
            $this->client->index([
                'index' => $index,
                'id' => $document->id,
                'body' => $document->body,
            ]);
        }
        $this->client->indices()->refresh(['index' => $index]);
    }
}