<?php

namespace ATSearchBundle\Elastic\Handler;

use OpenSearch\Client;
use OpenSearch\Common\Exceptions\{ClientErrorResponseException, ServerErrorResponseException};
use ATSearchBundle\Elastic\ValueObject\Document;
use ATSearchBundle\Elastic\ValueObject\Query;
use ATSearchBundle\ValueObject\Result;

readonly class SearchHandler
{
    public function __construct(private Client $searchClient)
    {
    }

    public function search(Query $query): Result
    {
        $index = Document::$indexPrefix . $query->tenantId . '_' . $query->indexName;

        $params = [
            'index' => $index,
            'body' => [
                'query' => ['bool' => ['must' => $query->filters]],
                'from' => $query->from,
                'size' => $query->size,
                'sort' => $query->sort,
            ],
            '_source' => $query->size > 0 ? $query->returnSource : false,
        ];
        if ($query->withCount) {
            $params['body']['track_total_hits'] = true;
        }
        if (isset($query->sort['_score'])) {
            $params['body']['track_scores'] = true;
        }
        if (!empty($query->suggest)) {
            $params['body']['suggest'] = $query->suggest;
        }

        if ($query->returnSource && $query->sourceIncludes) {
            $params['_source_includes'] = $query->sourceIncludes;
        }

        $totalCount = 0;
        $data = [];
        try {
            $ESResponse = $this->searchClient->search($params);
            $totalCount = $ESResponse['hits']['total']['value'];
            if ($query->returnSource) {
                $data = array_map(static fn($hit) => $hit['_source'], $ESResponse['hits']['hits']);
            }
        } catch (ClientErrorResponseException|ServerErrorResponseException) {
        }

        return new Result(
            $totalCount,
            $data,
        );
    }
}