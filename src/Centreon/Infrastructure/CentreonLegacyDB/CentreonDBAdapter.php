<?php
namespace Centreon\Infrastructure\CentreonLegacyDB;

use ReflectionClass;
use CentreonDB;
use Centreon\Infrastructure\Service\Expetion\NotFoundException;
use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class CentreonDBAdapter
{

    /**
     * @var \CentreonDB
     */
    private $db;

    /**
     * Construct
     * 
     * @param \CentreonDB $db
     */
    public function __construct(CentreonDB $db)
    {
        $this->db = $db;
    }

    public function getRepository($repository): ServiceEntityRepository
    {
        $interface = ServiceEntityRepository::class;
        $hasInterface = (new ReflectionClass($repository))
            ->isSubclassOf($interface)
        ;

        if ($hasInterface === false) {
            throw new NotFoundException(sprintf('Repository %s must implement %s', $repository, $interface));
        }

        $repositoryInstance = new $repository($this->db);

        return $repositoryInstance;
    }
}
