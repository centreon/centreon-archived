<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Entity\ViewImg;
use PDO;

class ViewImgRepository extends ServiceEntityRepository
{

    /**
     * Export options
     * 
     * @return \Centreon\Domain\Entity\ViewImg[]
     */
    public function export(): array
    {
        $sql = <<<SQL
SELECT vi.img_id AS `imgId`,
    vi.img_name AS `imgName`,
    vi.img_path AS `imgPath`,
    vi.img_comment AS `imgComment`,
    GROUP_CONCAT(vid.dir_alias) AS `imgDirs`
FROM `view_img` AS `vi`
LEFT JOIN `view_img_dir_relation` AS `vidr` ON vi.img_id = vidr.img_img_id
LEFT JOIN `view_img_dir` AS `vid` ON vid.dir_id = vidr.dir_dir_parent_id
GROUP BY vi.img_id
ORDER BY vi.img_id ASC
LIMIT 0, 1500
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
