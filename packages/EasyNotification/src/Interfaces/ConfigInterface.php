<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface ConfigInterface
{
    public function getAlgorithm(): string;

    public function getQueueRegion(): string;

    public function getQueueUrl(): string;

    public function getProviderExternalId(): string;

    public function getSecret(): string;
}
