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
 * Return host tab after poller filter
 *
 * @param array $host
 * @param integer $res_id
 * @return array $host
 */
function getFilteredPollers($host, $res_id)
{
    global $pearDB, $hostCache;

    $dbResult = $pearDB->prepare(
        "SELECT COUNT(*) AS count FROM acl_resources_poller_relations WHERE acl_res_id = :resId"
    );
    $dbResult->bindValue(':resId', $res_id, \PDO::PARAM_INT);
    $dbResult->execute();

    $row = $dbResult->fetch();
    $isPollerFilter = $row['count'];

    $hostTmp = $host;
    $dbResult = $pearDB->prepare(
        "SELECT host_host_id " .
        "FROM acl_resources_poller_relations, acl_resources, ns_host_relation " .
        "WHERE acl_resources_poller_relations.acl_res_id = acl_resources.acl_res_id " .
        "AND acl_resources.acl_res_id = :resId " .
        "AND ns_host_relation.nagios_server_id = acl_resources_poller_relations.poller_id " .
        "AND acl_res_activate = '1'"
    );
    $dbResult->bindValue(':resId', $res_id, \PDO::PARAM_INT);
    $dbResult->execute();

    if ($dbResult->rowCount()) {
        $host = array();
        while ($row = $dbResult->fetch()) {
            if (isset($hostTmp[$row['host_host_id']])) {
                $host[$row['host_host_id']] = $hostCache[$row['host_host_id']];
            }
        }
    } else {
        # If result of query is empty and user have poller restriction, clean host table.
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
 * @param integer $res_id
 * @return array $filteredHosts
 */
function getFilteredHostCategories($host, $res_id)
{
    global $pearDB, $hostTemplateCache;

    $dbResult = $pearDB->prepare(
        "SELECT DISTINCT host_host_id FROM " .
        "acl_resources_hc_relations, acl_res_group_relations, acl_resources, hostcategories_relation " .
        "WHERE acl_resources_hc_relations.acl_res_id = acl_resources.acl_res_id " .
        "AND acl_resources.acl_res_id = :resId " .
        "AND hostcategories_relation.hostcategories_hc_id = acl_resources_hc_relations.hc_id " .
        "AND acl_res_activate = '1'"
    );
    $dbResult->bindValue(':resId', $res_id, \PDO::PARAM_INT);
    $dbResult->execute();

    if (!$dbResult->rowCount()) {
        return $host;
    }

    $treatedHosts = array();
    $linkedHosts = array();
    while ($row = $dbResult->fetch()) {
        $linkedHosts[] = $row['host_host_id'];
    }

    $filteredHosts = array();
    while ($linkedHostId = array_pop($linkedHosts)) {
        $treatedHosts[] = $linkedHostId;
        if (isset($host[$linkedHostId])) { // host
            $filteredHosts[$linkedHostId] = $host[$linkedHostId];
        } elseif (isset($hostTemplateCache[$linkedHostId])) { // host template
            foreach ($hostTemplateCache[$linkedHostId] as $hostId) {
                if (isset($host[$hostId])) {
                    $filteredHosts[$hostId] = $host[$hostId];
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
 * @param integer $res_id
 * @return array $tab_categories
 */
function getAuthorizedCategories($res_id)
{
    global $pearDB;

    $tab_categories = array();
    $dbResult = $pearDB->prepare(
        "SELECT sc_id FROM acl_resources_sc_relations, acl_resources " .
        "WHERE acl_resources_sc_relations.acl_res_id = acl_resources.acl_res_id " .
        "AND acl_resources.acl_res_id = :resId " .
        "AND acl_res_activate = '1'"
    );
    $dbResult->bindValue(':resId', $res_id, \PDO::PARAM_INT);
    $dbResult->execute();

    while ($res = $dbResult->fetch()) {
        $tab_categories[$res["sc_id"]] = $res["sc_id"];
    }
    $dbResult->closeCursor();
    unset($res);
    unset($dbResult);
    return $tab_categories;
}

/**
 * Get a service template list for categories
 *
 * @param integer $service_id
 * @return array|void $tabCategory
 */
function getServiceTemplateCategoryList($service_id = null)
{
    global $pearDB, $svcTplCache, $svcCatCache;

    $tabCategory = array();

    if (!$service_id) {
        return;
    }

    if (isset($svcCatCache[$service_id])) {
        foreach ($svcCatCache[$service_id] as $ct_id => $flag) {
            $tabCategory[$ct_id] = $ct_id;
        }
        return $tabCategory;
    }

    /*
     * Init Table of template
     */
    $loopBreak = array();
    while (1) {
        if (isset($svcTplCache[$service_id]) && !isset($loopBreak[$service_id])) {
            if (isset($svcCatCache[$service_id])) {
                foreach ($svcCatCache[$service_id] as $ct_id => $flag) {
                    $tabCategory[$ct_id] = $ct_id;
                }
            }
            $loopBreak[$service_id] = true;
            $service_id = $svcTplCache[$service_id];
        } else {
            return $tabCategory;
        }
    }
}

/**
 * Get ACLs for host from a servicegroup
 *
 * @param $pearDB
 * @param integer $host_id
 * @param integer $resId
 * @return array|void $svc
 */
function getACLSGForHost($pearDB, $host_id, $resId)
{
    global $svcCache, $sgCache;

    if (!$pearDB || !isset($host_id)) {
        return;
    }

    $svc = array();
    if (isset($sgCache[$resId])) {
        foreach ($sgCache[$resId] as $hostId => $tab) {
            if ($host_id == $hostId) {
                foreach ($tab as $svcDesc => $svcId) {
                    $svc[$svcDesc] = $svcId;
                }
            }
        }
    }

    return $svc;
}

/**
 * If the resource ACL has poller filter
 *
 * @param int $res_id The ACL resource id
 * @return bool
 */
function hasPollerFilter($res_id)
{
    global $pearDB;

    if (!is_numeric($res_id)) {
        return false;
    }

    try {
        $res = $pearDB->prepare(
            'SELECT COUNT(*) as c FROM acl_resources_poller_relations WHERE acl_res_id = :resId'
        );
        $res->bindValue(':resId', $res_id, \PDO::PARAM_INT);
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
 * @param int $res_id The ACL resource id
 * @return bool
 */
function hasHostCategoryFilter($res_id)
{
    global $pearDB;

    if (!is_numeric($res_id)) {
        return false;
    }

    try {
        $res = $pearDB->prepare(
            'SELECT COUNT(*) as c FROM acl_resources_hc_relations WHERE acl_res_id = :resId'
        );
        $res->bindValue(':resId', $res_id, \PDO::PARAM_INT);
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
 * @param int $res_id The ACL resource id
 * @return bool
 */
function hasServiceCategoryFilter($res_id)
{
    global $pearDB;

    if (!is_numeric($res_id)) {
        return false;
    }

    try {
        $res = $pearDB->prepare(
            'SELECT COUNT(*) as c FROM acl_resources_sc_relations WHERE acl_res_id = :resId'
        );
        $res->bindValue(':resId', $res_id, \PDO::PARAM_INT);
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

function getAuthorizedServicesHost($host_id, $res_id, $authorizedCategories)
{
    global $pearDB, $svcCache, $hostCache;

    $tab_svc = getMyHostServicesByName($host_id);

    /*
     * Get Service Groups
     */
    $svc_SG = getACLSGForHost($pearDB, $host_id, $res_id);

    $tab_services = array();
    if (count($authorizedCategories)) {
        if ($tab_svc) {
            foreach ($tab_svc as $svc_descr => $svc_id) {
                $tab = getServiceTemplateCategoryList($svc_id);
                foreach ($tab as $t) {
                    if (isset($authorizedCategories[$t])) {
                        $tab_services[$svc_descr] = $svc_id;
                    }
                }
            }
        }
    } else {
        $tab_services = $tab_svc;
        if ($svc_SG) {
            foreach ($svc_SG as $key => $value) {
                $tab_services[$key] = $value;
            }
        }
    }
    return $tab_services;
}

function hostIsAuthorized($host_id, $group_id)
{
    global $pearDB;

    $dbResult = $pearDB->prepare(
        "SELECT rhr.host_host_id " .
        "FROM acl_resources_host_relations rhr, acl_resources res, acl_res_group_relations rgr " .
        "WHERE rhr.acl_res_id = res.acl_res_id " .
        "AND res.acl_res_id = rgr.acl_res_id " .
        "AND rgr.acl_group_id = :groupId " .
        "AND rhr.host_host_id = :hostId " .
        "AND res.acl_res_activate = '1'"
    );
    $dbResult->bindValue(':groupId', $group_id, \PDO::PARAM_INT);
    $dbResult->bindValue(':hostId', $host_id, \PDO::PARAM_INT);
    $dbResult->execute();

    if ($dbResult->rowCount()) {
        return true;
    }

    try {
        $dbRes2 = $pearDB->prepare(
            "SELECT hgr.host_host_id FROM " .
            "hostgroup_relation hgr, acl_resources_hg_relations rhgr, acl_resources res, acl_res_group_relations rgr " .
            "WHERE rhgr.acl_res_id = res.acl_res_id " .
            "AND res.acl_res_id = rgr.acl_res_id " .
            "AND rgr.acl_group_id = :groupId " .
            "AND hgr.hostgroup_hg_id = rhgr.hg_hg_id " .
            "AND hgr.host_host_id = :hostId " .
            "AND res.acl_res_activate = '1' " .
            "AND hgr.host_host_id NOT IN (SELECT host_host_id FROM acl_resources_hostex_relations " .
            "WHERE acl_res_id = rhgr.acl_res_id)"
        );
        $dbRes2->bindValue(':groupId', $group_id, \PDO::PARAM_INT);
        $dbRes2->bindValue(':hostId', $host_id, \PDO::PARAM_INT);
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
function getMyHostServicesByName($host_id = null)
{
    global $hsRelation, $svcCache;

    if (!$host_id) {
        return;
    }

    $hSvs = array();
    if (isset($hsRelation[$host_id])) {
        foreach ($hsRelation[$host_id] as $service_id => $flag) {
            if (isset($svcCache[$service_id])) {
                $service_description = str_replace('#S#', '/', $svcCache[$service_id]);
                $service_description = str_replace('#BS#', '\\', $service_description);
                $hSvs[$service_description] = html_entity_decode($service_id, ENT_QUOTES);
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
    $sql = "SELECT meta_id FROM acl_resources_meta_relations WHERE acl_res_id = {$db->escape($resId)}";
    $res = $db->query($sql);
    $arr = array();
    if ($res->rowCount()) {
        $hostId = $metaObj->getRealHostId();
        while ($row = $res->fetch()) {
            $svcId = $metaObj->getRealServiceId($row['meta_id']);
            $arr['_Module_Meta']['meta_' . $row['meta_id']] = "$hostId,$svcId";
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
