<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SeverityAwareExceptionInterface;
use Monolog\Logger;

final class SeverityClientConfigurator extends AbstractClientConfigurator
{
    /**
     * @var string[]
     */
    private const MAPPING = [
        Logger::INFO => SeverityAwareExceptionInterface::SEVERITY_INFO,
        Logger::WARNING => SeverityAwareExceptionInterface::SEVERITY_WARNING,
        Logger::ERROR => SeverityAwareExceptionInterface::SEVERITY_ERROR,
    ];

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface
     */
    private $errorLogLevelResolver;

    public function __construct(
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $priority = null
    ) {
        $this->errorLogLevelResolver = $errorLogLevelResolver;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $throwable = $report->getOriginalError();

                if ($throwable instanceof \Throwable) {
                    $report->setSeverity($this->getSeverity($throwable));
                }
            }));
    }

    private function getSeverity(\Throwable $throwable): ?string
    {
        // Allow to explicitly define the severity
        $severity = $throwable instanceof SeverityAwareExceptionInterface ? $throwable->getSeverity() : null;

        if ($severity !== null) {
            return $severity;
        }

        $logLevel = $this->errorLogLevelResolver->getLogLevel($throwable);

        return self::MAPPING[$logLevel] ?? null;
    }
}
