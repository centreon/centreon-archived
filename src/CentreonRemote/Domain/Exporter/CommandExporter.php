<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository;

class CommandExporter implements ExporterServiceInterface
{

    use ExportPathTrait;

    const EXPORT_FILE_COMMAND = 'command.yaml';
    const EXPORT_FILE_COMMAND_ARG = 'command_arg_description.yaml';
    const EXPORT_FILE_COMMAND_MACRO = 'on_demand_macro_command.yaml';
    const EXPORT_FILE_CONNECTOR = 'connector.yaml';
    const EXPORT_FILE_CATEGORY = 'command_categories.yaml';
    const EXPORT_FILE_CATEGORY_RELATION = 'command_categories_relation.yaml';

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    private $db;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    private $commitment;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->db = $services->get('centreon.db-manager');
    }

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
            $this->commitment->getParser()::dump($command, $this->getFile(static::EXPORT_FILE_COMMAND));
        })();

        (function() use ($pollerIds) {
            $commandArg = $this->db
                ->getRepository(Repository\CommandArgDescriptionRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($commandArg, $this->getFile(static::EXPORT_FILE_COMMAND_ARG));
        })();

        (function() use ($pollerIds) {
            $commandMacro = $this->db
                ->getRepository(Repository\OnDemandMacroCommandRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($commandMacro, $this->getFile(static::EXPORT_FILE_COMMAND_MACRO));
        })();

        (function() use ($pollerIds) {
            $connector = $this->db
                ->getRepository(Repository\ConnectorRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($connector, $this->getFile(static::EXPORT_FILE_CONNECTOR));
        })();

        (function() use ($pollerIds) {
            $category = $this->db
                ->getRepository(Repository\CommandCategoryRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($category, $this->getFile(static::EXPORT_FILE_CATEGORY));
        })();

        (function() use ($pollerIds) {
            $categoryRelation = $this->db
                ->getRepository(Repository\CommandCategoryRelationRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($categoryRelation, $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION));
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
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('command', $data);
            }
        })();

        // insert command argument
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_COMMAND_ARG);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('command_arg_description', $dataRelation);
            }
        })();

        // insert command macro
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_COMMAND_MACRO);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('on_demand_macro_command', $data);
            }
        })();

        // insert connector
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CONNECTOR);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('connector', $data);
            }
        })();

        // insert category
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('command_categories', $data);
            }
        })();

        // insert category relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('command_categories_relation', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }

    public function setCommitment(ExportCommitment $commitment): void
    {
        $this->commitment = $commitment;
    }

    public static function getName(): string
    {
        return 'command';
    }
}
