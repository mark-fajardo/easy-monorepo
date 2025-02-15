<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Strategy;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;

/**
 * A retry strategy with a constant or exponential retry delay.
 *
 * For example, if $delayMilliseconds=10000 & $multiplier=1 (default),
 * each retry will wait exactly 10 seconds.
 *
 * But if $delayMilliseconds=10000 & $multiplier=2:
 * - Retry 1: 10 second delay.
 * - Retry 2: 20 second delay (10000 * 2 = 20000).
 * - Retry 3: 40 second delay (20000 * 2 = 40000).
 *
 * @author Ryan Weaver <ryan@symfonycasts.com>
 */
final readonly class MultiplierWebhookRetryStrategy implements WebhookRetryStrategyInterface
{
    private int $delayMilliseconds;

    private float $multiplier;

    public function __construct(
        ?int $delayMilliseconds = null,
        ?float $multiplier = null,
        private ?int $maxDelayMilliseconds = null,
    ) {
        $this->delayMilliseconds = $delayMilliseconds ?? 1000;
        $this->multiplier = $multiplier ?? 1.0;
    }

    /**
     * @return int The time to delay/wait in milliseconds
     */
    public function getWaitingTime(WebhookInterface $webhook): int
    {
        $delay = (int)($this->delayMilliseconds * $this->multiplier ** $webhook->getCurrentAttempt());

        return $this->maxDelayMilliseconds !== null && $delay > $this->maxDelayMilliseconds
            ? $this->maxDelayMilliseconds
            : $delay;
    }

    public function isRetryable(WebhookInterface $webhook): bool
    {
        return $webhook->getCurrentAttempt() < $webhook->getMaxAttempt();
    }
}
