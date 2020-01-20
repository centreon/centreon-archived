<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace CentreonAutoDiscovery\Infrastructure\Mapper;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Monitoring\Host;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use CentreonAutoDiscovery\Domain\Mapper\DiscoveredHost;
use CentreonAutoDiscovery\Domain\Mapper\Interfaces\MapperRepositoryInterface;
use CentreonAutoDiscovery\Domain\Mapper\MapperRule;

class MapperRepositoryRDB extends AbstractRepositoryDRB implements MapperRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * MapperRepositoryRDB constructor.
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function __construct(
        DatabaseConnection $db,
        SqlRequestParametersTranslator $sqlRequestTranslator
    ) {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
    }

    /**
     * @inheritDoc
     */
    public function findMappersToApplyByJob(int $jobId): array
    {
        $request = $this->translateDbName(
            'SELECT * FROM `:db`.mod_host_disco_modifier WHERE job_id = :job_id ORDER BY `order`'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':job_id', $jobId, \PDO::PARAM_INT);
        $statement->execute();

        $mappersToApply = [];
        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $mappersToApply[] = EntityCreator::createEntityByArray(
                MapperRule::class,
                $result
            );
        }
        return $mappersToApply;
    }

    /**
     * @inheritDoc
     */
    public function findDiscoveredHostsByJob(int $jobId): array
    {
        $request = $this->translateDbName(
            'SELECT SQL_CALC_FOUND_ROWS * FROM `:db`.mod_host_disco_host WHERE job_id = :job_id'
        );

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY `id` ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);
        $statement->bindValue(':job_id', $jobId, \PDO::PARAM_INT);
        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $discoveredHosts = [];
        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $discoveredHosts[] = EntityCreator::createEntityByArray(
                DiscoveredHost::class,
                $result
            );
        }
        return $discoveredHosts;
    }
}
