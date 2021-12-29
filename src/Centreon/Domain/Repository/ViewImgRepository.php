<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class ViewImgRepository extends ServiceEntityRepository
{

    /**
     * Export
     *
     * @param array<mixed> $imgList
     * @return array<mixed>
     */
    public function export(array $imgList = null): array
    {
        if ($imgList === null) {
            $list = join(',', []);
        } else {
            $list = join(',', $imgList);
        }

        if (!$list) {
            return [];
        }

        $sql = <<<SQL
SELECT
    t.*,
    GROUP_CONCAT(vid.dir_alias) AS `img_dirs`
FROM `view_img` AS `t`
LEFT JOIN `view_img_dir_relation` AS `vidr` ON t.img_id = vidr.img_img_id
LEFT JOIN `view_img_dir` AS `vid` ON vid.dir_id = vidr.dir_dir_parent_id
WHERE t.img_id IN ({$list})
GROUP BY t.img_id
ORDER BY t.img_id ASC
LIMIT 0, 5000
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }

    public function truncate(): void
    {
        $sql = <<<SQL
TRUNCATE TABLE `view_img_dir_relation`;
TRUNCATE TABLE `view_img_dir`;
TRUNCATE TABLE `view_img`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    /**
     * Get a chain of the related objects
     *
     * @param int[] $pollerIds
     * @param int[] $hostTemplateChain
     * @param int[] $serviceTemplateChain
     * @return array<mixed>
     */
    public function getChainByPoller(
        array $pollerIds,
        array $hostTemplateChain = null,
        array $serviceTemplateChain = null
    ): array {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
        $hostList = join(',', $hostTemplateChain ?? []);
        $sqlFilterHostList = $hostList ? " OR ehi.host_host_id IN ({$hostList})" : '';
        $sqlFilterHostList2 = $hostList ? " OR hcr2.host_host_id IN ({$hostList})" : '';
        $sqlFilterHostList3 = $hostList ? " OR hcr3.host_host_id IN ({$hostList})" : '';

        $serviceList = join(',', $serviceTemplateChain ?? []);
        $sqlFilterServiceList = $serviceList ? " OR esi4.service_service_id IN ({$serviceList})" : '';
        $sqlFilterServiceList2 = $serviceList ? " OR scr5.service_service_id IN ({$serviceList})" : '';

        $sql = <<<SQL
SELECT l.* FROM (
SELECT
    t.img_id
FROM view_img AS t
INNER JOIN extended_host_information AS ehi ON ehi.ehi_icon_image = t.img_id
    OR ehi.ehi_vrml_image = t.img_id
    OR ehi.ehi_statusmap_image = t.img_id
LEFT JOIN ns_host_relation AS hr ON hr.host_host_id = ehi.host_host_id
WHERE hr.nagios_server_id IN ({$ids}){$sqlFilterHostList} 
GROUP BY t.img_id

UNION

SELECT
    t2.img_id
FROM view_img AS t2
INNER JOIN hostcategories AS hc2 ON hc2.icon_id = t2.img_id
INNER JOIN hostcategories_relation AS hcr2 ON hcr2.hostcategories_hc_id = hc2.hc_id
LEFT JOIN ns_host_relation AS hr2 ON hr2.host_host_id = hcr2.host_host_id
WHERE hr2.nagios_server_id IN ({$ids}){$sqlFilterHostList2} 
GROUP BY t2.img_id

UNION

SELECT
    t3.img_id
FROM view_img AS t3
INNER JOIN hostgroup AS hg3 ON hg3.hg_icon_image = t3.img_id
    OR hg3.hg_map_icon_image = t3.img_id
INNER JOIN hostgroup_relation AS hcr3 ON hcr3.hostgroup_hg_id = hg3.hg_id
LEFT JOIN ns_host_relation AS hr3 ON hr3.host_host_id = hcr3.host_host_id
WHERE hr3.nagios_server_id IN ({$ids}){$sqlFilterHostList3} 
GROUP BY t3.img_id

UNION

SELECT
    t4.img_id
FROM view_img AS t4
INNER JOIN extended_service_information AS esi4 ON esi4.esi_icon_image = t4.img_id
WHERE esi4.service_service_id IN (SELECT t4a.service_service_id
    FROM
        host_service_relation AS t4a
            LEFT JOIN
        hostgroup AS hg4a ON hg4a.hg_id = t4a.hostgroup_hg_id
            LEFT JOIN
        hostgroup_relation AS hgr4a ON hgr4a.hostgroup_hg_id = hg4a.hg_id
            INNER JOIN
        ns_host_relation AS hr4a ON hr4a.host_host_id = t4a.host_host_id
            OR hr4a.host_host_id = hgr4a.host_host_id
    WHERE
        hr4a.nagios_server_id IN ({$ids})
    GROUP BY t4a.service_service_id){$sqlFilterServiceList}
GROUP BY t4.img_id

UNION

SELECT
    t5.img_id
FROM view_img AS t5
INNER JOIN service_categories AS sc5 ON sc5.icon_id = t5.img_id
INNER JOIN service_categories_relation AS scr5 ON scr5.sc_id = sc5.sc_id
WHERE scr5.service_service_id IN (SELECT t5a.service_service_id
    FROM
        host_service_relation AS t5a
            LEFT JOIN
        hostgroup AS hg5a ON hg5a.hg_id = t5a.hostgroup_hg_id
            LEFT JOIN
        hostgroup_relation AS hgr5a ON hgr5a.hostgroup_hg_id = hg5a.hg_id
            INNER JOIN
        ns_host_relation AS hr5a ON hr5a.host_host_id = t5a.host_host_id
            OR hr5a.host_host_id = hgr5a.host_host_id
    WHERE
        hr5a.nagios_server_id IN ({$ids})
    GROUP BY t5a.service_service_id){$sqlFilterServiceList2}
GROUP BY t5.img_id
) AS l
GROUP BY l.img_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[$row['img_id']] = $row['img_id'];
        }

        return $result;
    }
}
