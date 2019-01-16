<?php
namespace CentreonModule\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class WidgetModelsRepository extends ServiceEntityRepository
{

    /**
     * Get an associative array of all widgets vs versions
     */
    public function getAllWidgetVsVersion(): array
    {
        $sql = "SELECT `directory` AS `id`, `version` FROM `widget_models`";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];
        
        while ($row = $stmt->fetch()) {
            $result[$row['id']] = $row['version'];
        }

        return $result;
    }
}
