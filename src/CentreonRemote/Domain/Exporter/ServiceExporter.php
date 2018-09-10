<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository;

class ServiceExporter implements ExporterServiceInterface
{

    use ExportPathTrait;

    const EXPORT_FILE_HOST_RELATION = 'host_service_relation.yaml';
    const EXPORT_FILE_SERVICE = 'service.yaml';
    const EXPORT_FILE_GROUP = 'servicegroup.yaml';
    const EXPORT_FILE_GROUP_RELATION = 'servicegroup_relation.yaml';
    const EXPORT_FILE_CATEGORY = 'service_categories.yaml';
    const EXPORT_FILE_CATEGORY_RELATION = 'service_categories_relation.yaml';
    const EXPORT_FILE_MACRO = 'on_demand_macro_service.yaml';
    const EXPORT_FILE_INFO = 'extended_service_information.yaml';

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

        $db->getRepository(Repository\ServiceRepository::class)->truncate();
    }

    /**
     * Export data
     */
    public function export(): void
    {
        // create path
        $this->createPath();
        $pollerIds = $this->commitment->getPollers();

        $templateChain = $this->db
            ->getRepository(Repository\ServiceRepository::class)
            ->getChainByPoller($pollerIds)
        ;

        // Extract data
        (function() use ($pollerIds) {
            $hostRelation = $this->db
                ->getRepository(Repository\HostServiceRelationRepository::class)
                ->export($pollerIds)
            ;
            $this->commitment->getParser()::dump($hostRelation, $this->getFile(static::EXPORT_FILE_HOST_RELATION));
        })();

        (function() use ($pollerIds, $templateChain) {
            $services = $this->db
                ->getRepository(Repository\ServiceRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($services, $this->getFile(static::EXPORT_FILE_SERVICE));
        })();

        (function() use ($pollerIds, $templateChain) {
            $serviceGroups = $this->db
                ->getRepository(Repository\ServiceGroupRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($serviceGroups, $this->getFile(static::EXPORT_FILE_GROUP));
        })();

        (function() use ($pollerIds, $templateChain) {
            $serviceGroupRelation = $this->db
                ->getRepository(Repository\ServiceGroupRelationRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($serviceGroupRelation, $this->getFile(static::EXPORT_FILE_GROUP_RELATION));
        })();

        (function() use ($pollerIds, $templateChain) {
            $serviceCategories = $this->db
                ->getRepository(Repository\ServiceCategoryRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($serviceCategories, $this->getFile(static::EXPORT_FILE_CATEGORY));
        })();

        (function() use ($pollerIds, $templateChain) {
            $serviceCategoryRelation = $this->db
                ->getRepository(Repository\ServiceCategoryRelationRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($serviceCategoryRelation, $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION));
        })();

        (function() use ($pollerIds, $templateChain) {
            $serviceMacros = $this->db
                ->getRepository(Repository\OnDemandMacroServiceRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($serviceMacros, $this->getFile(static::EXPORT_FILE_MACRO));
        })();

        (function() use ($pollerIds, $templateChain) {
            $serviceInfo = $this->db
                ->getRepository(Repository\ExtendedServiceInformationRepository::class)
                ->export($pollerIds, $templateChain)
            ;
            $this->commitment->getParser()::dump($serviceInfo, $this->getFile(static::EXPORT_FILE_INFO));
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

        // insert host relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_HOST_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('host_service_relation', $data);
            }
        })();

        // insert host relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_SERVICE);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('service', $data);
            }
        })();

        // insert group
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('servicegroup', $data);
            }
        })();

        // insert group relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('servicegroup_relation', $data);
            }
        })();

        // insert category
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('service_categories', $data);
            }
        })();

        // insert category relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('service_categories_relation', $data);
            }
        })();

        // insert macro
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_MACRO);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('on_demand_macro_service', $data);
            }
        })();

        // insert info
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_INFO);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('extended_service_information', $data);
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
        return 'service';
    }
}
