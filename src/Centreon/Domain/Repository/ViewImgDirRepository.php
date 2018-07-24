<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Entity\ViewImgDir;
use PDO;

class ViewImgDirRepository extends ServiceEntityRepository
{

    /**
     * Export options
     * 
     * @return \Centreon\Domain\Entity\ViewImg[]
     */
    public function export(): array
    {
        $sql = <<<SQL
SELECT vid.dir_id AS `dirId`,
    vid.dir_name AS `dirName`,
    vid.dir_alias AS `dirAlias`,
    vid.dir_comment AS `dirComment`
FROM view_img_dir as vid
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, ViewImgDir::class);

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
