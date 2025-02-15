<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Paginator;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

trait EloquentPaginatorTrait
{
    use DatabaseCommonPaginatorTrait;

    private Model $model;

    private ?int $totalItems = null;

    protected function doGetItems(): array
    {
        return $this->fetchItems();
    }

    private function applyPagination(EloquentBuilder|QueryBuilder $queryBuilder): void
    {
        $queryBuilder->forPage($this->getCurrentPage(), $this->getItemsPerPage());
    }

    private function createQueryBuilder(): EloquentBuilder
    {
        return $this->model->newQuery();
    }

    private function doGetTotalItems(): int
    {
        if ($this->totalItems !== null) {
            return $this->totalItems;
        }

        $queryBuilder = $this->createQueryBuilder();

        $this->applyCommonCriteria($queryBuilder);
        $this->applyFilterCriteria($queryBuilder);

        /** @var \Illuminate\Support\Collection<array-key, object>|int $count */
        $count = $queryBuilder->count();

        $this->totalItems = $count instanceof Collection ? $count->count() : $count;

        return $this->totalItems;
    }

    private function fetchItems(): array
    {
        $queryBuilder = $this->createQueryBuilder();

        if ($this->select !== null) {
            $queryBuilder->select($this->select);
        }

        // Get items criteria are applied regardless of fetching method
        $this->applyCommonCriteria($queryBuilder);
        $this->applyGetItemsCriteria($queryBuilder);

        return $this->hasJoinsInQuery === false
            ? $this->fetchItemsUsingQuery($queryBuilder)
            : $this->fetchItemsUsingPrimaryKeys($queryBuilder);
    }

    private function fetchItemsUsingPrimaryKeys(EloquentBuilder $queryBuilder): array
    {
        $primaryKeyQueryBuilder = $this->createQueryBuilder();

        // Apply pagination and criteria to get primary keys only for current page, and criteria
        $this->applyCommonCriteria($primaryKeyQueryBuilder);
        $this->applyFilterCriteria($primaryKeyQueryBuilder);
        $this->applyGetItemsCriteria($primaryKeyQueryBuilder);
        $this->applyPagination($primaryKeyQueryBuilder);

        $primaryKeyIndex = $this->getPrimaryKeyIndexWithDefault();
        // Prefix primaryKey with table to avoid ambiguous conflicts
        $prefixedPrimaryKey = \sprintf('%s.%s', $this->model->getTable(), $primaryKeyIndex);
        // Override select to fetch only primary key
        $primaryKeyQueryBuilder->select($prefixedPrimaryKey);

        /** @var \Illuminate\Database\Eloquent\Collection<array-key, \Illuminate\Database\Eloquent\Model> $result */
        $result = $primaryKeyQueryBuilder->get();

        $primaryKeys = $result->pluck($primaryKeyIndex)
            ->all();

        // If no primary keys, no items for current pagination
        if (\count($primaryKeys) === 0) {
            return [];
        }

        // Filter records on their primary keys
        $queryBuilder->whereIn($prefixedPrimaryKey, $primaryKeys);

        return $this->fetchResults($queryBuilder);
    }

    private function fetchItemsUsingQuery(EloquentBuilder $queryBuilder): array
    {
        $this->applyFilterCriteria($queryBuilder);
        $this->applyPagination($queryBuilder);

        return $this->fetchResults($queryBuilder);
    }

    private function fetchResults(EloquentBuilder $queryBuilder): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<array-key, \Illuminate\Database\Eloquent\Model> $collection */
        $collection = $queryBuilder->get();

        return \iterator_to_array($collection->getIterator());
    }
}
