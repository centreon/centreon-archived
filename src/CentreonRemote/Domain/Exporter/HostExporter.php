<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Repository;

class HostExporter extends ExporterServiceAbstract
{

    const NAME = 'host';
    const EXPORT_FILE_GROUP = 'hostgroup.yaml';
    const EXPORT_FILE_GROUP_HG_RELATION = 'hostgroup_hg_relation.yaml';
    const EXPORT_FILE_GROUP_RELATION = 'hostgroup_relation.yaml';
    const EXPORT_FILE_CATEGORY = 'hostcategories.yaml';
    const EXPORT_FILE_CATEGORY_RELATION = 'hostcategories_relation.yaml';
    const EXPORT_FILE_HOST = 'host.yaml';
    const EXPORT_FILE_INFO = 'extended_host_information.yaml';
    const EXPORT_FILE_MACRO = 'on_demand_macro_host.yaml';
    const EXPORT_FILE_TEMPLATE = 'host_template_relation.yaml';

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\HostRepository::class)->truncate();
    }

    /**
     * Export data
     */
    public function export(): void
    {
        // create path
        $this->createPath();
        $pollerIds = $this->commitment->getPollers();

        $hostTemplateChain = $this->_getIf('host.tpl.relation.chain', function () use ($pollerIds) {
            $baList = $this->cache->get('ba.list');

            return $this->db
                    ->getRepository(Repository\HostTemplateRelationRepository::class)
                    ->getChainByPoller($pollerIds, $baList)
            ;
        });

        // Extract data
        (function () use ($pollerIds, $hostTemplateChain) {
            $hosts = $this->db
                ->getRepository(Repository\HostRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($hosts, $this->getFile(static::EXPORT_FILE_HOST));
        })();

        (function () use ($pollerIds, $hostTemplateChain) {
            $hostCategories = $this->db
                ->getRepository(Repository\HostCategoryRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($hostCategories, $this->getFile(static::EXPORT_FILE_CATEGORY));
        })();

        (function () use ($pollerIds, $hostTemplateChain) {
            $hostCategoryRelation = $this->db
                ->getRepository(Repository\HostCategoryRelationRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($hostCategoryRelation, $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION));
        })();

        (function () use ($pollerIds, $hostTemplateChain) {
            $hostGroups = $this->db
                ->getRepository(Repository\HostGroupRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($hostGroups, $this->getFile(static::EXPORT_FILE_GROUP));
        })();

        (function () use ($pollerIds, $hostTemplateChain) {
            $hostGroupRelation = $this->db
                ->getRepository(Repository\HostGroupRelationRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($hostGroupRelation, $this->getFile(static::EXPORT_FILE_GROUP_RELATION));
        })();

        (function () use ($pollerIds, $hostTemplateChain) {
            $hostGroupHgRelation = $this->db
                ->getRepository(Repository\HostGroupHgRelationRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($hostGroupHgRelation, $this->getFile(static::EXPORT_FILE_GROUP_HG_RELATION));
        })();

        (function () use ($pollerIds, $hostTemplateChain) {
            $hostInfo = $this->db
                ->getRepository(Repository\ExtendedHostInformationRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($hostInfo, $this->getFile(static::EXPORT_FILE_INFO));
        })();

        (function () use ($pollerIds, $hostTemplateChain) {
            $hostMacros = $this->db
                ->getRepository(Repository\OnDemandMacroHostRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($hostMacros, $this->getFile(static::EXPORT_FILE_MACRO));
        })();

        (function () use ($pollerIds, $hostTemplateChain) {
            $hostTemplates = $this->db
                ->getRepository(Repository\HostTemplateRelationRepository::class)
                ->export($pollerIds, $hostTemplateChain)
            ;
            $this->_dump($hostTemplates, $this->getFile(static::EXPORT_FILE_TEMPLATE));
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

        // insert host
        (function () use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_HOST);
            $result = $this->_parse($exportPathFile);

            $dataHostServerRelation = array();
            $dataHosts = array();

            foreach ($result as $data) {

                if ($data['_nagios_id']) {
                    $dataHostServerRelation[] = array(
                        'nagios_server_id' => $data['_nagios_id'],
                        'host_host_id' => $data['host_id'],
                    );
                }

                unset($data['_nagios_id']);
                $dataHosts[] = $data;
            }

            // Insert latest values
            if ($dataHosts) {
                // Insert values by group of BULK_SIZE
                $db->insertBulk('host', $dataHosts);

                // Unset array after insert
                unset($dataHosts);
            }
            if ($dataHostServerRelation) {
                // Insert values by group of BULK_SIZE
                $db->insertBulk('ns_host_relation', $dataHostServerRelation);

                // Reset array after insert
                unset($dataHostServerRelation);
            }
            unset($result);
        })();

        // insert groups
        (function () use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP);
            $result = $this->_parse($exportPathFile);

            $db->insertBulk('hostgroup', $result);

            unset($result);
        })();

        // insert group relation
        (function () use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP_RELATION);
            $result = $this->_parse($exportPathFile);

            $db->insertBulk('hostgroup_relation', $result);

            unset($result);
        })();

        // insert categories
        (function () use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY);
            $result = $this->_parse($exportPathFile);

            $db->insertBulk('hostcategories', $result);

            unset($result);
        })();

        // insert categories relation
        (function () use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION);
            $result = $this->_parse($exportPathFile);

            $db->insertBulk('hostcategories_relation', $result);

            unset($result);
        })();

        // insert info
        (function () use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_INFO);
            $result = $this->_parse($exportPathFile);

            $db->insertBulk('extended_host_information', $result);

            unset($result);
        })();

        // insert macro
        (function () use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_MACRO);
            $result = $this->_parse($exportPathFile);

            $db->insertBulk('on_demand_macro_host', $result);

            unset($result);
        })();

        // insert template
        (function () use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_TEMPLATE);
            $result = $this->_parse($exportPathFile);

            $db->insertBulk('host_template_relation', $result);

            unset($result);
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }

    public static function order(): int
    {
        return 30;
    }
}
