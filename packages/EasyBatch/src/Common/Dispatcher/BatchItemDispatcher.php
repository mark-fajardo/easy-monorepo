<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Dispatcher;

use EonX\EasyBatch\Common\Iterator\BatchItemIteratorInterface;
use EonX\EasyBatch\Common\Manager\BatchObjectManagerInterface;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\ValueObject\BatchInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemInterface;
use EonX\EasyBatch\Common\ValueObject\BatchItemIteratorConfig;

final readonly class BatchItemDispatcher
{
    public function __construct(
        private AsyncDispatcherInterface $asyncDispatcher,
        private BatchItemIteratorInterface $batchItemIterator,
        private BatchItemRepositoryInterface $batchItemRepository,
        private BatchRepositoryInterface $batchRepository,
    ) {
    }

    public function dispatchDependItems(
        BatchObjectManagerInterface $batchObjectManager,
        BatchItemInterface $batchItem,
    ): void {
        $this->doDispatch($batchObjectManager, $batchItem->getBatchId(), $batchItem->getName());
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function dispatchItemsForBatch(BatchObjectManagerInterface $batchObjectManager, BatchInterface $batch): void
    {
        $this->doDispatch($batchObjectManager, $batch->getIdOrFail());
    }

    private function doDispatch(
        BatchObjectManagerInterface $batchObjectManager,
        int|string $batchId,
        ?string $dependsOnName = null,
    ): void {
        // Update batchItems to status pending after current page is dispatched
        $currentPageCallback = function (array $items): void {
            $this->batchItemRepository->updateStatusToPending($items);
        };

        $func = function (BatchItemInterface $batchItem) use ($batchObjectManager): void {
            if ($batchItem->getType() === BatchItemInterface::TYPE_MESSAGE) {
                $this->asyncDispatcher->dispatchItem($batchItem);

                return;
            }

            if ($batchItem->getType() === BatchItemInterface::TYPE_NESTED_BATCH) {
                $batchObjectManager->dispatchBatch($this->batchRepository->findNestedOrFail($batchItem->getIdOrFail()));
            }
        };

        $iteratorConfig = BatchItemIteratorConfig::create($batchId, $func, $dependsOnName)
            ->forDispatch()
            ->setCurrentPageCallback($currentPageCallback);

        $this->batchItemIterator->iterateThroughItems($iteratorConfig);
    }
}
