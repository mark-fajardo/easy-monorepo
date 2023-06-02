<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Stubs;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyEventDispatcher\Bridge\Symfony\EventDispatcher;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

final class EntityManagerStub
{
    /**
     * @param \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher $dispatcher
     * @param string[] $subscribedEntities
     * @param string[] $fixtures
     *
     * @return \EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromDeferredEntityEventDispatcher(
        DeferredEntityEventDispatcher $dispatcher,
        array $subscribedEntities = [],
        array $fixtures = [],
    ) {
        $eventSubscriber = new EntityEventSubscriber($dispatcher, $subscribedEntities);
        $eventManager = new EventManager();
        $eventManager->addEventSubscriber($eventSubscriber);
        $entityManagerStub = self::createFromEventManager($eventManager, $fixtures);
        $eventDispatcher = new EventDispatcher(new SymfonyEventDispatcher());

        return new EntityManagerDecorator(
            $dispatcher,
            $eventDispatcher,
            $entityManagerStub
        );
    }

    /**
     * @param \Doctrine\Common\EventManager|null $eventManager
     * @param string[] $fixtures
     *
     * @return \Doctrine\ORM\EntityManager
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromEventManager(?EventManager $eventManager = null, array $fixtures = [])
    {
        $conn = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $config = new Configuration();
        $config->setProxyDir(__DIR__ . '/../var');
        $config->setProxyNamespace('Proxy');

        $config->setMetadataDriverImpl(new AttributeDriver([]));

        $entityManager = EntityManager::create($conn, $config, $eventManager);
        $schema = \array_map(function ($class) use ($entityManager) {
            return $entityManager->getClassMetadata($class);
        }, $fixtures);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema($schema);

        $entityManager->getConnection()
            ->setNestTransactionsWithSavepoints(true);

        return $entityManager;
    }

    /**
     * @param \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface $eventDispatcher
     * @param string[] $subscribedEntities
     * @param string[] $fixtures
     *
     * @return \EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromSymfonyEventDispatcher(
        EventDispatcherInterface $eventDispatcher,
        array $subscribedEntities = [],
        array $fixtures = [],
    ) {
        $dispatcher = new DeferredEntityEventDispatcher($eventDispatcher, ObjectCopierFactory::create());

        return self::createFromDeferredEntityEventDispatcher(
            $dispatcher,
            $subscribedEntities,
            $fixtures
        );
    }
}
