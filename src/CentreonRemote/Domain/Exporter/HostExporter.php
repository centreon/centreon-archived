<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository;

class HostExporter implements ExporterServiceInterface
{

    use ExportPathTrait;

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
    public function cleanup() : void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\HostRepository::class)->truncate();
    }

    /**
     * Export data
     * 
     * @todo add exceptions
     */
    public function export(): void
    {
        // create path
        $this->createPath();

        $pollerId = $this->commitment->getPoller();

        // Extract data
        $hosts = $this->db
            ->getRepository(Repository\HostRepository::class)
            ->export($pollerId)
        ;

        $hostCategories = $this->db
            ->getRepository(Repository\HostCategoryRepository::class)
            ->export($pollerId)
        ;

        $hostCategoryRelation = $this->db
            ->getRepository(Repository\HostCategoryRelationRepository::class)
            ->export($pollerId)
        ;

        $hostGroups = $this->db
            ->getRepository(Repository\HostGroupRepository::class)
            ->export($pollerId)
        ;

        $hostGroupRelation = $this->db
            ->getRepository(Repository\HostGroupRelationRepository::class)
            ->export($pollerId)
        ;

        $hostGroupHgRelation = $this->db
            ->getRepository(Repository\HostGroupHgRelationRepository::class)
            ->export($pollerId)
        ;

        $hostInfo = $this->db
            ->getRepository(Repository\ExtendedHostInformationRepository::class)
            ->export($pollerId)
        ;

        $hostMacros = $this->db
            ->getRepository(Repository\OnDemandMacroHostRepository::class)
            ->export($pollerId)
        ;

        $hostTemplates = $this->db
            ->getRepository(Repository\HostTemplateRelationRepository::class)
            ->export($pollerId)
        ;

        $this->commitment->getParser()::dump($hosts, $this->getFile(static::EXPORT_FILE_HOST));
        $this->commitment->getParser()::dump($hostGroups, $this->getFile(static::EXPORT_FILE_GROUP));
        $this->commitment->getParser()::dump($hostGroupRelation, $this->getFile(static::EXPORT_FILE_GROUP_RELATION));
        $this->commitment->getParser()::dump($hostGroupHgRelation, $this->getFile(static::EXPORT_FILE_GROUP_HG_RELATION));
        $this->commitment->getParser()::dump($hostCategories, $this->getFile(static::EXPORT_FILE_CATEGORY));
        $this->commitment->getParser()::dump($hostCategoryRelation, $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION));
        $this->commitment->getParser()::dump($hostInfo, $this->getFile(static::EXPORT_FILE_INFO));
        $this->commitment->getParser()::dump($hostMacros, $this->getFile(static::EXPORT_FILE_MACRO));
        $this->commitment->getParser()::dump($hostTemplates, $this->getFile(static::EXPORT_FILE_TEMPLATE));
    }

    /**
     * Import data
     * 
     * @todo add exceptions
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
        (function() use ($db){
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $dataRelation = [
                    'nagios_server_id' => $data['_nagios_id'],
                    'host_host_id' => $data['host_id'],
                ];
                unset($data['_nagios_id']);

                $db->insert('ns_host_relation', $data);
                $db->insert('host', $data);
            }
        })();

        // insert groups
        (function() use ($db){
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('hostcategories', $data);
            }
        })();

        // insert group relation
        (function() use ($db){
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('hostgroup_relation', $data);
            }
        })();

        // insert group to group relation
        (function() use ($db){
            $exportPathFile = $this->getFile(static::EXPORT_FILE_GROUP_HG_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('hostgroup_hg_relation', $data);
            }
        })();

        // insert categories
        (function() use ($db){
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('hostcategories', $data);
            }
        })();

        // insert categories
        (function() use ($db){
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CATEGORY_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('hostcategories_relation', $data);
            }
        })();

        // insert info
        (function() use ($db){
            $exportPathFile = $this->getFile(static::EXPORT_FILE_INFO);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('extended_host_information', $data);
            }
        })();

        // insert macro
        (function() use ($db){
            $exportPathFile = $this->getFile(static::EXPORT_FILE_MACRO);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('on_demand_macro_host', $data);
            }
        })();

        // insert template
        (function() use ($db){
            $exportPathFile = $this->getFile(static::EXPORT_FILE_TEMPLATE);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('host_template_relation', $data);
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
        return 'host';
    }
}
