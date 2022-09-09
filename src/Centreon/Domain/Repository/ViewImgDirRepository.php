<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class ViewImgDirRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param array $imgList
     * @return array
     */
    public function export(array $imgList = null): array
    {
        if (!$imgList) {
            return [];
        }

        $list = join(',', $imgList ?? []);

        $sql = <<<SQL
SELECT
    t.*
FROM view_img_dir AS t
INNER JOIN view_img_dir_relation AS vidr ON vidr.dir_dir_parent_id = t.dir_id
    AND vidr.img_img_id IN ({$list})
GROUP BY t.dir_id
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
