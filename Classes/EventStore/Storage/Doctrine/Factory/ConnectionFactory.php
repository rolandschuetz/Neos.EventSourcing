<?php
declare(strict_types=1);
namespace Neos\EventSourcing\EventStore\Storage\Doctrine\Factory;

/*
 * This file is part of the Neos.EventSourcing package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Utility\Arrays;

/**
 * Factory for Doctrine connections
 *
 * @Flow\Scope("singleton")
 */
class ConnectionFactory
{

    /**
     * NOTE: We inject the Doctrine ObjectManager in order to initialize the EntityManagerConfiguration::configureEntityManager
     * slot is invoked. Without this an exception 'Unknown column type "flow_json_array" requested' might be thrown
     *
     * @Flow\Inject(lazy=false)
     * @var EntityManagerInterface
     */
    protected $doctrineEntityManager;

    /**
     * @var @Flow\InjectConfiguration(package="Neos.Flow", path="persistence.backendOptions")
     */
    protected $defaultFlowDatabaseConfiguration;

    /**
     * @param array $options
     * @return Connection
     * @throws DBALException
     */
    public function create(array $options = null): Connection
    {
        $config = new Configuration();
        $connectionParams = $options['backendOptions'] ?? [];
        $connectionParams = Arrays::arrayMergeRecursiveOverrule($this->defaultFlowDatabaseConfiguration, $connectionParams);

        $connection = DriverManager::getConnection($connectionParams, $config);

        if (isset($options['mappingTypes']) && \is_array($options['mappingTypes'])) {
            foreach ($options['mappingTypes'] as $typeName => $typeConfiguration) {
                if (!Type::hasType($typeName)) {
                    Type::addType($typeName, $typeConfiguration['className']);
                }
                $connection->getDatabasePlatform()->registerDoctrineTypeMapping($typeConfiguration['dbType'], $typeName);
            }
        }

        return $connection;
    }
}
