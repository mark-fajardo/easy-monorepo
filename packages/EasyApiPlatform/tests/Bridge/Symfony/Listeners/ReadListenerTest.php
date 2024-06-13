<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Bridge\Symfony\Listeners;

use EonX\EasyApiPlatform\Bridge\Symfony\Listeners\ReadListener;
use EonX\EasyApiPlatform\Tests\AbstractApiTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ReadListenerTest extends AbstractApiTestCase
{
    public function testItSucceeds(): void
    {
        $this->initDatabase();

        $response = self::$client->request(
            'POST',
            '/questions/1/mark-as-answered',
            [
                'headers' => [
                    'content-type' => 'application/json',
                ],
            ]
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testItSucceedsWithoutReadListener(): void
    {
        $this->initDatabase();
        self::getService(EventDispatcherInterface::class)->removeListener(
            'kernel.request',
            [self::getService(ReadListener::class), '__invoke']
        );

        $response = self::$client->request(
            'POST',
            '/questions/1/mark-as-answered',
            [
                'headers' => [
                    'content-type' => 'application/json',
                ],
            ]
        );

        self::assertSame(200, $response->getStatusCode());
    }
}
