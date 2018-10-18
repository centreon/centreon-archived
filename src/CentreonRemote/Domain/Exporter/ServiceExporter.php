<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Repository;

class ServiceExporter extends ExporterServiceAbstract
{

    const NAME = 'service';
    const EXPORT_FILE_HOST_RELATION = 'host_service_relation.yaml';
    const EXPORT_FILE_SERVICE = 'service.yaml';
    const EXPORT_FILE_GROUP = 'servicegroup.yaml';
    const EXPORT_FILE_GROUP_RELATION = 'servicegroup_relation.yaml';
    const EXPORT_FILE_CATEGORY = 'service_categories.yaml';
    const EXPORT_FILE_CATEGORY_RELATION = 'service_categories_relation.yaml';
    const EXPORT_FILE_MACRO = 'on_demand_macro_service.yaml';
    const EXPORT_FILE_INFO = 'extended_service_information.yaml';

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

        $serviceTemplateChain = $this->_getIf('service.chain', function() use ($pollerIds) {
            $baList = $this->cache->get('ba.list');

            return $this->db
                    ->getRepository(Repository\ServiceRepository::class)
                    ->getChainByPoller($pollerIds, $baList)
            ;
        });

        // Extract data
        (function() use ($pollerIds) {
            $hostRelation = $this->db
                ->getRepository(Repository\HostServiceRelationRepository::class)
                ->export($pollerIds)
            ;
            $this->_dump($hostRelation, $this->getFile(static::EXPORT_FILE_HOST_RELATION));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $services = $this->db
                ->getRepository(Repository\ServiceRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($services, $this->getFile(static::EXPORT_FILE_SERVICE));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $serviceGroups = $this->db
                ->getRepository(Repository\ServiceGroupRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($serviceGroups, $this->getFile(static::EXPORT_FILE_GROUP));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $serviceGroupRelation = $this->db
                ->getRepository(Repository\ServiceGroupRelationRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($serviceGroupRelation, $this->getFile(static::EXPORT_FILE_GROUP_RELATION));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $serviceCategories = $this->db
                ->getRepository(Repository\ServiceCategoryRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($serviceCategories, $this->getFile(static::EXPORT_FILE_CATEGORY));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $serviceCategoryRelation = $this->db
                ->getRepository(Repository\ServiceCategoryRelationRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($serviceCategoryRelation, $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $serviceMacros = $this->db
                ->getRepository(Repository\OnDemandMacroServiceRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($serviceMacros, $this->getFile(static::EXPORT_FILE_MACRO));
        })();

        (function() use ($pollerIds, $serviceTemplateChain) {
            $serviceInfo = $this->db
                ->getRepository(Repository\ExtendedServiceInformationRepository::class)
                ->export($pollerIds, $serviceTemplateChain)
            ;
            $this->_dump($serviceInfo, $this->getFile(static::EXPORT_FILE_INFO));
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
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('host_service_relation', $data);
            }
        })();

        // insert host relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_SERVICE);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('service', $data);
            }
        })();

        // insert group
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('servicegroup', $data);
            }
        })();

        // insert group relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('servicegroup_relation', $data);
            }
        })();

        // insert category
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('service_categories', $data);
            }
        })();

        // insert category relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('service_categories_relation', $data);
            }
        })();

        // insert macro
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_MACRO);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('on_demand_macro_service', $data);
            }
        })();

        // insert info
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_INFO);
            $result = $this->_parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('extended_service_information', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }

    public static function order(): int
    {
        return 40;
    }
}
