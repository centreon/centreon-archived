<?php

namespace Centreon\Infrastructure\HostConfiguration\Repository;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroWriteRepositoryInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostMacroFactoryRdb;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

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
    public function findAllByHost(Host $host): array
    {
        Assertion::notNull($host->getId(), 'Host::id');
        $statement = $this->db->prepare(
            $this->translateDbName('SELECT * FROM `:db`.on_demand_macro_host WHERE host_host_id = :host_id')
        );
        $statement->bindValue(':host_id', $host->getId(), \PDO::PARAM_INT);
        $statement->execute();
        $hostMacros = [];
        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $hostMacros[] = HostMacroFactoryRdb::create($result);
        }
        return $hostMacros;
    }

    /**
     * @inheritDoc
     */
    public function updateMacro(HostMacro $hostMacro): void
    {
        Assertion::notNull($hostMacro->getId(), 'HostMacro::id');
        Assertion::notNull($hostMacro->getHostId(), 'HostMacro::host_id');
        $statement = $this->db->prepare(
            $this->translateDbName(
                'UPDATE `:db`.on_demand_macro_host
                    SET host_macro_name = :new_name,
                        host_macro_value = :new_value,
                        is_password = :is_password,
                        description = :new_description,
                        macro_order = :new_order
                WHERE host_macro_id = :id'
            )
        );
        $statement->bindValue(':new_name', $hostMacro->getName());
        $statement->bindValue(':new_value', $hostMacro->getValue());
        $statement->bindValue(':is_password', $hostMacro->isPassword(), \PDO::PARAM_INT);
        $statement->bindValue(':new_description', $hostMacro->getDescription());
        $statement->bindValue(':new_order', $hostMacro->getOrder(), \PDO::PARAM_INT);
        $statement->bindValue(':id', $hostMacro->getId(), \PDO::PARAM_INT);
        $statement->execute();
    }
}
