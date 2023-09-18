<?php

namespace ATSearchBundle\Doctrine;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use ATSearchBundle\Doctrine\Converter\InputQueryToDoctrineQueryFilters;
use ATSearchBundle\Query\SearchQuery;
use ATSearchBundle\Query\SortClause\SortByField;
use ATSearchBundle\Query\SortClause\SortByRelationField;

class QueryToRepositoryQuery implements LocaleAwareInterface
{
    private string $locale;

    public function __construct(private readonly InputQueryToDoctrineQueryFilters $inputQueryToDoctrineFilter)
    {
    }

    public function getDoctrineQuery(SearchQuery $query, QueryBuilder $queryBuilder): Query
    {
        $alias = $queryBuilder->getRootAliases()[0] ?? 'unknown';

        $joinAwareQB = new JoinAwareQueryBuilder($queryBuilder, [], $alias);

        if ($query->filters) {
            $queryBuilder->where(
                $this->inputQueryToDoctrineFilter->convert($query->filters, $joinAwareQB)
            );
        }

        $joinTables = $joinAwareQB->joins;
        $this->addSorts($query, $queryBuilder, $joinTables, $alias);

        $queryBuilder->setMaxResults($query->limit);
        $queryBuilder->setFirstResult($query->offset);

        $this->joinTables($joinTables, $queryBuilder);

        $queryBuilder->groupBy($alias . '.id');

        return $queryBuilder->getQuery();
    }

    public function getDoctrineQueryForCount(SearchQuery $query, QueryBuilder $queryBuilder): Query
    {
        $alias = $queryBuilder->getRootAliases()[0] ?? 'unknown';
        $queryBuilder->select('COUNT(DISTINCT(' . $alias . '.id))');

        $joinAwareQB = new JoinAwareQueryBuilder($queryBuilder, [], $alias);

        if ($query->filters) {
            $queryBuilder->where(
                $this->inputQueryToDoctrineFilter->convert($query->filters, $joinAwareQB)
            );
        }

        $joinTables = $joinAwareQB->joins;
        $this->joinTables($joinTables, $queryBuilder);

        return $queryBuilder->getQuery();
    }

    private function addSorts(SearchQuery $query, QueryBuilder $qb, array &$joinTables, string $alias): void
    {
        foreach ($query->sorts as $sort) {
            if ($sort instanceof SortByField) {
                $qb->addOrderBy($alias . '.' . $sort->field, $sort->direction);
            }
            if ($sort instanceof SortByRelationField) {
                if ($sort->relationField !== 'translations') {
                    $qb->addOrderBy($sort->relationField . '.' . $sort->field, $sort->direction);
                    $joinTables[$sort->relationField] = $alias;
                    continue;
                }
                if ($this->locale === 'en') {
                    $joinTables[$sort->relationField] = $alias;
                    $qb->andWhere('translations.locale = :trans_locale');
                    $qb->setParameter('trans_locale', $this->locale);
                    $qb->addOrderBy('translations.' . $sort->field, $sort->direction);
                    continue;
                }

                // handle non english locales with fallback to english
                $aliasLocaleFallback = 'translations_sort_en.locale';
                $aliasLocale = 'translations_sort.locale';
                $qb->leftJoin(
                    $alias . '.translations',
                    'translations_sort_en',
                    Join::WITH, $aliasLocaleFallback . ' = :en_locale'
                );

                $qb->leftJoin(
                    $alias . '.translations',
                    'translations_sort',
                    Join::WITH, $aliasLocale . ' = :locale'
                );

                $aliasField = 'translations_sort.' . $sort->field;
                $aliasFieldFallback = 'translations_sort_en.' . $sort->field;
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->andX($aliasLocale . ' = :locale', $aliasField . ' IS NOT NULL'),
                        $qb->expr()->andX(
                            $aliasLocaleFallback . ' = :en_locale',
                            $aliasFieldFallback . ' IS NOT NULL',
                            $aliasField . ' IS NULL',
                        )
                    )
                )
                    ->setParameter('locale', $this->locale)
                    ->setParameter('en_locale', 'en')
                    ->addOrderBy('IFNULL(' . $aliasField . ', ' . $aliasFieldFallback . ')', $sort->direction);
            }
        }
    }

    /**
     * @param array $joinTables
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    private function joinTables(array $joinTables, QueryBuilder $queryBuilder): void
    {
        foreach ($joinTables as $joinTable => $table) {
            $queryBuilder->leftJoin($table . '.' . $joinTable, $joinTable);
        }
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}