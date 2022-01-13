<?php

namespace Centreon\Infrastructure\HostConfiguration\Repository;

use Centreon\Domain\Common\Assertion\Assertion;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Interfaces\HostMacro\HostMacroWriteRepositoryInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
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
}
