<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Repository;

class CommandExporter extends ExporterServiceAbstract
{

    const NAME = 'command';
    const EXPORT_FILE_COMMAND = 'command.yaml';
    const EXPORT_FILE_COMMAND_ARG = 'command_arg_description.yaml';
    const EXPORT_FILE_COMMAND_MACRO = 'on_demand_macro_command.yaml';
    const EXPORT_FILE_CONNECTOR = 'connector.yaml';
    const EXPORT_FILE_CATEGORY = 'command_categories.yaml';
    const EXPORT_FILE_CATEGORY_RELATION = 'command_categories_relation.yaml';

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\CommandRepository::class)->truncate();
    }

    /**
     * Export data
     */
    public function export(): void
    {
        // create path
        $this->createPath();
        $pollerIds = $this->commitment->getPollers();

        (function() use ($pollerIds) {
            $command = $this->db
                ->getRepository(Repository\CommandRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($command, $this->getFile(static::EXPORT_FILE_COMMAND));
        })();

        (function() use ($pollerIds) {
            $commandArg = $this->db
                ->getRepository(Repository\CommandArgDescriptionRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($commandArg, $this->getFile(static::EXPORT_FILE_COMMAND_ARG));
        })();

        (function() use ($pollerIds) {
            $commandMacro = $this->db
                ->getRepository(Repository\OnDemandMacroCommandRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($commandMacro, $this->getFile(static::EXPORT_FILE_COMMAND_MACRO));
        })();

        (function() use ($pollerIds) {
            $connector = $this->db
                ->getRepository(Repository\ConnectorRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($connector, $this->getFile(static::EXPORT_FILE_CONNECTOR));
        })();

        (function() use ($pollerIds) {
            $category = $this->db
                ->getRepository(Repository\CommandCategoryRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($category, $this->getFile(static::EXPORT_FILE_CATEGORY));
        })();

        (function() use ($pollerIds) {
            $categoryRelation = $this->db
                ->getRepository(Repository\CommandCategoryRelationRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($categoryRelation, $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION));
        })();
    }

    /**
     * Import data
     */
    public function import(): void
    {
        // skip if no data
        if (!is_dir($this->getPath())) {
            return;
        }

        $db = $this->db->getAdapter('configuration_db');

        // start transaction
        $db->beginTransaction();

        // allow insert records without foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=0;');

        // truncate tables
        $this->cleanup();

        // insert command
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_COMMAND);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('command', $data);
            }
        })();

        // insert command argument
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_COMMAND_ARG);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('command_arg_description', $dataRelation);
            }
        })();

        // insert command macro
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_COMMAND_MACRO);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('on_demand_macro_command', $data);
            }
        })();

        // insert connector
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CONNECTOR);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('connector', $data);
            }
        })();

        // insert category
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('command_categories', $data);
            }
        })();

        // insert category relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('command_categories_relation', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }
}
