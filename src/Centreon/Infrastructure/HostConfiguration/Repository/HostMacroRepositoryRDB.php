<?php

namespace Centreon\Infrastructure\HostConfiguration\Repository;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroWriteRepositoryInterface;

/**
 * This class is designed to represent the MariaDb repository to manage host macro,
 *
 * @package Centreon\Infrastructure\HostConfiguration\Repository
 */
class HostMacroRepositoryRDB extends AbstractRepositoryDRB implements
    HostMacroReadRepositoryInterface,
    HostMacroWriteRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function __construct(DatabaseConnection $db, SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
    }

    /**
     * @inheritDoc
     */
    public function addMacroToHost(Host $host, HostMacro $hostMacro): void
    {
        Assertion::notNull($host->getId(), 'Host::id');
        $request = $this->translateDbName(
            'INSERT INTO `:db`.on_demand_macro_host
            (host_host_id, host_macro_name, host_macro_value, is_password, description, macro_order)
            VALUES (:host_id, :name, :value, :is_password, :description, :order)'
        );
        $statement = $this->db->prepare($request);

        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->bindValue(':name', $hostMacro->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':value', $hostMacro->getValue(), \PDO::PARAM_STR);
        $statement->bindValue(':is_password', $hostMacro->isPassword(), \PDO::PARAM_INT);
        $statement->bindValue(':description', $hostMacro->getDescription(), \PDO::PARAM_STR);
        $statement->bindValue(':order', $hostMacro->getOrder(), \PDO::PARAM_INT);
        $statement->execute();

        $hostMacroId = (int)$this->db->lastInsertId();
        $hostMacro->setId($hostMacroId);
    }

    /**
     * @inheritDoc
     */
    public function findOnDemandHostMacros(int $hostId, bool $useInheritance): array
    {
        $request = $this->translateDbName(
            'SELECT
                host.host_id AS host_id, demand.host_macro_id AS id,
                host_macro_name AS name, host_macro_value AS `value`,
                macro_order AS `order`, is_password, description, host_template_model_htm_id
             FROM `:db`.host
                LEFT JOIN `:db`.on_demand_macro_host demand ON host.host_id = demand.host_host_id
             WHERE host.host_id = :host_id'
        );
        $statement = $this->db->prepare($request);

        $hostMacros = [];
        $loop = [];
        $macrosAdded = [];
        while (!is_null($hostId)) {
            if (isset($loop[$hostId])) {
                break;
            }
            $loop[$hostId] = 1;
            $statement->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
            $statement->execute();
            while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $hostId = $record['host_template_model_htm_id'];
                if (is_null($record['name']) || isset($macrosAdded[$record['name']])) {
                    continue;
                }
                $macrosAdded[$record['name']] = 1;
                $record['is_password'] = is_null($record['is_password']) ? 0 : $record['is_password'];
                $hostMacros[] = EntityCreator::createEntityByArray(
                    HostMacro::class,
                    $record
                );
            }
            if (!$useInheritance) {
                break;
            }
        }

        return $hostMacros;
    }
}
