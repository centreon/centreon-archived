<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

/**
 * Init functions
 */

function microtime_float2()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

/**
 * send a formatted message before exiting the script
 * @param $msg
 */
function programExit($msg)
{
    echo "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n";
    exit;
}

/**
 * set the `running` value to 0 to remove the DB's virtual lock
 * @param integer $appId , the process Id
 */
function removeLock(int $appId):void
{
    global $pearDB;

    if ($appId === 0) {
        programExit("Error the process Id can't be null.");
    }
    try {
        $stmt = $pearDB->prepare(
            "UPDATE cron_operation SET running = '0'
            WHERE id = :appId"
        );
        $stmt->bindValue(':appId', $appId, \PDO::PARAM_INT);
        $stmt->execute();
    } catch (\PDOException $e) {
        programExit("Error can't unlock the process in the cron_operation table.");
    }
}

/**
 * set the `running` value to 1 to set a virtual lock on the DB
 * @param integer $appId , the process Id
 */
function putALock(int $appId):void
{
    global $pearDB;

    if ($appId === 0) {
        programExit("Error the process Id can't be null.");
    }
    try {
        $stmt = $pearDB->prepare(
            "UPDATE cron_operation SET running = '1', time_launch = :currentTime
            WHERE id = :appId"
        );
        $stmt->bindValue(':appId', $appId, \PDO::PARAM_INT);
        $stmt->bindValue(':currentTime', time(), \PDO::PARAM_INT);
        $stmt->execute();
    } catch (\PDOException $e) {
        programExit("Error can't lock the process in the cron_operation table.");
    }
}

/**
 * get centAcl state in the DB
 * @return array $data
 */
function getCentAclRunningState()
{
    global $pearDB;
    $data = [];

    try {
        $dbResult = $pearDB->query(
            "SELECT id, running FROM cron_operation WHERE name LIKE 'centAcl.php'"
        );
        $data = $dbResult->fetch();
    } catch (\PDOException $e) {
        programExit("Error can't check state while process is running.");
    }

    return $data;
}

/**
 * Return host tab after poller filter
 *
 * @param array $host
 * @param integer $resId
 * @return array $host
 */
function getFilteredPollers($host, $resId)
{
    global $pearDB;

    $dbResult = $pearDB->prepare(
        "SELECT COUNT(*) AS count FROM acl_resources_poller_relations WHERE acl_res_id = :resId"
    );
    $dbResult->bindValue(':resId', $resId, \PDO::PARAM_INT);
    $dbResult->execute();
    $row = $dbResult->fetch();
    $isPollerFilter = $row['count'];

    $hostTmp = $host;
    $dbResult = $pearDB->prepare(
        "SELECT host_host_id
        FROM acl_resources_poller_relations, acl_resources, ns_host_relation
        WHERE acl_resources_poller_relations.acl_res_id = acl_resources.acl_res_id
        AND acl_resources.acl_res_id = :resId
        AND ns_host_relation.nagios_server_id = acl_resources_poller_relations.poller_id
        AND acl_res_activate = '1'"
    );
    $dbResult->bindValue(':resId', $resId, \PDO::PARAM_INT);
    $dbResult->execute();

    if ($dbResult->rowCount()) {
        $host = array();
        while ($row = $dbResult->fetch()) {
            if (isset($hostTmp[$row['host_host_id']])) {
                $host[$row['host_host_id']] = 1;
            }
        }
    } else {
        // If result of query is empty and user have poller restrictions, clean host table.
        if ($isPollerFilter) {
            $host = array();
        }
    }
    return $host;
}

/**
 * Return host tab after host categories filter.
 * Add a cache for filtered ACL rights
 * avoiding to recalculate it at each occurrence.
 *
 * @param array $host
 * @param integer $resId
 * @return array $filteredHosts
 */
function getFilteredHostCategories($host, $resId)
{
    global $pearDB, $hostTemplateCache;

    $dbResult = $pearDB->prepare(
        "SELECT DISTINCT host_host_id
        FROM acl_resources_hc_relations, acl_res_group_relations, acl_resources, hostcategories_relation
        WHERE acl_resources_hc_relations.acl_res_id = acl_resources.acl_res_id
        AND acl_resources.acl_res_id = :resId
        AND hostcategories_relation.hostcategories_hc_id = acl_resources_hc_relations.hc_id
        AND acl_res_activate = '1'"
    );
    $dbResult->bindValue(':resId', $resId, \PDO::PARAM_INT);
    $dbResult->execute();

    if (!$dbResult->rowCount()) {
        return $host;
    }

    $treatedHosts = [];
    $linkedHosts = [];
    while ($row = $dbResult->fetch()) {
        $linkedHosts[] = $row['host_host_id'];
    }

    $filteredHosts = [];
    while ($linkedHostId = array_pop($linkedHosts)) {
        $treatedHosts[] = $linkedHostId;
        if (isset($host[$linkedHostId])) { // host
            $filteredHosts[$linkedHostId] = 1;
        } elseif (isset($hostTemplateCache[$linkedHostId])) { // host template
            foreach ($hostTemplateCache[$linkedHostId] as $hostId) {
                if (isset($host[$hostId])) {
                    $filteredHosts[$hostId] = 1;
                }
                if (isset($hostTemplateCache[$hostId])) {
                    foreach ($hostTemplateCache[$hostId] as $hostId2) {
                        if (!in_array($hostId2, $linkedHosts) && !in_array($hostId2, $treatedHosts)) {
                            $linkedHosts[] = $hostId2;
                        }
                    }
                }
            }
        }
    }

    return $filteredHosts;
}

/**
 * Return enable categories for this resource access
 *
 * @param integer $resId
 * @return array $tabCategories
 */
function getAuthorizedCategories($resId)
{
    global $pearDB;

    $tabCategories = [];

    $dbResult = $pearDB->prepare(
        "SELECT sc_id FROM acl_resources_sc_relations, acl_resources
        WHERE acl_resources_sc_relations.acl_res_id = acl_resources.acl_res_id
        AND acl_resources.acl_res_id = :resId
        AND acl_res_activate = '1'"
    );
    $dbResult->bindValue(':resId', $resId, \PDO::PARAM_INT);
    $dbResult->execute();

    while ($res = $dbResult->fetch()) {
        $tabCategories[$res["sc_id"]] = $res["sc_id"];
    }
    $dbResult->closeCursor();
    unset($res);
    unset($dbResult);
    return $tabCategories;
}

/**
 * Get a service template list for categories
 *
 * @param integer $serviceId
 * @return array|void $tabCategory
 */
function getServiceTemplateCategoryList($serviceId = null)
{
    global $svcTplCache, $svcCatCache;

    $tabCategory = array();

    if (!$serviceId) {
        return;
    }

    if (isset($svcCatCache[$serviceId])) {
        foreach ($svcCatCache[$serviceId] as $ctId => $flag) {
            $tabCategory[$ctId] = $ctId;
        }
        return $tabCategory;
    }

    /*
     * Init Table of template
     */
    $loopBreak = array();
    while (1) {
        if (isset($svcTplCache[$serviceId]) && !isset($loopBreak[$serviceId])) {
            $serviceId = $svcTplCache[$serviceId];
            $tabCategory = getServiceTemplateCategoryList($serviceId);
            $loopBreak[$serviceId] = true;
        } else {
            return $tabCategory;
        }
    }
}

/**
 * Get ACLs for host from a servicegroup
 *
 * @param $pearDB
 * @param integer $hostId
 * @param integer $resId
 * @return array|void $svc
 */
function getACLSGForHost($pearDB, $hostId, $resId)
{
    global $sgCache;

    if (!$pearDB || !isset($hostId)) {
        return;
    }

    $svc = array();
    if (isset($sgCache[$resId])) {
        foreach ($sgCache[$resId] as $sgHostId => $tab) {
            if ($hostId == $sgHostId) {
                foreach (array_keys($tab) as $serviceId) {
                    $svc[$serviceId] = 1;
                }
            }
        }
    }

    return $svc;
}

/**
 * If the resource ACL has poller filter
 *
 * @param int $resId The ACL resource id
 * @return bool
 */
function hasPollerFilter($resId)
{
    global $pearDB;

    if (!is_numeric($resId)) {
        return false;
    }

    try {
        $res = $pearDB->prepare(
            'SELECT COUNT(*) as c FROM acl_resources_poller_relations WHERE acl_res_id = :resId'
        );
        $res->bindValue(':resId', $resId, \PDO::PARAM_INT);
        $res->execute();
    } catch (\PDOException $e) {
        return false;
    }
    $row = $res->fetch();
    if ($row['c'] > 0) {
        return true;
    }
    return false;
}

/**
 * If the resource ACL has host category filter
 *
 * @param int $resId The ACL resource id
 * @return bool
 */
function hasHostCategoryFilter($resId)
{
    global $pearDB;

    if (!is_numeric($resId)) {
        return false;
    }

    try {
        $res = $pearDB->prepare(
            'SELECT COUNT(*) as c FROM acl_resources_hc_relations WHERE acl_res_id = :resId'
        );
        $res->bindValue(':resId', $resId, \PDO::PARAM_INT);
        $res->execute();
    } catch (\PDOException $e) {
        return false;
    }
    $row = $res->fetch();
    if ($row['c'] > 0) {
        return true;
    }
    return false;
}

/**
 * If the resource ACL has service category filter
 *
 * @param int $resId The ACL resource id
 * @return bool
 */
function hasServiceCategoryFilter($resId)
{
    global $pearDB;

    if (!is_numeric($resId)) {
        return false;
    }

    try {
        $res = $pearDB->prepare(
            'SELECT COUNT(*) as c FROM acl_resources_sc_relations WHERE acl_res_id = :resId'
        );
        $res->bindValue(':resId', $resId, \PDO::PARAM_INT);
        $res->execute();
    } catch (\PDOException $e) {
        return false;
    }
    $row = $res->fetch();
    if ($row['c'] > 0) {
        return true;
    }
    return false;
}

function getAuthorizedServicesHost($hostId, $resId, $authorizedCategories)
{
    global $pearDB;

    $tabSvc = getMyHostServicesByName($hostId);

    /*
     * Get Service Groups
     */
    $svcSg = getACLSGForHost($pearDB, $hostId, $resId);

    $tabServices = array();
    if (count($authorizedCategories)) {
        if ($tabSvc) {
            foreach (array_keys($tabSvc) as $serviceId) {
                $tab = getServiceTemplateCategoryList($serviceId);
                foreach ($tab as $t) {
                    if (isset($authorizedCategories[$t])) {
                        $tabServices[$serviceId] = 1;
                    }
                }
            }
        }
    } else {
        $tabServices = $tabSvc;
        if ($svcSg) {
            foreach (array_keys($svcSg) as $serviceId) {
                $tabServices[$serviceId] = 1;
            }
        }
    }
    return $tabServices;
}

function hostIsAuthorized($hostId, $groupId)
{
    global $pearDB;

    $dbResult = $pearDB->prepare(
        "SELECT rhr.host_host_id
        FROM acl_resources_host_relations rhr, acl_resources res, acl_res_group_relations rgr
        WHERE rhr.acl_res_id = res.acl_res_id
        AND res.acl_res_id = rgr.acl_res_id
        AND rgr.acl_group_id = :groupId
        AND rhr.host_host_id = :hostId
        AND res.acl_res_activate = '1'"
    );
    $dbResult->bindValue(':groupId', $groupId, \PDO::PARAM_INT);
    $dbResult->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
    $dbResult->execute();

    if ($dbResult->rowCount()) {
        return true;
    }

    try {
        $dbRes2 = $pearDB->prepare(
            "SELECT hgr.host_host_id
            FROM hostgroup_relation hgr, acl_resources_hg_relations rhgr, acl_resources res, acl_res_group_relations rgr
            WHERE rhgr.acl_res_id = res.acl_res_id
            AND res.acl_res_id = rgr.acl_res_id
            AND rgr.acl_group_id = :groupId
            AND hgr.hostgroup_hg_id = rhgr.hg_hg_id
            AND hgr.host_host_id = :hostId
            AND res.acl_res_activate = '1'
            AND hgr.host_host_id NOT IN (SELECT host_host_id FROM acl_resources_hostex_relations
            WHERE acl_res_id = rhgr.acl_res_id)"
        );
        $dbRes2->bindValue(':groupId', $groupId, \PDO::PARAM_INT);
        $dbRes2->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $dbRes2->execute();
    } catch (\PDOException $e) {
        print "DB Error : " . $e->getMessage() . "<br />";
    }
    if ($dbRes2->rowCount()) {
        return true;
    }

    return false;
}

/*
 * Retrieve service description
 */
function getMyHostServicesByName($hostId = null)
{
    global $hsRelation, $svcCache;

    if (!$hostId) {
        return;
    }

    $hSvs = array();
    if (isset($hsRelation[$hostId])) {
        foreach ($hsRelation[$hostId] as $serviceId => $flag) {
            if (isset($svcCache[$serviceId])) {
                $hSvs[$serviceId] = 1;
            }
        }
    }
    return $hSvs;
}

/**
 * Get meta services
 *
 * @param int $resId
 * @param CentreonDB $db
 * @param CentreonMeta $metaObj
 * @return array
 */
function getMetaServices($resId, $db, $metaObj)
{
    $sql = "SELECT meta_id FROM acl_resources_meta_relations WHERE acl_res_id = " . (int) $resId;
    $res = $db->query($sql);
    $arr = array();
    if ($res->rowCount()) {
        $hostId = $metaObj->getRealHostId();
        while ($row = $res->fetch()) {
            $svcId = $metaObj->getRealServiceId($row['meta_id']);
            $arr[$hostId][$svcId] = 1;
        }
    }
    return $arr;
}

function getModulesExtensionsPaths($db)
{
    $extensionsPaths = array();
    $res = $db->query("SELECT name FROM modules_informations");
    while ($row = $res->fetch()) {
        $extensionsPaths = array_merge(
            $extensionsPaths,
            glob(_CENTREON_PATH_ . '/www/modules/' . $row['name'] . '/extensions/acl/')
        );
    }

    return $extensionsPaths;
}
