<?php

declare(strict_types=1);

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Bridge\Symfony\Session\SessionTrackingConfigurator;
use EonX\EasyBugsnag\Bridge\Symfony\Session\SessionTrackingListener;
use EonX\EasyBugsnag\Session\SessionTracker;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Cache
    $services
        ->set(BridgeConstantsInterface::SERVICE_SESSION_TRACKING_CACHE, PhpFilesAdapter::class)
        ->arg('$namespace', '%' . BridgeConstantsInterface::PARAM_SESSION_TRACKING_CACHE_NAMESPACE . '%')
        ->arg('$directory', '%' . BridgeConstantsInterface::PARAM_SESSION_TRACKING_CACHE_DIRECTORY . '%');

    $services
        ->set(SessionTracker::class)
        ->arg('$exclude', '%' . BridgeConstantsInterface::PARAM_SESSION_TRACKING_EXCLUDE_URLS . '%')
        ->arg('$excludeDelimiter', '%' . BridgeConstantsInterface::PARAM_SESSION_TRACKING_EXCLUDE_URLS_DELIMITER . '%');

    $services
        ->set(SessionTrackingConfigurator::class)
        ->arg('$cache', ref(BridgeConstantsInterface::SERVICE_SESSION_TRACKING_CACHE))
        ->arg('$expiresAfter', '%' . BridgeConstantsInterface::PARAM_SESSION_TRACKING_CACHE_EXPIRES_AFTER . '%');

    $services
        ->set(SessionTrackingListener::class)
        ->tag('kernel.event_listener');
};
