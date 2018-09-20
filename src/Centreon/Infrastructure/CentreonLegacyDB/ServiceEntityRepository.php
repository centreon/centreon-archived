<?php
namespace Centreon\Infrastructure\CentreonLegacyDB;

use CentreonDB;

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
     * Construct
     * 
     * @param \CentreonDB $db
     */
    public function __construct(CentreonDB $db)
    {
        $this->db = $db;
    }
}
