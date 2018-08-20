<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository\ServiceRepository;
use Centreon\Domain\Repository\ServiceGroupRepository;
use Centreon\Domain\Repository\ExtendedServiceInformationRepository;

class ServiceExporter implements ExporterServiceInterface
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
     * Export data
     * 
     * @todo add exceptions
     */
    public function export(): void
    {
        // @todo in progress
        return;

        // create path
        $exportPath = $this->createPath();

        $pollerId = $this->commitment->getPoller();
        
        // Extract data
        $serviceGroups = $this->db
            ->getRepository(ServiceGroupRepository::class)
            ->export($pollerId)
        ;
        
        $services = $this->db
            ->getRepository(ServiceRepository::class)
            ->export($pollerId)
        ;

        $serviceMacros = $this->db
            ->getRepository(ExtendedServiceInformationRepository::class)
            ->export($pollerId)
        ;

        $hostTemplates = $this->db
            ->getRepository(HostTemplateRelationRepository::class)
            ->export($pollerId)
        ;

        $this->commitment->getParser()::dump($serviceGroups, "{$exportPath}/servicegroup.yaml");
        $this->commitment->getParser()::dump($services, "{$exportPath}/service.yaml");
//        $this->commitment->getParser()::dump($serviceMacros, "{$exportPath}/on_demand_macro_host.yaml");
//        $this->commitment->getParser()::dump($hostTemplates, "{$exportPath}/host_template_relation.yaml");
    }
    
    /**
     * Import data
     * 
     * @todo add exceptions
     */
    public function import(): void
    {
        // @todo in progress
        // ...
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
