<?php
namespace Neos\Cqrs\EventStore\Storage\Doctrine\Schema;

/*
 * This file is part of the Neos.EventStore.DatabaseStorageAdapter package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * Use this helper in a doctrine migrations script to set up the event store schema
 */
final class EventStoreSchema
{
    /**
     * Use this method when you work with a single stream strategy
     *
     * @param Schema $schema
     * @param string $name
     */
    public static function createStream(Schema $schema, string $name)
    {
        $table = $schema->createTable($name);

        // The stream id, usually in the format "<BoundedContext>:<StreamName>"
        $table->addColumn('id', Type::INTEGER, array('autoincrement' => true));
        // The stream id, usually in the format "<BoundedContext>:<StreamName>"
        $table->addColumn('stream', Type::STRING, ['length' => 255]);
        // Version of the event in the respective stream
        $table->addColumn('version', Type::BIGINT, ['unsigned' => true]);
        // The event type in the format "<BoundedContext>:<EventType>"
        $table->addColumn('type', Type::STRING, ['length' => 255]);
        // The event payload as JSON
        $table->addColumn('payload', Type::TEXT);
        // The event metadata as JSON
        $table->addColumn('metadata', Type::TEXT);
        // Timestamp of the the event publishing
        $table->addColumn('recordedat', Type::DATETIME);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['stream', 'version'], 'stream_version');
    }

    /**
     * @param Schema $schema
     * @param string $name
     */
    public static function drop(Schema $schema, string $name)
    {
        $schema->dropTable($name);
    }
}