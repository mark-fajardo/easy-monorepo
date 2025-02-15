<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\MonologHandler;

use Bugsnag\Client;
use Bugsnag\Configuration;
use DateTimeImmutable;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
use EonX\EasyLogging\Resolver\BugsnagSeverityResolver;
use EonX\EasyLogging\Tests\Unit\AbstractSymfonyTestCase;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\LogRecord;

final class BugsnagMonologHandlerTest extends AbstractSymfonyTestCase
{
    public function testItSucceeds(): void
    {
        $client = new Client(new Configuration('some-api-key'));
        $sut = new BugsnagMonologHandler(new BugsnagSeverityResolver(), $client);
        $sut->setFormatter(new LineFormatter('formatted'));

        $sut->handle(new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'app',
            level: Level::Warning,
            message: 'message',
            context: [],
            extra: [],
            formatted: 'formatted',
        ));

        /** @var object $clientHttp */
        $clientHttp = self::getPrivatePropertyValue($client, 'http');
        /** @var \Bugsnag\Report[] $reports */
        $reports = self::getPrivatePropertyValue($clientHttp, 'queue');
        self::assertCount(1, $reports);
        $report = $reports[0];
        self::assertSame('info', $report->getSeverity());
        self::assertSame('message', $report->getName());
        self::assertSame('formatted', $report->getMessage());
    }

    public function testItSucceedsAndDoNothingWithExceptionHandledByEasyErrorHandler(): void
    {
        $client = new Client(new Configuration('some-api-key'));
        $sut = new BugsnagMonologHandler(new BugsnagSeverityResolver(), $client);
        $sut->setFormatter(new LineFormatter('formatted'));

        $sut->handle(new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'app',
            level: Level::Warning,
            message: 'message',
            context: [
                'exception_reported_by_error_handler' => true,
            ],
            extra: [],
            formatted: 'formatted'
        ));

        /** @var object $clientHttp */
        $clientHttp = self::getPrivatePropertyValue($client, 'http');
        $reports = self::getPrivatePropertyValue($clientHttp, 'queue');
        self::assertEmpty($reports);
    }
}
