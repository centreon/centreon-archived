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
    vi.img_comment AS `imgComment`
FROM view_img as vi
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, ViewImg::class);

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
