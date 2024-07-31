<?php
declare(strict_types=1);

namespace EonX\EasyLogging\MonologHandler;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyLogging\Resolver\BugsnagSeverityResolverInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

final class BugsnagMonologHandler extends AbstractProcessingHandler
{
    /**
     * @inheritdoc
     */
    public function __construct(
        private readonly BugsnagSeverityResolverInterface $bugsnagSeverityResolver,
        private readonly Client $bugsnagClient,
        $level = null,
        ?bool $bubble = null,
    ) {
        parent::__construct($level ?? Logger::WARNING, $bubble ?? true);
    }

    protected function write(array $record): void
    {
        if (
            isset($record['context']['exception_reported_by_error_handler'])
            && $record['context']['exception_reported_by_error_handler'] === true
        ) {
            return;
        }

        $severity = $this->bugsnagSeverityResolver->resolve((int)$record['level']);
        $this->bugsnagClient
            ->notifyError(
                (string)$record['message'],
                (string)$record['formatted'],
                static function (Report $report) use ($record, $severity): void {
                    $report->setSeverity($severity->value);
                    $report->setMetaData(['context' => $record['context'], 'extra' => $record['extra']]);
                }
            );
    }
}
