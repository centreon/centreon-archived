<?php
namespace Centreon\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

/**
 * Compatibility with Doctrine
 */
class CentreonDBManagerService
{
    /**
     * @var string
     */
    private $defaultManager;

    /**
     * @var array<string,mixed>
     */
    private $manager;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->manager = [
            'configuration_db' => new CentreonDBAdapter($services->get('configuration_db'), $this),
            'realtime_db' => new CentreonDBAdapter($services->get('realtime_db'), $this),
        ];

        $this->defaultManager = 'configuration_db';
    }

    public function getAdapter(string $alias): CentreonDBAdapter
    {
        $manager = array_key_exists($alias, $this->manager) ?
            $this->manager[$alias] :
            null;

        return $manager;
    }

    /**
     * Get default adapter with DB connection
     *
     * @return \Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter
     */
    public function getDefaultAdapter(): CentreonDBAdapter
    {
        return $this->manager[$this->defaultManager];
    }

    /**
     * @param mixed $repository
     */
    public function getRepository($repository): mixed
    {
        $manager = $this->manager[$this->defaultManager]
            ->getRepository($repository);

        return $manager;
    }
}
