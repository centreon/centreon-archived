<?php
namespace Centreon\Infrastructure\CentreonLegacyDB;

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use ReflectionClass;
use CentreonDB;

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
        $ref = new ReflectionClass($repository);
        $hasInterface = $ref->isSubclassOf($interface);

        if ($hasInterface === false) {
            throw new NotFoundException(sprintf('Repository %s must implement %s', $repository, $interface));
        }

        $repositoryInstance = new $repository($this->db);
        return $repositoryInstance;
    }
}
