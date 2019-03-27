<?php
namespace Centreon\Infrastructure\CentreonLegacyDB;

use CentreonDB;
use Centreon\Infrastructure\Service\CentreonDBManagerService;

/**
 * Compatibility with Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
 */
abstract class ServiceEntityRepository
{

    /**
     * @var \CentreonDB
     */
    protected $db;

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $manager;

    /**
     * Construct
     * 
     * @param \CentreonDB $db
     * @param \Centreon\Infrastructure\Service\CentreonDBManagerService $manager
     */
    public function __construct(CentreonDB $db, CentreonDBManagerService $manager = null)
    {
        $this->db = $db;
        $this->manager = $manager;
    }
}
