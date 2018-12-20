<?php
namespace CentreonModule\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class ModulesInformationsRepository extends ServiceEntityRepository
{

    /**
     * Get an associative array of all modules vs versions
     */
    public function getAllModuleVsVersion(): array
    {
        $sql = "SELECT `name` AS `id`, `mod_release` AS `version` FROM `modules_informations`";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];
        
        while ($row = $stmt->fetch()) {
            $result[$row['id']] = $row['version'];
        }

        return $result;
    }
}
