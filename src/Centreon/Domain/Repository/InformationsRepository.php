<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Entity\Informations;
use PDO;

class InformationsRepository extends ServiceEntityRepository
{

    /**
     * Export options
     * 
     * @return \Centreon\Domain\Entity\Informations[]
     */
    public function getAll(): array
    {
        $sql = 'SELECT * FROM informations';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, Informations::class);

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Find one by given key
     * @param string $key
     * @return Informations
     */
    public function getOneByKey($key): ?Informations
    {
        $sql = 'SELECT * FROM informations WHERE `key` = :key LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':key', $key, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, Informations::class);
        $result = $stmt->fetch();

        return $result ?: null;
    }
}
