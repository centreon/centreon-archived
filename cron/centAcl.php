<?php
/*
 * Copyright 2005-2011 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 *
 */

/**
 * Define the period between to update in second for ldap user/group
 */
define('LDAP_UPDATE_PERIOD', 3600);

include_once "DB.php";

include_once "@CENTREON_ETC@/centreon.conf.php";
include_once $centreon_path . "/cron/centAcl-Func.php";
include_once $centreon_path . "/www/class/centreonDB.class.php";
include_once $centreon_path . "/www/class/centreonLDAP.class.php";

$centreonDbName = $conf_centreon['db'];

function programExit($msg) {
    echo "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n";
    exit;
}

$nbProc = exec("ps -edf | grep -c \"[c]entAcl.php\"");
if ((int) $nbProc > 2) {
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

    /*
     * Detect Which DB layer is used
     */
    $DBRESULT = $pearDB->query("SELECT * FROM options WHERE `key` LIKE 'broker'");
    if (PEAR::isError($DBRESULT)) {
        print "Cannot Get Monitoring Engine";
        exit(1);
    }
    $row = $DBRESULT->fetchRow();
    $dbLayer = $row["value"];

    if ($dbLayer == 'ndo') {
        $pearDBndo = new CentreonDB("ndo");
    } else {
        $pearDBndo = $pearDBO;
    }

    /*
     * Lock in MySQL
     */
    $DBRESULT = $pearDB->query("SELECT id, running FROM cron_operation WHERE name LIKE 'centAcl.php'");
    if (PEAR::isError($DBRESULT)) {
        print "Error to check is process running.";
        exit(1);
    }
    $data = $DBRESULT->fetchRow();

    $is_running = $data["running"];
    $appID = $data["id"];
    $beginTime = time();

    if (count($data) == 0) {
        $DBRESULT = $pearDB->query("INSERT INTO cron_operation (name, system, activate) VALUES ('centAcl.php', '1', '1')");
        $DBRESULT = $pearDB->query("SELECT id, running FROM cron_operation WHERE name LIKE 'centAcl.php'");
        if (PEAR::isError($DBRESULT)) {
            print "Error to check is process running.";
            exit(1);
        }
        $data = $DBRESULT->fetchRow();
        $appID = $data["id"];
        $is_running = 0;
    }

    if ($is_running == 0) {
        $DBRESULT = $pearDB->query("UPDATE cron_operation SET running = '1', time_launch = '" . time() . "' WHERE id = '$appID'");
    } else {
        if ($nbProc <= 1) {
            $errorMessage = "According to DB another instance of centAcl.php is already running and I found " . $nbProc . " process...\n";
            $errorMessage .= "Executing query: UPDATE cron_operation SET running = 0 WHERE id =  '$appID'";
            $pearDB->query("UPDATE cron_operation SET running = '0' WHERE id = '$appID'");
        } else {
            $errorMessage = "centAcl marked as running. Exiting...";
        }
        programExit($errorMessage);
    }

    /*     * **********************************************
     * Sync ACL with ldap
     */
    $queryOptions = "SELECT `key`, `value` FROM `options` WHERE `key` IN ('ldap_auth_enable', 'ldap_last_acl_update')";
    $res = $pearDB->query($queryOptions);
    while ($row = $res->fetchRow()) {
        switch ($row['key']) {
            case 'ldap_auth_enable':
                $ldap_enable = $row['value'];
                break;
            case 'ldap_last_acl_update':
                $ldap_last_update = $row['value'];
                break;
        }
    }

    /*     * ********************************************
     * If the ldap is enable and the last check
     * is more than update period
     */
    if ($ldap_enable == 1 && $ldap_last_update < (time() - LDAP_UPDATE_PERIOD)) {
        $query = "SELECT ar_id FROM auth_ressource WHERE ar_enable = '1'";
        $ldapres = $pearDB->query($query);

        /*
         * Connect to LDAP Server
         */
        $ldapConn = new CentreonLDAP($pearDB, null);
        while ($ldaprow = $ldapres->fetchRow()) {
            $connectionResult = $ldapConn->connect(null, $ldaprow['ar_id']);
            if (false != $connectionResult) {
                $listGroup = array();
                $res = $pearDB->query("SELECT cg_id, cg_name, cg_ldap_dn FROM contactgroup WHERE cg_type = 'ldap'");
                while ($row = $res->fetchRow()) {
                    /*
                     * Test is the group a not move or delete in ldap
                     */
                    if (false === $ldapConn->getEntry($row['cg_ldap_dn'])) {
                        $dn = $ldapConn->findGroupDn($row['cg_name']);
                        if (false === $dn) {
                            /*
                             * Delete the ldap group in contactgroup
                             */
                            $queryDelete = "DELETE FROM contactgroup WHERE cg_id = " . $row['cg_id'];
                            if (PEAR::isError($pearDB->query($queryDelete))) {
                                print "Error in delete contactgroup for ldap group : " . $row['cg_name'] . "\n";
                            }
                            continue;
                        } else {
                            /*
                             * Update the ldap group in contactgroup
                             */
                            $queryUpdateDn = "UPDATE contactgroup SET cg_ldap_dn = '" . $row['cg_ldap_dn'] . "' WHERE cg_id = " . $row['cg_id'];
                            if (PEAR::isError($pearDB->query($queryUpdateDn))) {
                                print "Error in update contactgroup for ldap group : " . $row['cg_name'] . "\n";
                                continue;
                            } else {
                                $row['cg_ldap_dn'] = $dn;
                            }
                        }
                    }
                    $members = $ldapConn->listUserForGroup($row['cg_ldap_dn']);

                    /*
                     * Refresh Users Groups.
                     */
                    $queryDeleteRelation = "DELETE FROM contactgroup_contact_relation WHERE contactgroup_cg_id = " . $row['cg_id'];
                    $pearDB->query($queryDeleteRelation);
                    $queryContact = "SELECT contact_id FROM contact WHERE contact_ldap_dn IN ('" . join("', '", array_map('mysql_real_escape_string', $members)) . "')";
                    $resContact = $pearDB->query($queryContact);
                    if (PEAR::isError($resContact)) {
                        print "Error in getting contact id form members.\n";
                        continue;
                    }
                    while ($rowContact = $resContact->fetchRow()) {
                        $queryAddRelation = "INSERT INTO contactgroup_contact_relation
            	            						(contactgroup_cg_id, contact_contact_id)
            	            					VALUES (" . $row['cg_id'] . ", " . $rowContact['contact_id'] . ")";
                        if (PEAR::isError($pearDB->query($queryAddRelation))) {
                            print "Error insert relation between contactgroup " . $row['cg_id'] . " and contact " . $rowContact['contact_id'] . "\n";
                        }
                    }
                }
                $queryUpdateTime = "UPDATE `options` SET `value` = '" . time() . "' WHERE `key` = 'ldap_last_acl_update'";
                $pearDB->query($queryUpdateTime);
            } else {
                echo "[" . date("Y-m-d H:i:s") . "] Unable to connect to LDAP server. I keep building ACL ...\n";
            }
        }
    }

    /*     * **********************************************
     * Remove data from old groups (deleted groups)
     */
    $aclGroupToDelete = "SELECT DISTINCT acl_group_id FROM $centreonDbName.acl_groups WHERE acl_group_activate = '1'";
    $aclGroupToDelete2 = "SELECT DISTINCT acl_group_id FROM $centreonDbName.acl_res_group_relations";
    if ($dbLayer != 'broker') {
        $pearDBndo->query("DELETE FROM centreon_acl WHERE group_id NOT IN ($aclGroupToDelete)");
        $pearDBndo->query("DELETE FROM centreon_acl WHERE group_id NOT IN ($aclGroupToDelete2)");
    } else {
        $pearDBO->query("DELETE FROM centreon_acl WHERE group_id NOT IN ($aclGroupToDelete)");
        $pearDBO->query("DELETE FROM centreon_acl WHERE group_id NOT IN ($aclGroupToDelete2)");
    }

    /*     * ***********************************************
     * Check if some ACL have global options for
     * all resources are selected
     */
    $query = "SELECT acl_res_id, all_hosts, all_hostgroups, all_servicegroups " .
            "FROM acl_resources WHERE acl_res_activate = '1' " .
            "AND (all_hosts IS NOT NULL OR all_hostgroups IS NOT NULL OR all_servicegroups IS NOT NULL)";
    $res = $pearDB->query($query);
    while ($row = $res->fetchRow()) {
        /**
         * Specific counter
         */
        $i = 0;

        /**
         * Add Hosts
         */
        if ($row['all_hosts']) {
            $query = "SELECT host_id FROM host WHERE host_id NOT IN (SELECT DISTINCT host_host_id FROM acl_resources_host_relations WHERE acl_res_id = '" . $row['acl_res_id'] . "') AND host_register = '1'";
            $res1 = $pearDB->query($query);
            for (; $rowData = $res1->fetchRow(); $i++) {
                $insert_query = "INSERT INTO acl_resources_host_relations (host_host_id, acl_res_id) VALUES ('" . $rowData['host_id'] . "', '" . $row['acl_res_id'] . "')";
                $pearDB->query($insert_query);
            }
            $res1->free();
        }

        /**
         * Add Hostgroups
         */
        if ($row['all_hostgroups']) {
            $query = "SELECT hg_id FROM hostgroup WHERE hg_id NOT IN (SELECT DISTINCT hg_hg_id FROM acl_resources_hg_relations WHERE acl_res_id = '" . $row['acl_res_id'] . "')";
            $res1 = $pearDB->query($query);
            for (; $rowData = $res1->fetchRow(); $i++) {
                $insert_query = "INSERT INTO acl_resources_hg_relations (hg_hg_id, acl_res_id) VALUES ('" . $rowData['hg_id'] . "', '" . $row['acl_res_id'] . "')";
                $pearDB->query($insert_query);
            }
            $res1->free();
        }

        /**
         * Add Servicesgroups
         */
        if ($row['all_servicegroups']) {
            $query = "SELECT sg_id FROM servicegroup WHERE sg_id NOT IN (SELECT DISTINCT sg_id FROM acl_resources_sg_relations WHERE acl_res_id = '" . $row['acl_res_id'] . "')";
            $res1 = $pearDB->query($query);
            for (; $rowData = $res1->fetchRow(); $i++) {
                $insert_query = "INSERT INTO acl_resources_sg_relations (sg_id, acl_res_id) VALUES ('" . $rowData['sg_id'] . "', '" . $row['acl_res_id'] . "')";
                $pearDB->query($insert_query);
            }
            $res1->free();
        }

        if ($i != 0) {
            $pearDB->query("UPDATE acl_resources SET changed = '1' WHERE acl_res_id = '" . $row['acl_res_id'] . "'");
        }
    }
    $res->free();

    /*     * ***********************************************
     *  Caching of all Data
     *
     */
    $hostTemplateCache = array();
    $query = "SELECT host_host_id, host_tpl_id FROM host_template_relation";
    $res = $pearDB->query($query);
    while ($row = $res->fetchRow()) {
        if (!isset($hostTemplateCache[$row['host_tpl_id']])) {
            $hostTemplateCache[$row['host_tpl_id']] = array();
        }
        $hostTemplateCache[$row['host_tpl_id']][$row['host_host_id']] = $row['host_host_id'];
    }
    $res->free();

    $hostCache = array();
    $DBRESULT = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1'");
    while ($h = $DBRESULT->fetchRow()) {
        $hostCache[$h["host_id"]] = $h["host_name"];
    }
    $DBRESULT->free();
    unset($h);

    /*     * ***********************************************
     * Cache for host poller relation
     */
    $hostPollerCache = array();
    $query = "SELECT nagios_server_id, host_host_id FROM ns_host_relation";
    $res = $pearDB->query($query);
    while ($row = $res->fetchRow()) {
        if (!isset($hostPollerCache[$row['nagios_server_id']])) {
            $hostPollerCache[$row['nagios_server_id']] = array();
        }
        $hostPollerCache[$row['nagios_server_id']][$row['host_host_id']] = $row['host_host_id'];
    }


    /*     * ***********************************************
     * Get all included Hosts
     */
    $hostIncCache = array();
    $DBRESULT = $pearDB->query("SELECT host_id, host_name, acl_res_id FROM `host`, `acl_resources_host_relations` WHERE acl_resources_host_relations.host_host_id = host.host_id AND host.host_register = '1' AND host.host_activate = '1'");
    while ($h = $DBRESULT->fetchRow()) {
        if (!isset($hostIncCache[$h["acl_res_id"]])) {
            $hostIncCache[$h["acl_res_id"]] = array();
        }
        $hostIncCache[$h["acl_res_id"]][$h["host_id"]] = $h["host_name"];
    }
    $DBRESULT->free();

    /*     * ***********************************************
     * Get all excluded Hosts
     */
    $hostExclCache = array();
    $DBRESULT = $pearDB->query("SELECT host_id, host_name, acl_res_id FROM `host`, `acl_resources_hostex_relations` WHERE acl_resources_hostex_relations.host_host_id = host.host_id AND host.host_register = '1' AND host.host_activate = '1'");
    while ($h = $DBRESULT->fetchRow()) {
        if (!isset($hostExclCache[$h["acl_res_id"]])) {
            $hostExclCache[$h["acl_res_id"]] = array();
        }
        $hostExclCache[$h["acl_res_id"]][$h["host_id"]] = $h["host_name"];
    }
    $DBRESULT->free();

    /*     * ***********************************************
     * Service Cache
     */
    $svcCache = array();
    $DBRESULT = $pearDB->query("SELECT service_id, service_description FROM `service` WHERE service_register = '1'");
    while ($s = $DBRESULT->fetchRow()) {
        $svcCache[$s["service_id"]] = $s["service_description"];
    }
    $DBRESULT->free();

    /*     * ***********************************************
     * Host Host relation
     */
    $hostHGRelation = array();
    $DBRESULT = $pearDB->query("SELECT * FROM hostgroup_relation");
    while ($hg = $DBRESULT->fetchRow()) {
        if (!isset($hostHGRelation[$hg["hostgroup_hg_id"]])) {
            $hostHGRelation[$hg["hostgroup_hg_id"]] = array();
        }
        $hostHGRelation[$hg["hostgroup_hg_id"]][$hg["host_host_id"]] = $hg["host_host_id"];
    }
    $DBRESULT->free();
    unset($hg);


    /*     * ***********************************************
     * Host Service relation
     */
    $hsRelation = array();
    $hgsRelation = array();
    $DBRESULT = $pearDB->query("SELECT hostgroup_hg_id, host_host_id, service_service_id FROM host_service_relation");
    while ($sr = $DBRESULT->fetchRow()) {
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
    $DBRESULT->free();

    /*     * ***********************************************
     * Create Servive template modele Cache
     */
    $svcTplCache = array();
    $DBRESULT = $pearDB->query("SELECT service_template_model_stm_id, service_id FROM service");
    while ($tpl = $DBRESULT->fetchRow()) {
        $svcTplCache[$tpl["service_id"]] = $tpl["service_template_model_stm_id"];
    }
    $DBRESULT->free();
    unset($tpl);

    $svcCatCache = array();
    $DBRESULT = $pearDB->query("SELECT sc_id, service_service_id FROM `service_categories_relation`");
    while ($res = $DBRESULT->fetchRow()) {
        if (!isset($svcCatCache[$res["service_service_id"]])) {
            $svcCatCache[$res["service_service_id"]] = array();
        }
        $svcCatCache[$res["service_service_id"]][$res["sc_id"]] = 1;
    }
    $DBRESULT->free();
    unset($res);

    $sgCache = array();
    $query = "SELECT argr.`acl_res_id`, acl_group_id " .
            "FROM `acl_res_group_relations` argr, `acl_resources` ar  " .
            "WHERE argr.acl_res_id = ar.acl_res_id " .
            "AND ar.acl_res_activate = '1'";
    $res = $pearDB->query($query);
    while ($row = $res->fetchRow()) {
        $sgCache[$row['acl_group_id']][$row['acl_res_id']] = array();
    }


    $query = "SELECT service_service_id, sgr.host_host_id, acl_res_id " .
            "FROM servicegroup sg, acl_resources_sg_relations acl, servicegroup_relation sgr " .
            "WHERE acl.sg_id = sg.sg_id " .
            "AND sgr.servicegroup_sg_id = sg.sg_id ";
    $res = $pearDB->query($query);
    while ($row = $res->fetchRow()) {
        foreach ($sgCache as $acl_g_id => $acl_g) {
            foreach ($acl_g as $rId => $value) {
                if ($rId == $row['acl_res_id']) {
                    if (!isset($sgCache[$acl_g_id][$rId][$row['host_host_id']])) {
                        $sgCache[$acl_g_id][$rId][$row['host_host_id']] = array();
                    }
                    $sgCache[$acl_g_id][$rId][$row['host_host_id']][$svcCache[$row['service_service_id']]] = $row['service_service_id'];
                }
            }
        }
    }

    /*     * ***********************************************
     * Begin to build ACL
     */
    $tabGroups = array();
    $query = "SELECT DISTINCT acl_groups.acl_group_id, acl_resources.acl_res_id " .
            "FROM acl_res_group_relations, `acl_groups`, `acl_resources` " .
            "WHERE acl_groups.acl_group_id = acl_res_group_relations.acl_group_id " .
            "AND acl_res_group_relations.acl_res_id = acl_resources.acl_res_id " .
            "AND acl_groups.acl_group_activate = '1' " .
            "AND (acl_groups.acl_group_changed = '1' " .
            "OR acl_resources.changed = '1')";
    $DBRESULT1 = $pearDB->query($query);
    while ($result = $DBRESULT1->fetchRow()) {
        $tabGroups[$result["acl_group_id"]] = 1;
    }
    $DBRESULT1->free();
    unset($result);

    $query = "SELECT acl_res_id, hg_id FROM hostgroup, acl_resources_hg_relations
    			  WHERE acl_resources_hg_relations.hg_hg_id = hostgroup.hg_id";
    $res = $pearDB->query($query);
    $hgResCache = array();
    while ($row = $res->fetchRow()) {
        if (!isset($hgResCache[$row['acl_res_id']])) {
            $hgResCache[$row['acl_res_id']] = array();
        }
        $hgResCache[$row['acl_res_id']][] = $row['hg_id'];
    }

    $strBegin = "INSERT INTO `centreon_acl` ( `host_name` , `service_description` , `host_id` , `service_id`,`group_id` ) VALUES ";
    $cpt = 0;
    foreach ($tabGroups as $acl_group_id => $acl_res_id) {
        $tabElem = array();

        /*         * ***********************************************
         * Select
         */
        $DBRESULT2 = $pearDB->query("SELECT `acl_resources`.`acl_res_id` FROM `acl_res_group_relations`, `acl_resources` " .
                "WHERE `acl_res_group_relations`.`acl_group_id` = '" . $acl_group_id . "' " .
                "AND `acl_res_group_relations`.acl_res_id = `acl_resources`.acl_res_id " .
                "AND `acl_resources`.acl_res_activate = '1'");
        if ($debug) {
            $time_start = microtime_float2();
        }
        while ($res2 = $DBRESULT2->fetchRow()) {
            $Host = array();
            /* ------------------------------------------------------------------ */

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
            $authorizedCategories = getAuthorizedCategories($acl_group_id, $res2["acl_res_id"]);
            $Host = getFilteredHostCategories($Host, $acl_group_id, $res2["acl_res_id"]);
            $Host = getFilteredPollers($Host, $acl_group_id, $res2['acl_res_id']);

            /*
             * Initialize and first filter
             */
            foreach ($Host as $key => $value) {
                $tab = getAuthorizedServicesHost($key, $acl_group_id, $res2["acl_res_id"], $authorizedCategories);
                if (!isset($tabElem[$value])) {
                    $tabElem[$value] = array();
                }
                foreach ($tab as $desc => $id) {
                    $tabElem[$value][$desc] = $key . "," . $id;
                }
                unset($tab);
            }

            /*
             * get all Service groups
             */
            $sgReq = "SELECT tb.* FROM
    					 	(SELECT host_name, host_id, service_description, service_id FROM `acl_resources_sg_relations`, `servicegroup_relation`, `host`, `service`
						 	 WHERE acl_res_id = '" . $res2["acl_res_id"] . "'
						 	 AND host.host_id = servicegroup_relation.host_host_id
						 	 AND service.service_id = servicegroup_relation.service_service_id
						 	 AND servicegroup_relation.servicegroup_sg_id = acl_resources_sg_relations.sg_id
						 	 AND service_activate = '1'
						  UNION
						  	SELECT host_name, host_id, service_description, service_id FROM `acl_resources_sg_relations`, `servicegroup_relation`, `host`, `service`, `hostgroup`, `hostgroup_relation`
						 	 WHERE acl_res_id = '" . $res2["acl_res_id"] . "'
						 	 AND hostgroup.hg_id = servicegroup_relation.hostgroup_hg_id
						 	 AND servicegroup_relation.hostgroup_hg_id = hostgroup_relation.hostgroup_hg_id
						 	 AND hostgroup_relation.host_host_id = host.host_id
						 	 AND service.service_id = servicegroup_relation.service_service_id
						 	 AND servicegroup_relation.servicegroup_sg_id = acl_resources_sg_relations.sg_id
						 	 AND service_activate = '1') tb";
            $DBRESULT3 = & $pearDB->query($sgReq);
            if ($DBRESULT3->numRows()) {
                while ($h = $DBRESULT3->fetchRow()) {
                    if (!isset($tabElem[$h["host_name"]])) {
                        $tabElem[$h["host_name"]] = array();
                    }
                    $tabElem[$h["host_name"]][$h["service_description"]] = $h["host_id"] . "," . $h["service_id"];
                }
            }
            $DBRESULT3->free();

            unset($Host);

            /* ------------------------------------------------------------------
             * reset Flags
             */
            $pearDB->query("UPDATE `acl_resources` SET `changed` = '0' WHERE acl_res_id = '" . $res2["acl_res_id"] . "'");
        }
        $DBRESULT2->free();

        if ($debug) {
            $time_end = microtime_float2();
            $now = $time_end - $time_start;
            print round($now, 3) . " " . _("seconds") . "\n";
        }

        /*
         * Delete old data for this group
         */
        $DBRESULT = $pearDBndo->query("DELETE FROM `centreon_acl` WHERE `group_id` = '" . $acl_group_id . "'");

        $str = "";
        if (count($tabElem)) {
            $i = 0;
            foreach ($tabElem as $host => $svc_list) {
                $hasService = false;
                foreach ($svc_list as $desc => $t) {
                    $hasService = true;
                    if ($str != "") {
                        $str .= ', ';
                    }
                    $id_tmp = preg_split("/\,/", $t);
                    $str .= "('" . $host . "', '" . addslashes($desc) . "', '" . $id_tmp[0] . "' , '" . $id_tmp[1] . "' , " . $acl_group_id . ") ";
                    $i++;
                    if ($i >= 1000) {
                        $DBRESULTNDO = $pearDBndo->query($strBegin . $str);
                        $str = "";
                        $i = 0;
                    }
                }
                if ($hasService == false) {
                    $singleId = array_search($host, $hostCache);
                    if ($singleId) {
                        $pearDBndo->query("INSERT INTO centreon_acl (host_id, host_name, group_id) VALUES ($singleId, '" . $pearDBndo->escape($host) . "', $acl_group_id)");
                    }
                }
            }

            /*
             * Insert datas
             */
            if ($str != "") {
                $DBRESULTNDO = $pearDBndo->query($strBegin . $str);
                $str = "";
            }
        }
        $cpt++;
        $pearDB->query("UPDATE acl_groups SET acl_group_changed = '0' WHERE acl_group_id = " . $pearDB->escape($acl_group_id));
    }

    /*
     * Remove lock
     */
    $DBRESULT = $pearDB->query("UPDATE cron_operation SET running = '0', last_execution_time = '" . (time() - $beginTime) . "' WHERE id = '$appID'");

    /*
     * Close connection to databases
     */
    $pearDB->disconnect();
    $pearDBO->disconnect();
    if ($dbLayer == 'ndo') {
        $pearDBndo->disconnect();
    }
} catch (Exception $e) {
    programExit($e->getMessage());
}
?>