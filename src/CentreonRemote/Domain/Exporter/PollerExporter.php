<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository\CfgNagiosRepository;
use Centreon\Domain\Repository\NagiosServerRepository;
use Centreon\Domain\Repository\CfgCentreonBorkerRepository;

class PollerExporter implements ExporterServiceInterface
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
        // create path
        $exportPath = $this->createPath();

        $pollerId = $this->commitment->getPoller();
        
        $cfgNagios = $this->db
            ->getRepository(CfgNagiosRepository::class)
            ->export($pollerId)
        ;

        $nagiosServer = $this->db
            ->getRepository(NagiosServerRepository::class)
            ->export($pollerId)
        ;

        $cfgCentreonBroker = $this->db
            ->getRepository(CfgCentreonBorkerRepository::class)
            ->export($pollerId)
        ;

        // Store exports
        $this->commitment->getParser()::dump($cfgNagios, "{$exportPath}/cfg_nagios.yaml");
        $this->commitment->getParser()::dump($nagiosServer, "{$exportPath}/nagios_server.yaml");
        $this->commitment->getParser()::dump($cfgCentreonBroker, "{$exportPath}/cfg_centreonbroker.yaml");
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
        return 'poller';
    }
}
