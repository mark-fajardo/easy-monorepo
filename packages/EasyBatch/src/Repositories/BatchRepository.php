<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

use EonX\EasyBatch\Exceptions\BatchNotFoundException;
use EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException;
use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;

final class BatchRepository extends AbstractBatchObjectRepository implements BatchRepositoryInterface
{
    /**
     * @var string
     */
    private const SAVEPOINT = 'easy_batch_conn_savepoint';

    /**
     * @var bool
     */
    private $savepointActive = false;

    /**
     * @param int|string $id
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function find($id): ?BatchInterface
    {
        /** @var null|\EonX\EasyBatch\Interfaces\BatchInterface $batch */
        $batch = $this->doFind($id);

        return $batch;
    }

    /**
     * @param int|string $id
     *
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOrFail($id): BatchInterface
    {
        $batch = $this->find($id);

        if ($batch !== null) {
            return $batch;
        }

        throw new BatchNotFoundException(\sprintf('Batch for id "%s" not found', $id));
    }

    /**
     * @param int|string $parentBatchItemId
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     */
    public function findNestedOrFail($parentBatchItemId): BatchInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE parent_batch_item_id = :id', $this->table);
        $data = $this->conn->fetchAssociative($sql, ['id' => $parentBatchItemId]);

        if (\is_array($data)) {
            /** @var \EonX\EasyBatch\Interfaces\BatchInterface $batch */
            $batch = $this->factory->createFromArray($data);

            return $batch;
        }

        throw new BatchNotFoundException(\sprintf(
            'Batch for parent_batch_item_id "%s" not found',
            $parentBatchItemId
        ));
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function save(BatchInterface $batch): BatchInterface
    {
        $this->doSave($batch);

        return $batch;
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     * @throws \EonX\EasyBatch\Exceptions\BatchNotFoundException
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     * @throws \Throwable
     */
    public function updateAtomic(BatchInterface $batch, callable $func): BatchInterface
    {
        if ($batch->getId() === null) {
            throw new BatchObjectIdRequiredException('Batch ID is required to update it.');
        }

        $this->beginTransaction();

        try {
            $sql = \sprintf('SELECT * FROM %s WHERE id = :id FOR UPDATE', $this->table);
            $data = $this->conn->fetchAssociative($sql, ['id' => $batch->getId()]);
            $freshBatch = \is_array($data) ? $this->factory->createFromArray($data) : null;

            if ($freshBatch === null) {
                throw new BatchNotFoundException(\sprintf('Batch for id "%s" not found', $batch->getId()));
            }

            $freshBatch = $this->save($func($freshBatch));

            $this->commit();

            return $freshBatch;
        } catch (\Throwable $throwable) {
            $this->rollback();

            throw $throwable;
        }
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    private function beginTransaction(): void
    {
        // If transaction active and savepoint supported, create new savepoint
        if ($this->conn->isTransactionActive() && $this->conn->getDatabasePlatform()->supportsSavepoints()) {
            $this->conn->createSavepoint(self::SAVEPOINT);
            $this->savepointActive = true;

            return;
        }

        // Otherwise create transaction
        $this->savepointActive = false;
        $this->conn->beginTransaction();
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function commit(): void
    {
        $this->savepointActive
            ? $this->conn->releaseSavepoint(self::SAVEPOINT)
            : $this->conn->commit();
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function rollback(): void
    {
        $this->savepointActive
            ? $this->conn->rollbackSavepoint(self::SAVEPOINT)
            : $this->conn->rollBack();
    }
}
