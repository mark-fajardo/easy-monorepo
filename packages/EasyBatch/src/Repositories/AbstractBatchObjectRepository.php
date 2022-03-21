<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Interfaces\BatchObjectFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectTransformerInterface;

abstract class AbstractBatchObjectRepository
{
    /**
     * @var null|string[]
     */
    private ?array $tableColumns = null;

    public function __construct(
        protected BatchObjectFactoryInterface $factory,
        protected BatchObjectIdStrategyInterface $idStrategy,
        protected BatchObjectTransformerInterface $transformer,
        protected Connection $conn,
        protected string $table
    ) {
        // No body needed.
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doSave(BatchObjectInterface $batchObject): void
    {
        $batchObjectId = $batchObject->getId() ?? $this->idStrategy->generateId();
        $now = Carbon::now('UTC');

        $batchObject->setId($batchObjectId);
        $batchObject->setCreatedAt($batchObject->getCreatedAt() ?? $now);
        $batchObject->setUpdatedAt($now);

        $data = $this->transformer->transformToArray($batchObject);
        foreach (\array_diff(\array_keys($data), $this->resolveTableColumns()) as $toRemove) {
            unset($data[$toRemove]);
        }

        $this->has($batchObjectId) === false
            ? $this->conn->insert($this->table, $data)
            : $this->conn->update($this->table, $data, ['id' => $batchObjectId]);
    }

    /**
     * @param int|string $id
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doFind($id): ?BatchObjectInterface
    {
        $data = $this->fetchData($id);

        return $data !== null ? $this->factory->createFromArray($data) : null;
    }

    /**
     * @param int|string $id
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function has($id): bool
    {
        return \is_array($this->fetchData($id));
    }

    /**
     * @param int|string $id
     *
     * @return null|mixed[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchData($id): ?array
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->table);
        $result = $this->conn->fetchAssociative($sql, ['id' => $id]);

        return \is_array($result) ? $result : null;
    }

    /**
     * @return string[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function resolveTableColumns(): array
    {
        if ($this->tableColumns !== null) {
            return $this->tableColumns;
        }

        $sql = $this->conn->getDatabasePlatform()
            ->getListTableColumnsSQL($this->table);

        $columns = $this->conn->fetchAllAssociative($sql);

        return $this->tableColumns = \array_column($columns, 'name');
    }
}
