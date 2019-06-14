#!@PHP_BIN@
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
 * Define the period between to update in second for ldap user/group
 */
define('LDAP_UPDATE_PERIOD', 3600);

require_once realpath(__DIR__ . "/../config/centreon.config.php");
include_once _CENTREON_PATH_ . "/cron/centAcl-Func.php";
include_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
include_once _CENTREON_PATH_ . "/www/class/centreonLDAP.class.php";
include_once _CENTREON_PATH_ . "/www/class/centreonMeta.class.php";
include_once _CENTREON_PATH_ . "/www/class/centreonContactgroup.class.php";

$centreonDbName = $conf_centreon['db'];

function programExit($msg)
{
    echo "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n";
    exit;
}

$nbProc = exec('ps -o args -p $(pidof -o $$ -o $PPID -o %PPID -x php || echo 1000000) | grep -c ' . __FILE__);
if ((int) $nbProc > 0) {
    programExit("More than one centAcl.php process currently running. Going to exit...");
}

ini_set('max_execution_time', 0);

try {
    /*
     * Init values
     */
    $debug = 0;

    /*
     * Init DB connections
     */
    $pearDB = new CentreonDB();
    $pearDBO = new CentreonDB("centstorage");

    $metaObj = new CentreonMeta($pearDB);
    $cgObj = new CentreonContactgroup($pearDB);

    /*
     * Lock in MySQL
     */
    try {
        $dbResult = $pearDB->query(
            "SELECT id, running FROM cron_operation WHERE name LIKE 'centAcl.php'"
        );
    } catch (\PDOException $e) {
        print "Error can't check when process is running.";
        exit(1);
    }

    $data = $dbResult->fetch();

    $is_running = $data["running"];
    $appID = $data["id"];
    $beginTime = time();

    if (!$data || count($data) == 0) {
        try {
            $pearDB->query(
                "INSERT INTO cron_operation (name, system, activate) " .
                "VALUES ('centAcl.php', '1', '1')"
            );
            $dbResult = $pearDB->query(
                "SELECT id, running FROM cron_operation WHERE name LIKE 'centAcl.php'"
            );
        } catch (\PDOException $e) {
            print "Error can't check when process is running.";
            exit(1);
        }
        $data = $dbResult->fetch();
        $appID = $data["id"];
        $is_running = 0;
    }

    if ($is_running == 0) {
        $dbResult = $pearDB->query(
            "UPDATE cron_operation SET running = '1', time_launch = '" . time() .
            "' WHERE id = ' . $appID . '"
        );
    } else {
        if ($nbProc <= 1) {
            $errorMessage = "According to DB another instance of centAcl.php is already running and I found " .
                $nbProc . " process...\n";
            $errorMessage .= "Executing query: UPDATE cron_operation SET running = 0 WHERE id =  ' . $appID . '";
            $pearDB->query("UPDATE cron_operation SET running = '0' WHERE id = ' . $appID . '");
        } else {
            $errorMessage = "centAcl marked as running. Exiting...";
        }
        programExit($errorMessage);
    }

    $resourceCache = array();

    /** **********************************************
     * Sync ACL with ldap
     */
    $res = $pearDB->query(
        "SELECT `key`, `value` FROM `options` 
        WHERE `key` IN ('ldap_auth_enable', 'ldap_last_acl_update')"
    );
    while ($row = $res->fetch()) {
        switch ($row['key']) {
            case 'ldap_auth_enable':
                $ldap_enable = $row['value'];
                break;
            case 'ldap_last_acl_update':
                $ldap_last_update = $row['value'];
                break;
        }
    }

    /** ********************************************
     * If the ldap is enable and the last check
     * is greater than the update period
     */
    if ($ldap_enable == 1 && $ldap_last_update < (time() - LDAP_UPDATE_PERIOD)) {
        $cgObj->syncWithLdap();
    }

    /** **********************************************
     * Remove data from old groups (deleted groups)
     */
    $aclGroupToDelete = "SELECT DISTINCT acl_group_id " .
        "FROM " . $centreonDbName . ".acl_groups WHERE acl_group_activate = '1'";
    $aclGroupToDelete2 = "SELECT DISTINCT acl_group_id FROM " . $centreonDbName . ".acl_res_group_relations";
    $pearDBO->query("DELETE FROM centreon_acl WHERE group_id NOT IN (" . $aclGroupToDelete . ")");
    $pearDBO->query("DELETE FROM centreon_acl WHERE group_id NOT IN (" . $aclGroupToDelete2 . ")");

    /** ***********************************************
     * Check if some ACL have global options selected for
     * all the resources
     */
    $res = $pearDB->query(
        "SELECT acl_res_id, all_hosts, all_hostgroups, all_servicegroups
        FROM acl_resources WHERE acl_res_activate = '1'
        AND (all_hosts IS NOT NULL OR all_hostgroups IS NOT NULL OR all_servicegroups IS NOT NULL)"
    );
    while ($row = $res->fetch()) {
        // manage acl_resources.changed flag
        $aclResourcesUpdated = false;

        /**
         * Add Hosts
         */
        if ($row['all_hosts']) {
            $res1 = $pearDB->prepare(
                "SELECT host_id FROM host WHERE host_id NOT IN (SELECT DISTINCT host_host_id
                FROM acl_resources_host_relations WHERE acl_res_id = :aclResId)
                AND host_register = '1'"
            );
            $res1->bindValue(':aclResId', $row['acl_res_id'], \PDO::PARAM_INT);
            $res1->execute();

            if ($res1->rowCount()) {
                // set acl_resources.changed flag to 1
                $aclResourcesUpdated = true;
            }

            while ($rowData = $res1->fetch()) {
                $stmt = $pearDB->prepare(
                    "INSERT INTO acl_resources_host_relations (host_host_id, acl_res_id)
                    VALUES (:hostId, :aclResId)"
                );
                $stmt->bindValue(':hostId', $rowData['host_id'], \PDO::PARAM_INT);
                $stmt->bindValue(':aclResId', $row['acl_res_id'], \PDO::PARAM_INT);
                $stmt->execute();
            }
            $res1->closeCursor();
        }

        /**
         * Add Hostgroups
         */
        if ($row['all_hostgroups']) {
            $res1 = $pearDB->prepare(
                "SELECT hg_id FROM hostgroup
                WHERE hg_id NOT IN (
                    SELECT DISTINCT hg_hg_id FROM acl_resources_hg_relations
                    WHERE acl_res_id = :aclResId)"
            );
            $res1->bindValue(':aclResId', $row['acl_res_id'], \PDO::PARAM_INT);
            $res1->execute();


            if ($res1->rowCount()) {
                // set acl_resources.changed flag to 1
                $aclResourcesUpdated = true;
            }

            while ($rowData = $res1->fetch()) {
                $stmt = $pearDB->prepare(
                    "INSERT INTO acl_resources_hg_relations (hg_hg_id, acl_res_id)
                    VALUES (:hgId, :aclResId)"
                );
                $stmt->bindValue(':hgId', $rowData['hg_id'], \PDO::PARAM_INT);
                $stmt->bindValue(':aclResId', $row['acl_res_id'], \PDO::PARAM_INT);
                $stmt->execute();
            }
            $res1->closeCursor();
        }

        /**
         * Add Servicesgroups
         */
        if ($row['all_servicegroups']) {
            $res1 = $pearDB->prepare(
                "SELECT sg_id FROM servicegroup 
                WHERE sg_id NOT IN (
                    SELECT DISTINCT sg_id FROM acl_resources_sg_relations
                    WHERE acl_res_id = :aclResId)"
            );
            $res1->bindValue(':aclResId', $row['acl_res_id'], \PDO::PARAM_INT );
            $res1->execute();

            if ($res1->rowCount()) {
                // set acl_resources.changed flag to 1
                $aclResourcesUpdated = true;
            }

            while ($rowData = $res1->fetch()) {
                $stmt = $pearDB->prepare(
                    "INSERT INTO acl_resources_sg_relations (sg_id, acl_res_id)
                    VALUES (:sgIg, :aclResId)"
                );
                $stmt->bindValue(':sgId', $rowData['sg_id'], \PDO::PARAM_INT);
                $stmt->bindValue(':aclResId', $row['acl_res_id'], \PDO::PARAM_INT);
                $stmt->execute();
            }
            $res1->closeCursor();
        }

        if ($aclResourcesUpdated) {
            $stmt = $pearDB->prepare(
                "UPDATE acl_resources SET changed = '1' WHERE acl_res_id = :aclResId"
            );
            $stmt->bindValue(':aclResId', $row['acl_res_id'], \PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    $res->closeCursor();

    /**
     * Check that the ACL resources have changed
     *  if no : go away.
     *  if yes : let's go to build cache and update database
     */

    $tabGroups = array();
    $dbResult1 = $pearDB->query(
        "SELECT DISTINCT acl_groups.acl_group_id
        FROM acl_res_group_relations, `acl_groups`, `acl_resources`
        WHERE acl_groups.acl_group_id = acl_res_group_relations.acl_group_id
            AND acl_res_group_relations.acl_res_id = acl_resources.acl_res_id
            AND acl_groups.acl_group_activate = '1'
            AND (acl_groups.acl_group_changed = '1' OR acl_resources.changed = '1')"
    );
    while ($result = $dbResult1->fetch()) {
        $tabGroups[] = $result['acl_group_id'];
    }
    $dbResult1->closeCursor();
    unset($result);

    if (count($tabGroups)) {

        /** ***********************************************
         *  Cache for hosts and host Templates
         *
         */
        $hostTemplateCache = array();
        $res = $pearDB->query(
            "SELECT host_host_id, host_tpl_id FROM host_template_relation"
        );
        while ($row = $res->fetch()) {
            if (!isset($hostTemplateCache[$row['host_tpl_id']])) {
                $hostTemplateCache[$row['host_tpl_id']] = array();
            }
            $hostTemplateCache[$row['host_tpl_id']][$row['host_host_id']] = $row['host_host_id'];
        }
        $res->closeCursor();

        $hostCache = array();
        $dbResult = $pearDB->query(
            "SELECT host_id, host_name FROM host WHERE host_register IN ('1', '2')"
        );
        while ($h = $dbResult->fetch()) {
            $hostCache[$h["host_id"]] = $h["host_name"];
        }
        $dbResult->closeCursor();
        unset($h);

        /** ***********************************************
         * Cache for host poller relation
         */
        $hostPollerCache = array();
        $res = $pearDB->query(
            "SELECT nagios_server_id, host_host_id FROM ns_host_relation"
        );
        while ($row = $res->fetch()) {
            if (!isset($hostPollerCache[$row['nagios_server_id']])) {
                $hostPollerCache[$row['nagios_server_id']] = array();
            }
            $hostPollerCache[$row['nagios_server_id']][$row['host_host_id']] = $row['host_host_id'];
        }

        /** ***********************************************
         * Get all included Hosts
         */
        $hostIncCache = array();
        $dbResult = $pearDB->query(
            "SELECT host_id, host_name, acl_res_id
            FROM `host`, acl_resources_host_relations
            WHERE acl_resources_host_relations.host_host_id = host.host_id
                AND host.host_register = '1'"
        );
        while ($h = $dbResult->fetch()) {
            if (!isset($hostIncCache[$h["acl_res_id"]])) {
                $hostIncCache[$h["acl_res_id"]] = array();
            }
            $hostIncCache[$h["acl_res_id"]][$h["host_id"]] = $h["host_name"];
        }
        $dbResult->closeCursor();

        /** ***********************************************
         * Get all excluded Hosts
         */
        $hostExclCache = array();
        $dbResult = $pearDB->query(
            "SELECT host_id, host_name, acl_res_id
            FROM `host`, acl_resources_hostex_relations
            WHERE acl_resources_hostex_relations.host_host_id = host.host_id
                AND host.host_register = '1'"
        );
        while ($h = $dbResult->fetch()) {
            if (!isset($hostExclCache[$h["acl_res_id"]])) {
                $hostExclCache[$h["acl_res_id"]] = array();
            }
            $hostExclCache[$h["acl_res_id"]][$h["host_id"]] = $h["host_name"];
        }
        $dbResult->closeCursor();

        /** ***********************************************
         * Service Cache
         */
        $svcCache = array();
        $dbResult = $pearDB->query(
            "SELECT service_id, service_description FROM `service`
            WHERE service_register = '1'"
        );
        while ($s = $dbResult->fetch()) {
            $svcCache[$s["service_id"]] = $s["service_description"];
        }
        $dbResult->closeCursor();

        /** ***********************************************
         * Host Host relation
         */
        $hostHGRelation = array();
        $dbResult = $pearDB->query("SELECT * FROM hostgroup_relation");
        while ($hg = $dbResult->fetch()) {
            if (!isset($hostHGRelation[$hg["hostgroup_hg_id"]])) {
                $hostHGRelation[$hg["hostgroup_hg_id"]] = array();
            }
            $hostHGRelation[$hg["hostgroup_hg_id"]][$hg["host_host_id"]] = $hg["host_host_id"];
        }
        $dbResult->closeCursor();
        unset($hg);

        /** ***********************************************
         * Host Service relation
         */
        $hsRelation = array();
        $dbResult = $pearDB->query(
            "SELECT hostgroup_hg_id, host_host_id, service_service_id
            FROM host_service_relation"
        );
        while ($sr = $dbResult->fetch()) {
            if (isset($sr["host_host_id"]) && $sr["host_host_id"]) {
                if (!isset($hsRelation[$sr["host_host_id"]])) {
                    $hsRelation[$sr["host_host_id"]] = array();
                }
                $hsRelation[$sr["host_host_id"]][$sr["service_service_id"]] = 1;
            } else {
                if (isset($hostHGRelation[$sr["hostgroup_hg_id"]])) {
                    foreach ($hostHGRelation[$sr["hostgroup_hg_id"]] as $host_id) {
                        if (!isset($hsRelation[$host_id])) {
                            $hsRelation[$host_id] = array();
                        }
                        $hsRelation[$host_id][$sr["service_service_id"]] = 1;
                    }
                }
            }
        }
        $dbResult->closeCursor();

        /** ***********************************************
         * Create Service template model Cache
         */
        $svcTplCache = array();
        $dbResult = $pearDB->query("SELECT service_template_model_stm_id, service_id FROM service");
        while ($tpl = $dbResult->fetch()) {
            $svcTplCache[$tpl["service_id"]] = $tpl["service_template_model_stm_id"];
        }
        $dbResult->closeCursor();
        unset($tpl);

        $svcCatCache = array();
        $dbResult = $pearDB->query("SELECT sc_id, service_service_id FROM `service_categories_relation`");
        while ($res = $dbResult->fetch()) {
            if (!isset($svcCatCache[$res["service_service_id"]])) {
                $svcCatCache[$res["service_service_id"]] = array();
            }
            $svcCatCache[$res["service_service_id"]][$res["sc_id"]] = 1;
        }
        $dbResult->closeCursor();
        unset($res);

        $sgCache = array();
        $res = $pearDB->query(
            "SELECT argr.`acl_res_id`, acl_group_id
            FROM `acl_res_group_relations` argr, `acl_resources` ar
            WHERE argr.acl_res_id = ar.acl_res_id
                AND ar.acl_res_activate = '1'"
        );
        while ($row = $res->fetch()) {
            $sgCache[$row['acl_res_id']] = array();
        }
        $res->closeCursor();
        unset($row);

        $res = $pearDB->query(
            "SELECT service_service_id, sgr.host_host_id, acl_res_id
            FROM servicegroup sg, acl_resources_sg_relations acl, servicegroup_relation sgr
            WHERE acl.sg_id = sg.sg_id
                AND sgr.servicegroup_sg_id = sg.sg_id "
        );
        while ($row = $res->fetch()) {
            foreach (array_keys($sgCache) as $rId) {
                if ($rId == $row['acl_res_id']) {
                    if (!isset($sgCache[$rId][$row['host_host_id']])) {
                        $sgCache[$rId][$row['host_host_id']] = array();
                    }
                    $sgCache[$rId][$row['host_host_id']][$svcCache[$row['service_service_id']]] =
                        $row['service_service_id'];
                }
            }
        }
        $res->closeCursor();
        unset($row);

        $res = $pearDB->query(
            "SELECT acl_res_id, hg_id
            FROM hostgroup, acl_resources_hg_relations
            WHERE acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id"
        );
        $hgResCache = array();
        while ($row = $res->fetch()) {
            if (!isset($hgResCache[$row['acl_res_id']])) {
                $hgResCache[$row['acl_res_id']] = array();
            }
            $hgResCache[$row['acl_res_id']][] = $row['hg_id'];
        }
        $res->closeCursor();
        unset($row);

        // Prepare statement
        $deleteHandler = $pearDBO->prepare("DELETE FROM centreon_acl WHERE group_id = ?");

        /** ***********************************************
         * Begin to build ACL
         */
        $cpt = 0;
        foreach ($tabGroups as $acl_group_id) {
            /*
             * Delete old data for this group
             */
            $deleteHandler->execute(array($acl_group_id));

            /** ***********************************************
             * Select
             */
            $dbResult2 = $pearDB->prepare(
                "SELECT DISTINCT(`acl_resources`.`acl_res_id`)
                FROM `acl_res_group_relations`, `acl_resources`
                WHERE `acl_res_group_relations`.`acl_group_id` = :aclGroupId
                    AND `acl_res_group_relations`.acl_res_id = `acl_resources`.acl_res_id
                    AND `acl_resources`.acl_res_activate = '1'"
            );
            $dbResult2->bindValue(':aclGroupId', $acl_group_id, \PDO::PARAM_INT);
            $dbResult2->execute();
            if ($debug) {
                $time_start = microtime_float2();
            }

            while ($res2 = $dbResult2->fetch()) {
                if (!isset($resourceCache[$res2["acl_res_id"]])) {
                    $resourceCache[$res2["acl_res_id"]] = array();

                    $Host = array();
                    /*
                    * Get all Hosts
                    */
                    if (isset($hostIncCache[$res2["acl_res_id"]])) {
                        foreach ($hostIncCache[$res2["acl_res_id"]] as $host_id => $host_name) {
                            $Host[$host_id] = $host_name;
                        }
                    }

                    if (isset($hgResCache[$res2['acl_res_id']])) {
                        foreach ($hgResCache[$res2['acl_res_id']] as $hgId) {
                            if (isset($hostHGRelation[$hgId])) {
                                foreach ($hostHGRelation[$hgId] as $host_id) {
                                    if ($hostCache[$host_id]) {
                                        $Host[$host_id] = $hostCache[$host_id];
                                    } else {
                                        print "Host $host_id unknown !\n";
                                    }
                                }
                            }
                        }
                    }

                    if (isset($hostExclCache[$res2["acl_res_id"]])) {
                        foreach ($hostExclCache[$res2["acl_res_id"]] as $host_id => $host_name) {
                            unset($Host[$host_id]);
                        }
                    }

                    /*
                    * Give Authorized Categories
                    */
                    $authorizedCategories = getAuthorizedCategories($res2["acl_res_id"]);

                    /*
                    * get all Service groups
                    */
                    $dbResult3 = $pearDB->prepare(
                        "SELECT host_name, host_id, service_description, service_id
                        FROM `acl_resources_sg_relations`, `servicegroup_relation`, `host`, `service`
                        WHERE acl_res_id = :aclResId
                            AND host.host_id = servicegroup_relation.host_host_id
                            AND service.service_id = servicegroup_relation.service_service_id
                            AND servicegroup_relation.servicegroup_sg_id = acl_resources_sg_relations.sg_id
                            AND service_activate = '1'
                        UNION
                        SELECT host_name, host_id, service_description, service_id 
                        FROM `acl_resources_sg_relations`, `servicegroup_relation`, `host`, `service`, `hostgroup`,
                         `hostgroup_relation`
                        WHERE acl_res_id = :aclResId
                            AND hostgroup.hg_id = servicegroup_relation.hostgroup_hg_id
                            AND servicegroup_relation.hostgroup_hg_id = hostgroup_relation.hostgroup_hg_id
                            AND hostgroup_relation.host_host_id = host.host_id
                            AND service.service_id = servicegroup_relation.service_service_id
                            AND servicegroup_relation.servicegroup_sg_id = acl_resources_sg_relations.sg_id
                            AND service_activate = '1'"
                    );
                    $dbResult3->bindValue(':aclResId', $res2["acl_res_id"], \PDO::PARAM_INT);
                    $dbResult3->execute();

                    $sgElem = array();
                    $tmpH = array();
                    if ($dbResult3->rowCount()) {
                        while ($h = $dbResult3->fetch()) {
                            if (!isset($sgElem[$h["host_name"]])) {
                                $sgElem[$h["host_name"]] = array();
                                $tmpH[$h['host_id']] = $h['host_name'];
                            }
                            $sgElem[$h["host_name"]][$h["service_description"]] =
                                $h["host_id"] . "," . $h["service_id"];
                        }
                    }
                    $dbResult3->closeCursor();

                    $tmpH = getFilteredHostCategories($tmpH, $res2["acl_res_id"]);
                    $tmpH = getFilteredPollers($tmpH, $res2["acl_res_id"]);

                    foreach ($sgElem as $key => $value) {
                        if (in_array($key, $tmpH)) {
                            if (count($authorizedCategories) == 0) { // no category filter
                                $resourceCache[$res2["acl_res_id"]][$key] = $value;
                            } else {
                                // subkey = <service_description>, subvalue = <host_id>,<service_id>
                                foreach ($value as $subkey => $subvalue) {
                                    if (preg_match('/\d+,(\d+)/', $subvalue, $matches)) { // get service id
                                        $linkedServiceCategories = getServiceTemplateCategoryList($matches[1]);
                                        foreach ($linkedServiceCategories as $linkedServiceCategory) {
                                            // Check if category linked to service is allowed
                                            if (in_array($linkedServiceCategory, $authorizedCategories)) {
                                                $resourceCache[$res2["acl_res_id"]][$key][$subkey] = $subvalue;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    unset($tmpH);
                    unset($sgElem);

                    // Filter
                    $Host = getFilteredHostCategories($Host, $res2["acl_res_id"]);
                    $Host = getFilteredPollers($Host, $res2['acl_res_id']);

                    /*
                    * Initialize and first filter
                    */
                    foreach ($Host as $key => $value) {
                        $tab = getAuthorizedServicesHost($key, $res2["acl_res_id"], $authorizedCategories);
                        if (!isset($resourceCache[$res2["acl_res_id"]][$value])) {
                            $resourceCache[$res2["acl_res_id"]][$value] = array();
                        }
                        foreach ($tab as $desc => $id) {
                            $resourceCache[$res2["acl_res_id"]][$value][$desc] = $key . "," . $id;
                        }
                        unset($tab);
                    }
                    unset($Host);

                    /*
                    * Set meta services
                    */
                    $metaServices = getMetaServices($res2['acl_res_id'], $pearDB, $metaObj);
                    if (count($metaServices)) {
                        $resourceCache[$res2["acl_res_id"]] += $metaServices;
                    }
                }

                $strBegin = "INSERT INTO centreon_acl (host_id, service_id, group_id) VALUES ";
                $strEnd = " ON DUPLICATE KEY UPDATE `group_id` = ? ";

                $str = "";
                $params = [];
                $i = 0;
                foreach ($resourceCache[$res2["acl_res_id"]] as $host => $svc_list) {
                    if ($hostId = array_search($host, $hostCache)) {
                        if ($str != "") {
                            $str .= ", ";
                        }
                        $str .= " (?, NULL, ?) ";
                        $params[] = $hostId;
                        $params[] = $acl_group_id;

                        foreach (array_values($svc_list) as $hostServiceId) {
                            $explodedHostServiceId = explode(",", $hostServiceId);

                            if ($str != "") {
                                $str .= ', ';
                            }

                            if (isset($explodedHostServiceId[1])) {
                                $i++;
                                $str .= " (?, ?, ?) ";
                                $params[] = $hostId;
                                $params[] = $explodedHostServiceId[1];
                                $params[] = $acl_group_id;
                                if ($i >= 1000) {
                                    $params[] = $acl_group_id; // argument for $strEnd
                                    $stmt = $pearDBO->prepare($strBegin . $str . $strEnd);
                                    $stmt->execute($params); // inject acl by bulk (1000 relations)
                                    $params = [];
                                    $str = "";
                                    $i = 0;
                                }
                            }
                        }
                    }
                }

                // inject remaining acl (bulk of less than 1000 relations)
                if ($str != "") {
                    $params[] = $acl_group_id; // argument for $strEnd
                    $stmt = $pearDBO->prepare($strBegin . $str . $strEnd);
                    $stmt->execute($params);
                    $str = "";
                }

                // reset flags of acl_resources
                $stmt = $pearDB->prepare("UPDATE `acl_resources` SET `changed` = '0' WHERE acl_res_id = ?");
                $stmt->execute([$res2["acl_res_id"]]);
            }
            $dbResult2->closeCursor();

            if ($debug) {
                $time_end = microtime_float2();
                $now = $time_end - $time_start;
                print round($now, 3) . " " . _("seconds") . "\n";
            }

            $cpt++;

            // reset flags of acl_groups
            $stmt = $pearDB->prepare("UPDATE acl_groups SET acl_group_changed = '0' WHERE acl_group_id = ?");
            $stmt->execute([$acl_group_id]);
        }

        /**
         * Include module specific ACL evaluation
         */
        $extensionsPaths = getModulesExtensionsPaths($pearDB);
        foreach ($extensionsPaths as $extensionPath) {
            require_once $extensionPath . 'centAcl.php';
        }
    }

    /*
     * Remove lock
     */
    $dbResult = $pearDB->prepare(
        "UPDATE cron_operation " .
        "SET running = '0', last_execution_time = :time
        WHERE id = :appId"
    );
    $dbResult->bindValue(':time', (time() - $beginTime), \PDO::PARAM_INT);
    $dbResult->bindValue(':appId', $appID, \PDO::PARAM_INT);
    $dbResult->execute();

    /*
     * Close connection to databases
     */
    $pearDB = null;
    $pearDBO = null;
} catch (Exception $e) {
    programExit($e->getMessage());
}
