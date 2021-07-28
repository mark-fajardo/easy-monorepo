<?php

declare(strict_types=1);

use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\MessageBodyDecoderInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\JsonMessageBodyDecoder;
use EonX\EasyAsync\Bridge\Symfony\Messenger\ShouldKillWorkerSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(MessageBodyDecoderInterface::class, JsonMessageBodyDecoder::class);

    $services
        ->set(ShouldKillWorkerSubscriber::class)
        ->tag('monolog.logger', ['channel' => BridgeConstantsInterface::LOG_CHANNEL]);
};
