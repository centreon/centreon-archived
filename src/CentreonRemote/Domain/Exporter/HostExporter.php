<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository\HostRepository;
use Centreon\Domain\Repository\HostGroupRepository;
use Centreon\Domain\Repository\HostTemplateRelationRepository;
use Centreon\Domain\Repository\ExtendedHostInformationRepository;

class HostExporter implements ExporterServiceInterface
{
    use ExportPathTrait;

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
     * Export media data
     * 
     * @todo add exceptions
     */
    public function export(): void
    {
        // create path
        $exportPath = $this->createPath();

        $pollerId = $this->commitment->getPoller();
        
        // Extract data
        $hostGroups = $this->db
            ->getRepository(HostGroupRepository::class)
            ->export($pollerId)
        ;
        
        $hosts = $this->db
            ->getRepository(HostRepository::class)
            ->export($pollerId)
        ;

        $hostMacros = $this->db
            ->getRepository(ExtendedHostInformationRepository::class)
            ->export($pollerId)
        ;

        $hostTemplates = $this->db
            ->getRepository(HostTemplateRelationRepository::class)
            ->export($pollerId)
        ;

        $this->commitment->getParser()::dump($hostGroups, "{$exportPath}/hostgroup.yaml");
        $this->commitment->getParser()::dump($hosts, "{$exportPath}/host.yaml");
        $this->commitment->getParser()::dump($hostMacros, "{$exportPath}/on_demand_macro_host.yaml");
        $this->commitment->getParser()::dump($hostTemplates, "{$exportPath}/host_template_relation.yaml");
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
