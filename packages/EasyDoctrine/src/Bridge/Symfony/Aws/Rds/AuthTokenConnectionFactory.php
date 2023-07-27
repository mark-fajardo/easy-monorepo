<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Aws\Rds;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use EonX\EasyDoctrine\Bridge\AwsRds\AwsRdsConnectionParamsResolver;
use EonX\EasyDoctrine\Bridge\AwsRds\Drivers\DbalDriver;

final class AuthTokenConnectionFactory
{
    public function __construct(
        private readonly ConnectionFactory $factory,
        private readonly AwsRdsConnectionParamsResolver $connectionParamsResolver,
    ) {
    }

    /**
     * @param array<string, string>|null $mappingTypes
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function createConnection(
        array $params,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
        ?array $mappingTypes = null,
    ): Connection {
        $connection = $this->factory->createConnection($params, $config, $eventManager, $mappingTypes ?? []);

        $connectionClass = $connection::class;

        return new $connectionClass(
            $connection->getParams(),
            new DbalDriver($connection->getDriver(), $this->connectionParamsResolver),
            $connection->getConfiguration(),
            $connection->getEventManager()
        );
    }
}
