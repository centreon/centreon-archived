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
        $result = $stmt->fetch();
        $informations = null;
        if ($result) {
            $informations = new Informations();
            $informations->setKey($result['key']);
            $informations->setValue($result['value']);
        }

        return $informations;
    }

    /**
     * Turn on or off remote flag in database
     * @param string $flag ('yes' or 'no')
     * @return void
     */
    public function toggleRemote(string $flag): void
    {
        $sql = "UPDATE `informations` SET `value`= :state WHERE `key` = 'isRemote'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':state', $flag, PDO::PARAM_STR);
        $stmt->execute();

        $centralState = ($flag === 'yes') ? 'no' : 'yes';
        $sql = "UPDATE `informations` SET `value`= :state WHERE `key` = 'isCentral'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':state', $centralState, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Authorize Master to make calls to remote for Tasks
     * @param string $ip
     * @return void
     */
    public function authorizeMaster(string $ip): void
    {
        $sql = "DELETE FROM `informations` WHERE `key` = 'authorizedMaster'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        /*
         * resolve the address down to IP
         */
        $ipAddress = gethostbyname($ip);
        $sql = "INSERT INTO `informations` (`key`, `value`) VALUES ('authorizedMaster', :ip)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
        $stmt->execute();
    }
}
