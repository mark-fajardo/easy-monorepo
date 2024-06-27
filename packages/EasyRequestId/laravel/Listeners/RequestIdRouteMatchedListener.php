<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Laravel\Listeners;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Common\Resolver\ResolvesFromHttpFoundationRequestTrait;
use Illuminate\Routing\Events\RouteMatched;

final class RequestIdRouteMatchedListener
{
    use ResolvesFromHttpFoundationRequestTrait;

    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
    ) {
    }

    public function handle(RouteMatched $event): void
    {
        $this->setResolver($event->request, $this->requestIdProvider);
    }
}
