<?php

namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class TopologyRepository extends ServiceEntityRepository
{
    /**
     * Disable Menus for a Master-to-Remote transition
     *
     * @return bool
     */
    public function disableMenus(): bool
    {
        $sql = file_get_contents( getcwd().'/src/Centreon/Infrastructure/Resources/sql/disablemenus.sql');
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Enable Menus for a Remote-to-Master transition
     *
     * @return bool
     */
    public function enableMenus(): bool
    {
        $sql = file_get_contents(getcwd().'/src/Centreon/Infrastructure/Resources/sql/enablemenus.sql');
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}
