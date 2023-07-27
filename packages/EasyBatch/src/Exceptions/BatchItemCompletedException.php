<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Exceptions;

use EonX\EasyBatch\Interfaces\EasyBatchPreventProcessExceptionInterface as PreventProcessInterface;

final class BatchItemCompletedException extends AbstractEasyBatchException implements PreventProcessInterface
{
    // No body needed
}
