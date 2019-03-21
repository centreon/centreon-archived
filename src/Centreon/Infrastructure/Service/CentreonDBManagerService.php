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
     * @var \Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter
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
            'configuration_db' => new CentreonDBAdapter($services->get('configuration_db')),
            'realtime_db' => new CentreonDBAdapter($services->get('realtime_db')),
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

    public function getRepository($repository): ServiceEntityRepository
    {
        $manager = $this->manager[$this->defaultManager]
            ->getRepository($repository);

        return $manager;
    }
}
