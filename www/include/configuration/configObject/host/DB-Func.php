<?php

/*
 * Copyright 2005-2020 Centreon
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

if (!isset($centreon)) {
    exit();
}

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonACL.class.php';

/**
 * Quickform rule that checks whether or not monitoring server can be set
 *
 * @global CentreonDB $pearDB
 * @global HTML_QuickFormCustom $form
 * @param int $instanceId
 * @return boolean
 */
function testPollerDep($instanceId)
{
    global $pearDB, $form;

    $hostId = $form->getSubmitValue('host_id');
    $hostParents = filter_var_array(
        $form->getSubmitValue('host_parents'),
        FILTER_VALIDATE_INT
    );

    if (!$hostId || is_null($hostParents)) {
        return true;
    }

    $request = "SELECT COUNT(*) as total "
        . "FROM host_hostparent_relation hhr, ns_host_relation nhr "
        . "WHERE hhr.host_parent_hp_id = nhr.host_host_id "
        . "AND hhr.host_host_id = :host_id "
        . "AND nhr.nagios_server_id != :server_id";

    $fieldsToBind = [];
    if (!in_array(false, $hostParents)) {
        for ($index = 0; $index < count($hostParents); $index++) {
            $fieldsToBind[':parent_' . $index] = $hostParents[$index];
        }
        $request .= " AND host_parent_hp_id IN (" .
            implode(',', array_keys($fieldsToBind)) . ")";
    }

    $prepare = $pearDB->prepare($request);
    $prepare->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
    $prepare->bindValue(':server_id', $instanceId, \PDO::PARAM_INT);

    foreach ($fieldsToBind as $field => $hostParentId) {
        $prepare->bindValue($field, $hostParentId, \PDO::PARAM_INT);
    }

    if ($prepare->execute()) {
        $result = $prepare->fetch(\PDO::FETCH_ASSOC);
        return ((int) $result['total']) == 0;
    }

    return true;
}

/**
 * Quickform rule that checks whether or not reserved macro are used
 *
 * @global CentreonDB $pearDB
 * @return boolean
 */
function hostMacHandler()
{
    global $pearDB;

    if (!isset($_REQUEST['macroInput'])) {
        return true;
    }

    $fieldsToBind = [];
    for ($index = 0; $index < count($_POST['macroInput']); $index++) {
        $fieldsToBind[':macro_' . $index] =
            "'\$_HOST" . strtoupper($_POST['macroInput'][$index]) . "\$'";
    }

    $request =
        "SELECT count(*) as total FROM nagios_macro WHERE macro_name IN (" .
        implode(',', array_keys($fieldsToBind)) . ")";

    $prepare = $pearDB->prepare($request);
    foreach ($fieldsToBind as $field => $macroName) {
        $prepare->bindValue($field, $macroName, \PDO::PARAM_STR);
    }

    if ($prepare->execute()) {
        $result = $prepare->fetch(\PDO::FETCH_ASSOC);
        return ((int) $result['total']) == 0;
    }
    return true;
}

/**
 * Indicates if the host name has already been used
 *
 * @global CentreonDB $pearDB
 * @global HTML_QuickFormCustom $form
 * @global Centreon $centreon
 * @param string $name Name to check
 * @return boolean Return false if the host name has already been used
 */
function hasHostNameNeverUsed($name = null)
{
    global $pearDB, $form, $centreon;

    $id = null;
    if (isset($form)) {
        $id = (int) $form->getSubmitValue('host_id');
    }

    $prepare = $pearDB->prepare(
        "SELECT host_name, host_id FROM host "
        . "WHERE host_name = :host_name AND host_register = '1'"
    );
    $hostName = CentreonDB::escape($centreon->checkIllegalChar($name));

    $prepare->bindValue(':host_name', $hostName, \PDO::PARAM_STR);
    $prepare->execute();
    $result = $prepare->fetch(\PDO::FETCH_ASSOC);
    $totals = $prepare->rowCount();

    if ($totals >= 1 && ($result["host_id"] == $id)) {
        /**
         * In case of modification
         */
        return true;
    } elseif ($totals >= 1 && ($result["host_id"] != $id)) {
        return false;
    } else {
        return true;
    }
}

function testHostName($name = null)
{
    if (preg_match("/^_Module_/", $name)) {
        return false;
    }
    return true;
}

/**
 * Indicates if the host template has already been used
 *
 * @global CentreonDB $pearDB
 * @global HTML_QuickFormCustom $form
 * @param string $name Name to check
 * @return boolean Return false if the host template has already been used
 */
function hasHostTemplateNeverUsed($name = null)
{
    global $pearDB, $form;

    $id = null;
    if (isset($form)) {
        $id = (int) $form->getSubmitValue('host_id');
    }

    $prepare = $pearDB->prepare(
        "SELECT host_name, host_id FROM host "
        . "WHERE host_name = :host_name AND host_register = '0'"
    );
    $prepare->bindValue(':host_name', $name, \PDO::PARAM_STR);
    $prepare->execute();
    $total = $prepare->rowCount();
    $result = $prepare->fetch(\PDO::FETCH_ASSOC);

    if ($total >= 1 && $result["host_id"] == $id) {
        /**
         * In case of modification
         */
        return true;
    } elseif ($total >= 1 && $result["host_id"] != $id) {
        /**
         * In case of duplicate
         */
        return false;
    } else {
        return true;
    }
}

/**
 * Checks if the insertion can be made
 *
 * @return bool
 */
function hasNoInfiniteLoop($hostId, $templateId)
{
    global $pearDB;
    static $antiTplLoop = array();

    if ($hostId === $templateId) {
        return false;
    }

    if (!count($antiTplLoop)) {
        $query = "SELECT * FROM host_template_relation";
        $res = $pearDB->query($query);
        while ($row = $res->fetch()) {
            if (!isset($antiTplLoop[$row['host_tpl_id']])) {
                $antiTplLoop[$row['host_tpl_id']] = array();
            }
            $antiTplLoop[$row['host_tpl_id']][$row['host_host_id']] = $row['host_host_id'];
        }
    }

    if (isset($antiTplLoop[$hostId])) {
        foreach ($antiTplLoop[$hostId] as $hId) {
            if ($hId == $templateId) {
                return false;
            }
            if (false === hasNoInfiniteLoop($hId, $templateId)) {
                return false;
            }
        }
    }
    return true;
}

function enableHostInDB($host_id = null, $host_arr = array())
{
    global $pearDB, $centreon;

    if (!$host_id && !count($host_arr)) {
        return;
    }

    if ($host_id) {
        $host_arr = array($host_id => "1");
    }
    foreach ($host_arr as $key => $value) {
        $dbResult = $pearDB->query("UPDATE host SET host_activate = '1' WHERE host_id = '" . (int)$key . "'");
        $dbResult2 = $pearDB->query("SELECT host_name FROM `host` WHERE host_id = '" . (int)$key . "' LIMIT 1");
        $row = $dbResult2->fetch();
        $centreon->CentreonLogAction->insertLog("host", $key, $row['host_name'], "enable");
    }
}

function disableHostInDB($host_id = null, $host_arr = array())
{
    global $pearDB, $centreon;
    if (!$host_id && !count($host_arr)) {
        return;
    }

    if ($host_id) {
        $host_arr = array($host_id => "1");
    }
    foreach ($host_arr as $key => $value) {
        $dbResult = $pearDB->query("UPDATE host SET host_activate = '0' WHERE host_id = '" . (int)$key . "'");
        $dbResult2 = $pearDB->query("SELECT host_name FROM `host` WHERE host_id = '" . (int)$key . "' LIMIT 1");
        $row = $dbResult2->fetch();
        $centreon->CentreonLogAction->insertLog("host", $key, $row['host_name'], "disable");
    }
}

/**
 * @param int $hostId
 */
function removeRelationLastHostDependency(int $hostId): void
{
    global $pearDB;

    $query = 'SELECT service_service_id FROM host_service_relation WHERE host_host_id =  ' . $hostId;
    $res = $pearDB->query($query);

    while ($row = $res->fetch()) {
        $query = 'SELECT count(dependency_dep_id) AS nb_dependency , dependency_dep_id AS id 
              FROM dependency_serviceParent_relation 
              WHERE dependency_dep_id = (SELECT dependency_dep_id FROM dependency_serviceParent_relation 
                                         WHERE service_service_id =  ' . $row['service_service_id'] . ')';
        $dbResult = $pearDB->query($query);
        $result = $dbResult->fetch();

        //is last service parent
        if ($result['nb_dependency'] == 1) {
            $pearDB->query("DELETE FROM dependency WHERE dep_id = " . $result['id']);
        }
    }

    $query = 'SELECT count(dependency_dep_id) AS nb_dependency , dependency_dep_id AS id 
              FROM dependency_hostParent_relation 
              WHERE dependency_dep_id = (SELECT dependency_dep_id FROM dependency_hostParent_relation 
                                         WHERE host_host_id =  ' . $hostId . ')';
    $dbResult = $pearDB->query($query);
    $result = $dbResult->fetch();

    //is last parent
    if ($result['nb_dependency'] == 1) {
        $pearDB->query("DELETE FROM dependency WHERE dep_id = " . $result['id']);
    }
}

function deleteHostInDB($hosts = array())
{
    global $pearDB, $centreon;

    foreach ($hosts as $key => $value) {
        removeRelationLastHostDependency((int)$key);
        $rq = "SELECT @nbr := (SELECT COUNT( * ) 
                              FROM host_service_relation 
                              WHERE service_service_id = hsr.service_service_id 
                              GROUP BY service_service_id) 
                              AS nbr, hsr.service_service_id 
                              FROM host_service_relation hsr, host 
                              WHERE hsr.host_host_id = '" . (int)$key . "' 
                              AND host.host_id = hsr.host_host_id 
                              AND host.host_register = '1'";
        $dbResult = $pearDB->query($rq);

        $dbResult3 = $pearDB->query("SELECT host_name FROM `host` WHERE `host_id` = '" . (int)$key . "' LIMIT 1");
        $hostname = $dbResult3->fetch();

        while ($row = $dbResult->fetch()) {
            if ($row["nbr"] == 1) {
                $dbResult4 = $pearDB->query("SELECT service_description 
                                            FROM `service`
                                            WHERE `service_id` = '" . $row["service_service_id"] . "' LIMIT 1");
                $svcname = $dbResult4->fetch();

                $dbResult2 = $pearDB->query("DELETE FROM service 
                                              WHERE service_id = '" . $row["service_service_id"] . "'");
                $centreon->CentreonLogAction->insertLog(
                    "service",
                    $row["service_service_id"],
                    $hostname['host_name'] . "/" . $svcname["service_description"],
                    "d"
                );
            }
        }
        $centreon->user->access->updateACL(array("type" => 'HOST', 'id' => $key, "action" => "DELETE"));
        $dbResult = $pearDB->query("DELETE FROM host WHERE host_id = '" . (int)$key . "'");
        $dbResult = $pearDB->query("DELETE FROM host_template_relation WHERE host_host_id = '" . (int)$key . "'");
        $dbResult = $pearDB->query("DELETE FROM on_demand_macro_host WHERE host_host_id = '" . (int)$key . "'");
        $dbResult = $pearDB->query("DELETE FROM contact_host_relation WHERE host_host_id = '" . (int)$key . "'");
        $centreon->CentreonLogAction->insertLog("host", $key, $hostname['host_name'], "d");
    }
}

/*
 *  This function is called for duplicating a host
 */

function multipleHostInDB($hosts = array(), $nbrDup = array())
{
    global $pearDB, $path, $centreon, $is_admin;

    $hostAcl = array();
    foreach ($hosts as $key => $value) {
        $dbResult = $pearDB->query("SELECT * FROM host WHERE host_id = '" . (int)$key . "' LIMIT 1");
        $row = $dbResult->fetch();
        $row["host_id"] = null;
        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "host_name" ? ($hostName = $value2 = $value2 . "_" . $i) : null;
                $val
                    ? $val .= ($value2 != null ? (", '" . CentreonDB::escape($value2) . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . CentreonDB::escape($value2) . "'") : "NULL");
                if ($key2 != "host_id") {
                    $fields[$key2] = $value2;
                }
                if (isset($hostName)) {
                    $fields["host_name"] = $hostName;
                }
            }
            if (hasHostNameNeverUsed($hostName)) {
                $val ? $rq = "INSERT INTO host VALUES (" . $val . ")" : $rq = null;
                $dbResult = $pearDB->query($rq);
                $dbResult = $pearDB->query("SELECT MAX(host_id) FROM host");
                $maxId = $dbResult->fetch();
                if (isset($maxId["MAX(host_id)"])) {
                    $hostAcl[$maxId['MAX(host_id)']] = $key;

                    $dbResult = $pearDB->query("SELECT DISTINCT host_parent_hp_id 
                                                FROM host_hostparent_relation
                                                WHERE host_host_id = '" . (int)$key . "'");
                    $fields["host_parents"] = "";
                    while ($host = $dbResult->fetch()) {
                        $dbResult1 = $pearDB->query("INSERT INTO host_hostparent_relation 
                              VALUES ('" . $host["host_parent_hp_id"] . "', '" . $maxId["MAX(host_id)"] . "')");
                        $fields["host_parents"] .= $host["host_parent_hp_id"] . ",";
                    }
                    $fields["host_parents"] = trim($fields["host_parents"], ",");

                    $res = $pearDB->query("SELECT DISTINCT host_host_id 
                                          FROM host_hostparent_relation 
                                          WHERE host_parent_hp_id = '" . (int)$key . "'");
                    $fields["host_childs"] = "";
                    while ($host = $res->fetch()) {
                        $res1 = $pearDB->query("INSERT INTO host_hostparent_relation (host_parent_hp_id, host_host_id) 
                                        VALUES ('" . $maxId["MAX(host_id)"] . "', '" . $host['host_host_id'] . "')");
                        $fields["host_childs"] .= $host['host_host_id'] . ",";
                    }
                    $fields['host_childs'] = trim($fields['host_childs'], ",");

                    // We need to duplicate the entire Service and not only create a new relation for it in the DB
                    // /Need Service functions
                    if (file_exists($path . "../service/DB-Func.php")) {
                        require_once($path . "../service/DB-Func.php");
                    } elseif (file_exists($path . "../service/DB-Func.php")) {
                        require_once($path . "../configObject/service/DB-Func.php");
                    }
                    $hostInf = $maxId["MAX(host_id)"];
                    $serviceArr = array();
                    $serviceNbr = array();
                    // Get all Services link to the Host
                    $dbResult = $pearDB->query("SELECT DISTINCT service_service_id 
                                              FROM host_service_relation 
                                              WHERE host_host_id = '" . (int)$key . "'");
                    while ($service = $dbResult->fetch()) {
                        // If the Service is link with several Host, we keep this property and don't duplicate it,
                        // just create a new relation with the new Host
                        $dbResult2 = $pearDB->query("SELECT COUNT(*) 
                                                FROM host_service_relation 
                                                WHERE service_service_id = '" . $service["service_service_id"] . "'");
                        $mulHostSv = $dbResult2->fetch();
                        if ($mulHostSv["COUNT(*)"] > 1) {
                            $dbResult3 = $pearDB->query("INSERT INTO host_service_relation 
                VALUES (NULL, NULL, '" . $maxId["MAX(host_id)"] . "', NULL, '" . $service["service_service_id"] . "')");
                        } else {
                            $serviceArr[$service["service_service_id"]] = $service["service_service_id"];
                            $serviceNbr[$service["service_service_id"]] = 1;
                        }
                    }
                    // Register Host -> Duplicate the Service list
                    if ($row["host_register"] == 1) {
                        multipleServiceInDB($serviceArr, $serviceNbr, $hostInf, 0);
                    } else {
                        // Host Template -> Link to the existing Service Template List
                        $dbResult = $pearDB->query("SELECT DISTINCT service_service_id 
                                                    FROM host_service_relation
                                                    WHERE host_host_id = '" . (int)$key . "'");
                        while ($svs = $dbResult->fetch()) {
                            $dbResult1 = $pearDB->query("INSERT INTO host_service_relation 
                    VALUES (NULL, NULL, '" . $maxId["MAX(host_id)"] . "', NULL, '" . $svs["service_service_id"] . "')");
                        }
                    }

                    /*
                     * ContactGroup duplication
                     */
                    $dbResult = $pearDB->query("SELECT DISTINCT contactgroup_cg_id 
                                                FROM contactgroup_host_relation 
                                                WHERE host_host_id = '" . (int)$key . "'");
                    $fields["host_cgs"] = "";
                    while ($Cg = $dbResult->fetch()) {
                        $dbResult1 = $pearDB->query("INSERT INTO contactgroup_host_relation 
                                VALUES ('" . $maxId["MAX(host_id)"] . "', '" . $Cg["contactgroup_cg_id"] . "')");
                        $fields["host_cgs"] .= $Cg["contactgroup_cg_id"] . ",";
                    }
                    $fields["host_cgs"] = trim($fields["host_cgs"], ",");

                    /*
                     * Contact duplication
                     */
                    $dbResult = $pearDB->query("SELECT DISTINCT contact_id 
                                                FROM contact_host_relation 
                                                WHERE host_host_id = '" . (int)$key . "'");
                    $fields["host_cs"] = "";
                    while ($C = $dbResult->fetch()) {
                        $dbResult1 = $pearDB->query("INSERT INTO contact_host_relation 
                                        VALUES ('" . $maxId["MAX(host_id)"] . "', '" . $C["contact_id"] . "')");
                        $fields["host_cs"] .= $C["contact_id"] . ",";
                    }
                    $fields["host_cs"] = trim($fields["host_cs"], ",");

                    /*
                     * Hostgroup duplication
                     */
                    $dbResult = $pearDB->query("SELECT DISTINCT hostgroup_hg_id 
                                                FROM hostgroup_relation 
                                                WHERE host_host_id = '" . (int)$key . "'");
                    while ($Hg = $dbResult->fetch()) {
                        $dbResult1 = $pearDB->query("INSERT INTO hostgroup_relation 
                                    VALUES (NULL, '" . $Hg["hostgroup_hg_id"] . "', '" . $maxId["MAX(host_id)"] . "')");
                    }

                    /*
                     * Host Extended Informations
                     */
                    $dbResult = $pearDB->query("SELECT * 
                                                FROM extended_host_information 
                                                WHERE host_host_id = '" . (int)$key . "'");
                    while ($ehi = $dbResult->fetch()) {
                        $val = null;
                        $ehi["host_host_id"] = $maxId["MAX(host_id)"];
                        $ehi["ehi_id"] = null;
                        foreach ($ehi as $key2 => $value2) {
                            $val
                                ? $val .= ($value2 != null ? (", '" . CentreonDB::escape($value2) . "'") : ", NULL")
                                : $val .= ($value2 != null ? ("'" . CentreonDB::escape($value2) . "'") : "NULL");
                            if ($key2 != "ehi_id") {
                                $fields[$key2] = $value2;
                            }
                        }
                        $val
                            ? $rq = "INSERT INTO extended_host_information VALUES (" . $val . ")"
                            : $rq = null;
                        $dbResult2 = $pearDB->query($rq);
                    }

                    /*
                     * Poller link ducplication
                     */
                    $dbResult = $pearDB->query("SELECT DISTINCT nagios_server_id 
                                                FROM ns_host_relation 
                                                WHERE host_host_id = '" . (int)$key . "'");
                    $fields["nagios_server_id"] = "";
                    while ($Hg = $dbResult->fetch()) {
                        $dbResult1 = $pearDB->query("INSERT INTO ns_host_relation 
                                      VALUES ('" . $Hg["nagios_server_id"] . "', '" . $maxId["MAX(host_id)"] . "')");
                        $fields["nagios_server_id"] .= $Hg["nagios_server_id"] . ",";
                    }
                    $fields["nagios_server_id"] = trim($fields["nagios_server_id"], ",");

                    /*
                     *  multiple templates & on demand macros
                     */
                    $mTpRq1 = "SELECT * 
                              FROM `host_template_relation` 
                              WHERE `host_host_id` ='" . (int)$key . "' 
                              ORDER BY `order`";
                    $dbResult3 = $pearDB->query($mTpRq1);
                    $multiTP_logStr = "";
                    while ($hst = $dbResult3->fetch()) {
                        if ($hst['host_tpl_id'] != $maxId["MAX(host_id)"]) {
                            $mTpRq2 = "INSERT INTO `host_template_relation` (`host_host_id`, `host_tpl_id`, `order`) 
                                       VALUES ('" . $maxId["MAX(host_id)"] . "', '"
                                . $pearDB->escape($hst['host_tpl_id']) . "', '" . $pearDB->escape($hst['order']) . "')";
                            $dbResult4 = $pearDB->query($mTpRq2);
                            $multiTP_logStr .= $hst['host_tpl_id'] . ",";
                        }
                    }
                    $multiTP_logStr = trim($multiTP_logStr, ",");
                    $fields["templates"] = $multiTP_logStr;

                    /*
                     * on demand macros
                     */
                    $mTpRq1 = "SELECT * FROM `on_demand_macro_host` WHERE `host_host_id` ='" . (int)$key . "'";
                    $dbResult3 = $pearDB->query($mTpRq1);
                    while ($hst = $dbResult3->fetch()) {
                        $macName = str_replace("\$", "", $hst["host_macro_name"]);
                        $macVal = $hst['host_macro_value'];
                        if (!isset($hst['is_password'])) {
                            $hst['is_password'] = '0';
                        }
                        $mTpRq2 = "INSERT INTO `on_demand_macro_host` (`host_host_id`, `host_macro_name`,
                                  `host_macro_value`, `is_password`)
                                   VALUES "
                            . "('" . $maxId["MAX(host_id)"] . "', '\$" . $pearDB->escape($macName) . "\$', '"
                            . $pearDB->escape($macVal) . "', '" . $pearDB->escape($hst["is_password"]) . "')";
                        $dbResult4 = $pearDB->query($mTpRq2);
                        $fields["_" . strtoupper($macName) . "_"] = $macVal;
                    }

                    /*
                     * Host Categorie Duplication
                     */
                    $request = "INSERT INTO hostcategories_relation
                                SELECT hostcategories_hc_id, '" . $maxId["MAX(host_id)"] . "'
                                FROM hostcategories_relation
                                WHERE host_host_id = '" . (int)$key . "'";
                    $dbResult3 = $pearDB->query($request);

                    $centreon->CentreonLogAction->insertLog("host", $maxId["MAX(host_id)"], $hostName, "a", $fields);
                }
            }
            // if all duplication names are already used, next value is never set
            if (isset($maxId['MAX(host_id)'])) {
                $centreon->user->access->updateACL([
                    'type' => 'HOST',
                    'id' => $maxId['MAX(host_id)'],
                    'action' => 'DUP',
                    'duplicate_host' => (int)$key,
                ]);
            }
        }
    }
    CentreonACL::duplicateHostAcl($hostAcl);
}

function updateHostInDB($host_id = null, $from_MC = false, $cfg = null)
{
    global $form, $centreon;

    if (!$host_id) {
        return;
    }

    if (!isset($cfg)) {
        $ret = $form->getSubmitValues();
    } else {
        $ret = $cfg;
    }

    /*
     *  Global function to use
     */

    if ($from_MC) {
        updateHost_MC($host_id);
    } else {
        updateHost($host_id, $from_MC, $ret);
    }

    /*
     *  Function for updating host parents
     *  1 - MC with deletion of existing parents
     *  2 - MC with addition of new parents
     *  3 - Normal update
     */

    if (isset($ret["mc_mod_hpar"]["mc_mod_hpar"]) && $ret["mc_mod_hpar"]["mc_mod_hpar"]) {
        updateHostHostParent($host_id);
    } elseif (isset($ret["mc_mod_hpar"]["mc_mod_hpar"]) && !$ret["mc_mod_hpar"]["mc_mod_hpar"]) {
        updateHostHostParent_MC($host_id);
    } else {
        updateHostHostParent($host_id);
    }

    # Function for updating host childs
    # 1 - MC with deletion of existing childs
    # 2 - MC with addition of new childs
    # 3 - Normal update
    if (isset($ret["mc_mod_hch"]["mc_mod_hch"]) && $ret["mc_mod_hch"]["mc_mod_hch"]) {
        updateHostHostChild($host_id);
    } elseif (isset($ret["mc_mod_hch"]["mc_mod_hch"]) && !$ret["mc_mod_hch"]["mc_mod_hch"]) {
        updateHostHostChild_MC($host_id);
    } else {
        updateHostHostChild($host_id);
    }

    # Function for updating host cg
    # 1 - MC with deletion of existing cg
    # 2 - MC with addition of new cg
    # 3 - Normal update
    if (isset($ret["mc_mod_hcg"]["mc_mod_hcg"]) && $ret["mc_mod_hcg"]["mc_mod_hcg"]) {
        updateHostContactGroup($host_id, $ret);
        updateHostContact($host_id, $ret);
    } elseif (isset($ret["mc_mod_hcg"]["mc_mod_hcg"]) && !$ret["mc_mod_hcg"]["mc_mod_hcg"]) {
        updateHostContactGroup_MC($host_id, $ret);
        updateHostContact_MC($host_id, $ret);
    } else {
        updateHostContactGroup($host_id, $ret);
        updateHostContact($host_id, $ret);
    }

    # Function for updating notification options
    # 1 - MC with deletion of existing options (Replacement)
    # 2 - MC with addition of new options (incremental)
    # 3 - Normal update
    if (isset($ret["mc_mod_notifopts"]["mc_mod_notifopts"]) && $ret["mc_mod_notifopts"]["mc_mod_notifopts"]) {
        updateHostNotifs($host_id);
    } elseif (isset($ret["mc_mod_notifopts"]["mc_mod_notifopts"]) && !$ret["mc_mod_notifopts"]["mc_mod_notifopts"]) {
        updateHostNotifs_MC($host_id);
    } else {
        updateHostNotifs($host_id);
    }

# Function for updating notification interval options
# 1 - MC with deletion of existing options (Replacement)
# 2 - MC with addition of new options (incremental)
# 3 - Normal update
    if (
        isset($ret["mc_mod_notifopt_notification_interval"]["mc_mod_notifopt_notification_interval"])
        && $ret["mc_mod_notifopt_notification_interval"]["mc_mod_notifopt_notification_interval"]
    ) {
        updateHostNotifOptionInterval($host_id);
    } elseif (
        isset($ret["mc_mod_notifopt_notification_interval"]["mc_mod_notifopt_notification_interval"])
        && !$ret["mc_mod_notifopt_notification_interval"]["mc_mod_notifopt_notification_interval"]
    ) {
        updateHostNotifOptionInterval_MC($host_id);
    } else {
        updateHostNotifOptionInterval($host_id);
    }

# Function for updating first notification delay options
# 1 - MC with deletion of existing options (Replacement)
# 2 - MC with addition of new options (incremental)
# 3 - Normal update, default behavior
    if (
        isset($ret["mc_mod_notifopt_first_notification_delay"]["mc_mod_notifopt_first_notification_delay"])
        && $ret["mc_mod_notifopt_first_notification_delay"]["mc_mod_notifopt_first_notification_delay"]
    ) {
        updateHostNotifOptionFirstNotificationDelay($host_id);
    } elseif (
        isset($ret["mc_mod_notifopt_first_notification_delay"]["mc_mod_notifopt_first_notification_delay"])
        && !$ret["mc_mod_notifopt_first_notification_delay"]["mc_mod_notifopt_first_notification_delay"]
    ) {
        updateHostNotifOptionFirstNotificationDelay_MC($host_id);
    } else {
        updateHostNotifOptionFirstNotificationDelay($host_id);
    }


# Function for updating first notification delay options
    updateHostNotifOptionRecoveryNotificationDelay($host_id);



# Function for updating notification timeperiod options
# 1 - MC with deletion of existing options (Replacement)
# 2 - MC with addition of new options (incremental)
# 3 - Normal update
    if (
        isset($ret["mc_mod_notifopt_timeperiod"]["mc_mod_notifopt_timeperiod"])
        && $ret["mc_mod_notifopt_timeperiod"]["mc_mod_notifopt_timeperiod"]
    ) {
        updateHostNotifOptionTimeperiod($host_id);
    } elseif (
        isset($ret["mc_mod_notifopt_timeperiod"]["mc_mod_notifopt_timeperiod"])
        && !$ret["mc_mod_notifopt_timeperiod"]["mc_mod_notifopt_timeperiod"]
    ) {
        updateHostNotifOptionTimeperiod_MC($host_id);
    } else {
        updateHostNotifOptionTimeperiod($host_id);
    }

# Function for updating host hg
# 1 - MC with deletion of existing hg
# 2 - MC with addition of new hg
# 3 - Normal update
    if (isset($ret["mc_mod_hhg"]["mc_mod_hhg"]) && $ret["mc_mod_hhg"]["mc_mod_hhg"]) {
        updateHostHostGroup($host_id);
    } elseif (isset($ret["mc_mod_hhg"]["mc_mod_hhg"]) && !$ret["mc_mod_hhg"]["mc_mod_hhg"]) {
        updateHostHostGroup_MC($host_id);
    } else {
        updateHostHostGroup($host_id);
    }

# Function for updating host hc
# 1 - MC with deletion of existing hc
# 2 - MC with addition of new hc
# 3 - Normal update
    if (isset($ret["mc_mod_hhc"]["mc_mod_hhc"]) && $ret["mc_mod_hhc"]["mc_mod_hhc"]) {
        updateHostHostCategory($host_id);
    } elseif (isset($ret["mc_mod_hhc"]["mc_mod_hhc"]) && !$ret["mc_mod_hhc"]["mc_mod_hhc"]) {
        updateHostHostCategory_MC($host_id);
    } else {
        updateHostHostCategory($host_id, $ret);
    }

# Function for updating host template
# 1 - MC with deletion of existing template
# 2 - MC with addition of new template
# 3 - Normal update
    if (isset($ret["mc_mod_htpl"]["mc_mod_htpl"]) && $ret["mc_mod_htpl"]["mc_mod_htpl"]) {
        updateHostTemplateService($host_id);
    } elseif (isset($ret["mc_mod_htpl"]["mc_mod_htpl"]) && !$ret["mc_mod_htpl"]["mc_mod_htpl"]) {
        updateHostTemplateService_MC($host_id);
    } else {
        updateHostTemplateService($host_id);
    }

    if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"]) {
        if (isset($ret["host_template_model_htm_id"])) {
            createHostTemplateService($host_id, $ret["host_template_model_htm_id"]);
        } elseif ($centreon->user->get_version()) {
            createHostTemplateService($host_id);
        }
    }

    /*
     * Host extended information
     */
    if ($from_MC) {
        updateHostExtInfos_MC($host_id);
    } else {
        updateHostExtInfos($host_id, $ret);
    }

    # Function for updating host hg
    # 1 - MC with deletion of existing hg
    # 2 - MC with addition of new hg
    # 3 - Normal update
    updateNagiosServerRelation($host_id);
    return ($host_id);
}

function insertHostInDB($ret = array(), $macro_on_demand = null)
{
    global $centreon, $form;

    isset($ret["nagios_server_id"])
        ? $server_id = $ret["nagios_server_id"]
        : $server_id = $form->getSubmitValue("nagios_server_id");
    if (!isset($server_id) || $server_id == "" || $server_id == 0) {
        $server_id = null;
    }

    $host_id = insertHost($ret, $macro_on_demand, $server_id);
    updateHostHostParent($host_id, $ret);
    updateHostHostChild($host_id);
    updateHostContactGroup($host_id, $ret);
    updateHostContact($host_id, $ret);
    updateHostNotifs($host_id, $ret);
    updateHostNotifOptionInterval($host_id, $ret);
    updateHostNotifOptionTimeperiod($host_id, $ret);
    updateHostNotifOptionFirstNotificationDelay($host_id, $ret);
    updateHostHostGroup($host_id, $ret);
    updateHostHostCategory($host_id, $ret);
    updateHostTemplateService($host_id);
    updateNagiosServerRelation($host_id, $ret);
    $ret = $form->getSubmitValues();
    if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"]) {
        createHostTemplateService($host_id);
    }
    $centreon->user->access->updateACL(array(
        "type" => 'HOST',
        'id' => $host_id,
        "action" => "ADD",
        "access_grp_id" => (isset($ret["acl_groups"]) ? $ret["acl_groups"] : null),
    ));
    insertHostExtInfos($host_id, $ret);
    return ($host_id);
}

function insertHost($ret, $macro_on_demand = null, $server_id = null)
{
    global $form, $pearDB, $centreon, $is_admin;

    $hostObj = new CentreonHost($pearDB);
    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    $host = new CentreonHost($pearDB);

    if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != null) {
        $ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
        $ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
        $ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
    }
    if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != null) {
        $ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
        $ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
        $ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
    }

    // For Centreon 2, we no longer need "host_template_model_htm_id" in Nagios 3
    // but we try to keep it compatible with Nagios 2 which needs "host_template_model_htm_id"
    if (isset($_POST['nbOfSelect'])) {
        $dbResult = $pearDB->query("SELECT host_id FROM `host` WHERE host_register='0' LIMIT 1");
        $result = $dbResult->fetch();
        $ret["host_template_model_htm_id"] = $result["host_id"];
        $dbResult->closeCursor();
    }

    $ret["host_name"] = $host->checkIllegalChar($ret["host_name"], $server_id);

    $bindParams = sanitizeFormHostParameters($ret);
    $params = [];
    foreach (array_keys($bindParams) as $token) {
        $params[] = ltrim($token, ':');
    }

    $rq = "INSERT INTO `host` ( " . implode(', ', $params) . ")
           VALUES (" . implode(", ", array_keys($bindParams)) . " )";
    $stmt = $pearDB->prepare($rq);
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $stmt->bindValue($token, $value, $paramType);
        }
    }

    $stmt->execute();
    $dbResult = $pearDB->query("SELECT MAX(host_id) FROM host");
    $host_id = $dbResult->fetch();

    /*
     *  Insert multiple templates
     */
    $multiTP_logStr = "";
    if (isset($ret["use"]) && $ret["use"]) {
        $already_stored = array();
        $tplTab = preg_split("/\,/", $ret["use"]);
        $j = 0;
        foreach ($tplTab as $val) {
            $tplId = getMyHostID($val);
            if (
                !isset($already_stored[$tplId])
                && $tplId && hasNoInfiniteLoop($host_id['MAX(host_id)'], $tplId) === true
            ) {
                $rq = "INSERT INTO host_template_relation (`host_host_id`, `host_tpl_id`, `order`) 
                        VALUES (" . $host_id['MAX(host_id)'] . ", " . $tplId . ", " . $j . ")";
                $dbResult = $pearDB->query($rq);
                $multiTP_logStr .= $tplId . ",";
                $j++;
                $already_stored[$tplId] = 1;
            }
        }
    } elseif (isset($_REQUEST['tpSelect'])) {
        $hostObj->setTemplates($host_id['MAX(host_id)'], $_REQUEST['tpSelect']);
    }

    /*
     * Insert on demand macros
     * Keeping it just in case it could used somewhere else
     */

    if (isset($macro_on_demand)) {
        $my_tab = $macro_on_demand;
        if (isset($my_tab['nbOfMacro'])) {
            $already_stored = array();
            for ($i = 0; $i <= $my_tab['nbOfMacro']; $i++) {
                $macInput = "macroInput_" . $i;
                $macValue = "macroValue_" . $i;
                if (
                    isset($my_tab[$macInput])
                    && !isset($already_stored[strtolower($my_tab[$macInput])]) && $my_tab[$macInput]
                ) {
                    $my_tab[$macInput] = str_replace("\$_HOST", "", $my_tab[$macInput]);
                    $my_tab[$macInput] = str_replace("\$", "", $my_tab[$macInput]);
                    $macName = $my_tab[$macInput];
                    $macVal = $my_tab[$macValue];
                    $rq = "INSERT INTO on_demand_macro_host (`host_macro_name`, `host_macro_value`,
                           `description`, `host_host_id`, `macro_order`) 
                           VALUES ('\$_HOST" . strtoupper($macName) . "\$', '" . CentreonDB::escape($macVal) . "', "
                        . $host_id['MAX(host_id)'] . ", " . $i . ")";
                    $dbResult = $pearDB->query($rq);
                    $fields["_" . strtoupper($my_tab[$macInput]) . "_"] = $my_tab[$macValue];
                    $already_stored[strtolower($my_tab[$macInput])] = 1;
                }
            }
        }
    } elseif (
        isset($_REQUEST['macroInput'])
        && isset($_REQUEST['macroValue'])
    ) {
        $macroDescription = array();
        foreach ($_REQUEST as $nam => $ele) {
            if (preg_match_all("/macroDescription_(\w+)$/", $nam, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $macroDescription[$match[1]] = $ele;
                }
            }
        }
        $hostObj->insertMacro(
            $host_id['MAX(host_id)'],
            $_REQUEST['macroInput'],
            $_REQUEST['macroValue'],
            $_REQUEST['macroPassword'] ?? [],
            $macroDescription,
            false,
            $ret["command_command_id"]
        );
    }

    if (isset($ret['criticality_id'])) {
        setHostCriticality($host_id['MAX(host_id)'], $ret['criticality_id']);
    }

    if (isset($ret['acl_groups']) && count($ret['acl_groups'])) {
        foreach ($ret['acl_groups'] as $groupId) {
            $sql = "SELECT acl_res_id FROM acl_res_group_relations WHERE acl_group_id = " . $groupId;
            $res = $pearDB->query($sql);

            $query = "INSERT INTO acl_resources_host_relations (acl_res_id, host_host_id) VALUES ";
            $first = true;

            while ($aclRes = $res->fetch()) {
                if (!$first) {
                    $query .= ", ";
                } else {
                    $first = false;
                }
                $query .= "(" . $aclRes['acl_res_id'] . ", " . $pearDB->escape($host_id['MAX(host_id)']) . ")";
            }

            if (!$first) {
                $pearDB->query($query);
            }
        }
    }
    /*
     *  Logs
     */

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog(
        "host",
        $host_id["MAX(host_id)"],
        CentreonDB::escape($ret["host_name"]),
        "a",
        $fields
    );

    return ($host_id["MAX(host_id)"]);
}

/**
 * @global HTML_QuickFormCustom $form
 * @global CentreonDB $pearDB
 * @param int $host_id
 * @param array $ret
 */
function insertHostExtInfos($host_id, $ret)
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    if (empty($ret)) {
        $ret = $form->getSubmitValues();
    }

    /*
     * Check if image selected isn't a directory
     */
    if (isset($ret["ehi_icon_image"]) && strrchr("REP_", $ret["ehi_icon_image"])) {
        $ret["ehi_icon_image"] = null;
    }
    if (isset($ret["ehi_statusmap_image"]) && strrchr("REP_", $ret["ehi_statusmap_image"])) {
        $ret["ehi_statusmap_image"] = null;
    }
    /*
     *
     */
    $rq = "INSERT INTO `extended_host_information` " .
        "( `ehi_id` , `host_host_id` , `ehi_notes` , `ehi_notes_url` , " .
        "`ehi_action_url` , `ehi_icon_image` , `ehi_icon_image_alt` , " .
        "`ehi_statusmap_image` , `ehi_2d_coords` , " .
        "`ehi_3d_coords` )" .
        "VALUES ( ";
    $rq .= "NULL, " . $host_id . ", ";
    isset($ret["ehi_notes"]) && $ret["ehi_notes"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_notes"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_notes_url"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_action_url"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_icon_image"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_icon_image_alt"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_statusmap_image"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_2d_coords"]) . "', "
        : $rq .= "NULL, ";
    isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_3d_coords"]) . "' "
        : $rq .= "NULL ";
    $rq .= ")";
    $dbResult = $pearDB->query($rq);
}

/*
 * Get list of host templates recursively
 */

function getHostListInUse($hst_list, $hst)
{
    global $pearDB;

    $str = $hst_list;
    $dbResult = $pearDB->query("SELECT `host_tpl_id` FROM `host_template_relation` WHERE host_host_id ='" . $hst . "'");
    while ($result = $dbResult->fetch()) {
        $str .= ",'" . $result['host_tpl_id'] . "'";
        $str = getHostListInUse($str, $result['host_tpl_id']);
    }
    $dbResult->closeCursor();
    return $str;
}

/*
 *  Checks if the service that is gonna be deleted is actually
 *  associated to another host template
 *  if yes, we do not delete the service
 *  Function returns true if it doesn't have to be deleted, otherwise it returns false
 */

function serviceIsInUse($svc_id, $host_list)
{
    global $pearDB;

    $hst_list = "";
    $flag_first = 1;
    foreach ($host_list as $val) {
        if (isset($val)) {
            if (!$flag_first) {
                $hst_list .= ",'" . $val . "'";
            } else {
                $hst_list .= "'" . $val . "'";
                $flag_first = 0;
            }
            $hst_list = getHostListInUse($hst_list, $val);
        }
    }
    if ($hst_list == "") {
        $hst_list = "NULL";
    }
    $rq = "SELECT service_id " .
        "FROM service svc, host_service_relation hsr " .
        "WHERE hsr.service_service_id = svc.service_template_model_stm_id " .
        "AND hsr.service_service_id = '" . $svc_id . "' " .
        "AND hsr.host_host_id IN (" . $hst_list . ")";
    $dbResult = $pearDB->query($rq);
    if ($dbResult->rowCount() >= 1) {
        return true;
    }
    return false;
}

/*
 * 	this function cleans all the services that were linked to the removed host template
 */

function deleteHostServiceMultiTemplate($hID, $scndHID, $host_list, $antiLoop = null)
{
    global $pearDB;

    if (isset($antiLoop[$scndHID]) && $antiLoop[$scndHID]) {
        return 0;
    }
    $dbResult = $pearDB->query("SELECT service_service_id " .
        "FROM `service` svc, `host_service_relation` hsr " .
        "WHERE svc.service_id = hsr.service_service_id " .
        "AND svc.service_register = '0' " .
        "AND hsr.host_host_id = '" . $scndHID . "'");
    while ($svcID = $dbResult->fetch()) {
        if (!serviceIsInUse($svcID['service_service_id'], $host_list)) {
            $rq2 = "DELETE hsr, svc FROM `host_service_relation` hsr, `service` svc " .
                "WHERE hsr.service_service_id = svc.service_id " .
                "AND svc.service_template_model_stm_id = '" . $svcID['service_service_id'] . "' " .
                "AND svc.service_register = '1' " .
                "AND hsr.host_host_id = '" . $hID . "'";
            $pearDB->query($rq2);
        }
    }
    $dbResult->closeCursor();

    $rq = "SELECT host_tpl_id " .
        "FROM host_template_relation " .
        "WHERE host_host_id = '" . $scndHID . "' " .
        "ORDER BY `order`";

    $dbResult = $pearDB->query($rq);
    while ($result = $dbResult->fetch()) {
        $dbResult2 = $pearDB->query("SELECT service_service_id " .
            "FROM `service` svc, `host_service_relation` hsr " .
            "WHERE svc.service_id = hsr.service_service_id " .
            "AND svc.service_register = '0' " .
            "AND hsr.host_host_id = '" . $result["host_tpl_id"] . "'");
        while ($svcID = $dbResult2->fetch()) {
            $rq2 = "DELETE hsr, svc FROM `host_service_relation` hsr, `service` svc " .
                "WHERE hsr.service_service_id = svc.service_id " .
                "AND svc.service_template_model_stm_id = '" . $svcID['service_service_id'] . "' " .
                "AND svc.service_register = '1' " .
                "AND hsr.host_host_id = '" . $hID . "'";
            $dbResult4 = $pearDB->query($rq2);
        }
        $antiLoop[$scndHID] = 1;
        deleteHostServiceMultiTemplate($hID, $result["host_tpl_id"], $host_list, $antiLoop);
    }
    $dbResult->closeCursor();
}

function updateHost($host_id = null, $from_MC = false, $cfg = null)
{
    global $form, $pearDB, $centreon;

    $hostObj = new CentreonHost($pearDB);

    if (!$host_id) {
        return;
    }

    $host = new CentreonHost($pearDB);

    $ret = array();
    if (!isset($cfg)) {
        $ret = $form->getSubmitValues();
    } else {
        $ret = $cfg;
    }

    isset($ret["nagios_server_id"])
        ? $server_id = $ret["nagios_server_id"]
        : $server_id = $form->getSubmitValue("nagios_server_id");
    if (!isset($server_id) || $server_id == "" || $server_id == 0) {
        $server_id = null;
    }

    if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != null) {
        $ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
        $ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
        $ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
    }
    if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != null) {
        $ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
        $ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
        $ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
    }
    $ret["host_name"] = $host->checkIllegalChar($ret["host_name"], $server_id);
    $bindParams = sanitizeFormHostParameters($ret);
    $rq = "UPDATE host SET ";
    foreach (array_keys($bindParams) as $token) {
        $rq .= ltrim($token, ':') . " = " . $token . ", ";
    }
    $rq = rtrim($rq, ', ');
    $rq .= " WHERE host_id = :hostId";
    $stmt = $pearDB->prepare($rq);
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $stmt->bindValue($token, $value, $paramType);
        }
    }
    $stmt->bindValue(':hostId', $host_id, \PDO::PARAM_INT);
    $stmt->execute();

    /*
     *  Update multiple templates
     */
    if (isset($_REQUEST['tpSelect'])) {
        /* Cleanup host service link to host template to be removed */
        $newTp = array();
        foreach ($_POST['tpSelect'] as $tmpl) {
            $newTp[$tmpl] = $tmpl;
        }

        $dbResult = $pearDB->query("SELECT `host_tpl_id` 
                                    FROM `host_template_relation`
                                    WHERE `host_host_id` = '" . $host_id . "'");
        while ($hst = $dbResult->fetch()) {
            if (!isset($newTp[$hst['host_tpl_id']])) {
                deleteHostServiceMultiTemplate($host_id, $hst['host_tpl_id'], $newTp);
            }
        }

        /* Set template */
        $hostObj->setTemplates($host_id, $_REQUEST['tpSelect']);
    } elseif (isset($ret["use"]) && $ret["use"]) {
        $already_stored = array();
        $tplTab = preg_split("/\,/", $ret["use"]);
        $j = 0;
        $DBRES = $pearDB->query("DELETE FROM `host_template_relation` WHERE `host_host_id` = '" . $host_id . "'");
        foreach ($tplTab as $val) {
            $tplId = getMyHostID($val);
            if (!isset($already_stored[$tplId]) && $tplId) {
                $rq = "INSERT INTO host_template_relation (`host_host_id`, `host_tpl_id`, `order`) 
                        VALUES (" . $host_id . ", " . $tplId . ", " . $j . ")";
                $dbResult = $pearDB->query($rq);
                $j++;
                $already_stored[$tplId] = 1;
            }
        }
    } else {
        /* Cleanup host service link to host template to be removed */
        $newTp = array();

        $dbResult = $pearDB->query("SELECT `host_tpl_id` 
                                    FROM `host_template_relation` 
                                    WHERE `host_host_id` = '" . $host_id . "'");
        while ($hst = $dbResult->fetch()) {
            if (!isset($newTp[$hst['host_tpl_id']])) {
                deleteHostServiceMultiTemplate($host_id, $hst['host_tpl_id'], $newTp);
            }
        }

        /* Set template */
        $hostObj->setTemplates($host_id, array());
    }

    /*
     *  Update demand macros
     */
    if (
        isset($_REQUEST['macroInput']) &&
        isset($_REQUEST['macroValue'])
    ) {
        $macroDescription = array();
        foreach ($_REQUEST as $nam => $ele) {
            if (preg_match_all("/^macroDescription_(\w+)$/", $nam, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $macroDescription[$match[1]] = $ele;
                }
            }
        }
        $hostObj->insertMacro(
            $host_id,
            $_REQUEST['macroInput'],
            $_REQUEST['macroValue'],
            $_REQUEST['macroPassword'],
            $macroDescription,
            false,
            $ret["command_command_id"]
        );
    } else {
        $pearDB->query("DELETE FROM on_demand_macro_host WHERE host_host_id = '" . CentreonDB::escape($host_id) . "'");
    }

    if (isset($ret['criticality_id'])) {
        setHostCriticality($host_id, $ret['criticality_id']);
    }

    /*
     *  Logs
     */
    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("host", $host_id, CentreonDB::escape($ret["host_name"]), "c", $fields);
    $centreon->user->access->updateACL(array("type" => 'HOST', 'id' => $host_id, "action" => "UPDATE"));
}

function updateHost_MC($host_id = null)
{
    global $form, $pearDB, $centreon;

    $hostObj = new CentreonHost($pearDB);
    if (!$host_id) {
        return;
    }

    $ret = array();
    $ret = $form->getSubmitValues();
    if (isset($ret["command_command_id_arg1"]) && $ret["command_command_id_arg1"] != null) {
        $ret["command_command_id_arg1"] = str_replace("\n", "#BR#", $ret["command_command_id_arg1"]);
        $ret["command_command_id_arg1"] = str_replace("\t", "#T#", $ret["command_command_id_arg1"]);
        $ret["command_command_id_arg1"] = str_replace("\r", "#R#", $ret["command_command_id_arg1"]);
    }
    if (isset($ret["command_command_id_arg2"]) && $ret["command_command_id_arg2"] != null) {
        $ret["command_command_id_arg2"] = str_replace("\n", "#BR#", $ret["command_command_id_arg2"]);
        $ret["command_command_id_arg2"] = str_replace("\t", "#T#", $ret["command_command_id_arg2"]);
        $ret["command_command_id_arg2"] = str_replace("\r", "#R#", $ret["command_command_id_arg2"]);
    }

    // For Centreon 2, we no longer need "host_template_model_htm_id" in Nagios 3
    // but we try to keep it compatible with Nagios 2 which needs "host_template_model_htm_id"
    if (isset($_POST['nbOfSelect'])) {
        $dbResult = $pearDB->query("SELECT host_id FROM `host` WHERE host_register='0' LIMIT 1");
        $result = $dbResult->fetch();
        $ret["host_template_model_htm_id"] = $result["host_id"];
        $dbResult->closeCursor();
    }

    $bindParams = sanitizeFormHostParameters($ret);
    $rq = "UPDATE host SET ";
    foreach (array_keys($bindParams) as $token) {
        $rq .= ltrim($token, ':') . " = " . $token . ", ";
    }
    $rq = rtrim($rq, ', ');
    $rq .= " WHERE host_id = :hostId";
    $stmt = $pearDB->prepare($rq);
    foreach ($bindParams as $token => $bindValues) {
        foreach ($bindValues as $paramType => $value) {
            $stmt->bindValue($token, $value, $paramType);
        }
    }
    $stmt->bindValue(':hostId', $host_id, \PDO::PARAM_INT);
    $stmt->execute();

    /*
     *  update multiple templates
     */
    if (isset($_REQUEST['tpSelect'])) {
        $oldTp = array();
        if (isset($_POST['mc_mod_tplp']['mc_mod_tplp']) && $_POST['mc_mod_tplp']['mc_mod_tplp'] == 0) {
            $dbResult = $pearDB->query("SELECT `host_tpl_id` 
                                        FROM `host_template_relation` 
                                        WHERE `host_host_id`='" . $host_id . "'");
            while ($hst = $dbResult->fetch()) {
                $oldTp[$hst["host_tpl_id"]] = $hst["host_tpl_id"];
            }
        }
        $hostObj->setTemplates($host_id, $_REQUEST['tpSelect'], $oldTp);
    }

    /*
     *  Update on demand macros
     */
    $macroDescription = array();
    foreach ($_REQUEST as $nam => $ele) {
        if (preg_match_all("/^macroDescription_(\w+)$/", $nam, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $macroDescription[$match[1]] = $ele;
            }
        }
    }

    if (isset($_REQUEST['macroInput']) && isset($_REQUEST['macroValue'])) {
        $hostObj->insertMacro(
            $host_id,
            $_REQUEST['macroInput'],
            $_REQUEST['macroValue'],
            $_REQUEST['macroPassword'] ?? [],
            $macroDescription,
            true
        );
    }

    if (isset($ret['criticality_id']) && $ret['criticality_id']) {
        setHostCriticality($host_id, $ret['criticality_id']);
    }

    $dbResultX = $pearDB->query("SELECT host_name FROM `host` WHERE host_id='" . $host_id . "' LIMIT 1");
    $row = $dbResultX->fetch();

    /* Prepare value for changelog */
    $fields = CentreonLogAction::prepareChanges($ret);
    $centreon->CentreonLogAction->insertLog("host", $host_id, $row["host_name"], "mc", $fields);
}

function updateHostHostParent($host_id = null, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "DELETE FROM host_hostparent_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);

    if (isset($ret["host_parents"])) {
        $ret = $ret["host_parents"];
    } else {
        $ret = CentreonUtils::mergeWithInitialValues($form, 'host_parents');
    }

    for ($i = 0; $i < count($ret); $i++) {
        if (isset($ret[$i]) && $ret[$i] != $host_id && $ret[$i] != "") {
            $rq = "INSERT INTO host_hostparent_relation ";
            $rq .= "(host_parent_hp_id, host_host_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $ret[$i] . "', '" . $host_id . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/*
 * For massive change. We just add the new list if the elem doesn't exist yet
 */

function updateHostHostParent_MC($host_id = null, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "SELECT * FROM host_hostparent_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
    $hpars = array();
    while ($arr = $dbResult->fetch()) {
        $hpars[$arr["host_parent_hp_id"]] = $arr["host_parent_hp_id"];
    }

    $ret = $form->getSubmitValue("host_parents");
    if (is_array($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($hpars[$ret[$i]]) && isset($ret[$i])) {
                if (isset($ret[$i]) && $ret[$i] != $host_id && $ret[$i] != "") {
                    $rq = "INSERT INTO host_hostparent_relation ";
                    $rq .= "(host_parent_hp_id, host_host_id) ";
                    $rq .= "VALUES ";
                    $rq .= "('" . $ret[$i] . "', '" . $host_id . "')";
                    $dbResult = $pearDB->query($rq);
                }
            }
        }
    }
}

function updateHostHostChild($host_id = null)
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "DELETE FROM host_hostparent_relation ";
    $rq .= "WHERE host_parent_hp_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);

    $ret = array();
    $ret = CentreonUtils::mergeWithInitialValues($form, 'host_childs');
    for ($i = 0; $i < count($ret); $i++) {
        if (isset($ret[$i]) && $ret[$i] != $host_id && $ret[$i] != "") {
            $rq = "INSERT INTO host_hostparent_relation ";
            $rq .= "(host_parent_hp_id, host_host_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $host_id . "', '" . $ret[$i] . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/**
 * For massive change. We just add the new list if the elem doesn't exist yet
 */
function updateHostHostChild_MC($host_id = null)
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "SELECT * FROM host_hostparent_relation ";
    $rq .= "WHERE host_parent_hp_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
    $hchs = array();
    while ($arr = $dbResult->fetch()) {
        $hchs[$arr["host_host_id"]] = $arr["host_host_id"];
    }

    $ret = $form->getSubmitValue("host_childs");
    if (is_array($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($hchs[$ret[$i]]) && isset($ret[$i])) {
                if (isset($ret[$i]) && $ret[$i] != $host_id && $ret[$i] != "") {
                    $rq = "INSERT INTO host_hostparent_relation ";
                    $rq .= "(host_parent_hp_id, host_host_id) ";
                    $rq .= "VALUES ";
                    $rq .= "('" . $host_id . "', '" . $ret[$i] . "')";
                    $dbResult = $pearDB->query($rq);
                }
            }
        }
    }
}

/**
 *
 */
function updateHostExtInfos($host_id = null, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }

    /*
     * Check if image selected isn't a directory
     */
    if (isset($ret["ehi_icon_image"]) && strrchr("REP_", $ret["ehi_icon_image"])) {
        $ret["ehi_icon_image"] = null;
    }
    if (isset($ret["ehi_statusmap_image"]) && strrchr("REP_", $ret["ehi_statusmap_image"])) {
        $ret["ehi_statusmap_image"] = null;
    }
    /*
     *
     */
    $rq = "UPDATE extended_host_information ";
    $rq .= "SET ehi_notes = ";
    isset($ret["ehi_notes"]) && $ret["ehi_notes"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_notes"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "ehi_notes_url = ";
    isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_notes_url"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "ehi_action_url = ";
    isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_action_url"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "ehi_icon_image = ";
    isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_icon_image"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "ehi_icon_image_alt = ";
    isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_icon_image_alt"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "ehi_statusmap_image = ";
    isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_statusmap_image"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "ehi_2d_coords = ";
    isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_2d_coords"]) . "', "
        : $rq .= "NULL, ";
    $rq .= "ehi_3d_coords = ";
    isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != null
        ? $rq .= "'" . CentreonDB::escape($ret["ehi_3d_coords"]) . "' "
        : $rq .= "NULL ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
}

/**
 *
 */
function updateHostExtInfos_MC($host_id = null)
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $ret = $form->getSubmitValues();
    $rq = "UPDATE extended_host_information SET ";
    if (isset($ret["ehi_notes"]) && $ret["ehi_notes"] != null) {
        $rq .= "ehi_notes = '" . CentreonDB::escape($ret["ehi_notes"]) . "', ";
    }
    if (isset($ret["ehi_notes_url"]) && $ret["ehi_notes_url"] != null) {
        $rq .= "ehi_notes_url = '" . CentreonDB::escape($ret["ehi_notes_url"]) . "', ";
    }
    if (isset($ret["ehi_action_url"]) && $ret["ehi_action_url"] != null) {
        $rq .= "ehi_action_url = '" . CentreonDB::escape($ret["ehi_action_url"]) . "', ";
    }
    if (isset($ret["ehi_icon_image"]) && $ret["ehi_icon_image"] != null) {
        $rq .= "ehi_icon_image = '" . CentreonDB::escape($ret["ehi_icon_image"]) . "', ";
    }
    if (isset($ret["ehi_icon_image_alt"]) && $ret["ehi_icon_image_alt"] != null) {
        $rq .= "ehi_icon_image_alt = '" . CentreonDB::escape($ret["ehi_icon_image_alt"]) . "', ";
    }
    if (isset($ret["ehi_statusmap_image"]) && $ret["ehi_statusmap_image"] != null) {
        $rq .= "ehi_statusmap_image = '" . CentreonDB::escape($ret["ehi_statusmap_image"]) . "', ";
    }
    if (isset($ret["ehi_2d_coords"]) && $ret["ehi_2d_coords"] != null) {
        $rq .= "ehi_2d_coords = '" . CentreonDB::escape($ret["ehi_2d_coords"]) . "', ";
    }
    if (isset($ret["ehi_3d_coords"]) && $ret["ehi_3d_coords"] != null) {
        $rq .= "ehi_3d_coords = '" . CentreonDB::escape($ret["ehi_3d_coords"]) . "', ";
    }
    if (strcmp("UPDATE extended_host_information SET ", $rq)) {
        // Delete last ',' in request
        $rq[strlen($rq) - 2] = " ";
        $rq .= "WHERE host_host_id = '" . $host_id . "'";
        $dbResult = $pearDB->query($rq);
    }
}

/**
 *
 */
function updateHostContactGroup($host_id, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "DELETE FROM contactgroup_host_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);

    $ret = isset($ret["host_cgs"]) ? $ret["host_cgs"] : CentreonUtils::mergeWithInitialValues($form, 'host_cgs');
    $cg = new CentreonContactgroup($pearDB);
    for ($i = 0; $i < count($ret); $i++) {
        if (!is_numeric($ret[$i])) {
            $res = $cg->insertLdapGroup($ret[$i]);
            if ($res != 0) {
                $ret[$i] = $res;
            } else {
                continue;
            }
        }
        if (isset($ret[$i]) && $ret[$i] && $ret[$i] != "") {
            $rq = "INSERT INTO contactgroup_host_relation ";
            $rq .= "(host_host_id, contactgroup_cg_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $host_id . "', '" . $ret[$i] . "')";
            $dbResult = $pearDB->query($rq);
        }
    }
}

/*
 *  Only for Nagios 3
 */

function updateHostContact($host_id, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }
    $rq = "DELETE FROM contact_host_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);

    $ret = isset($ret["host_cs"]) ? $ret["host_cs"] : CentreonUtils::mergeWithInitialValues($form, 'host_cs');
    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO contact_host_relation ";
        $rq .= "(host_host_id, contact_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . $host_id . "', '" . $ret[$i] . "')";
        $dbResult = $pearDB->query($rq);
    }
}

/**
 * For massive change. We just add the new list if the elem doesn't exist yet
 */
function updateHostContactGroup_MC($host_id, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "SELECT * FROM contactgroup_host_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
    $cgs = array();
    while ($arr = $dbResult->fetch()) {
        $cgs[$arr["contactgroup_cg_id"]] = $arr["contactgroup_cg_id"];
    }
    $ret = $form->getSubmitValue("host_cgs");
    if (is_array($ret)) {
        $cg = new CentreonContactgroup($pearDB);
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($cgs[$ret[$i]])) {
                if (!is_numeric($ret[$i])) {
                    $res = $cg->insertLdapGroup($ret[$i]);
                    if ($res != 0) {
                        $ret[$i] = $res;
                    } else {
                        continue;
                    }
                }
                if (isset($ret[$i]) && $ret[$i] && $ret[$i] != "") {
                    $rq = "INSERT INTO contactgroup_host_relation ";
                    $rq .= "(host_host_id, contactgroup_cg_id) ";
                    $rq .= "VALUES ";
                    $rq .= "('" . $host_id . "', '" . $ret[$i] . "')";
                    $dbResult = $pearDB->query($rq);
                }
            }
        }
    }
}

/**
 * For massive change. We just add the new list if the elem doesn't exist yet
 */
function updateHostContact_MC($host_id, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "SELECT * FROM contact_host_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
    $cs = array();
    while ($arr = $dbResult->fetch()) {
        $cs[$arr["contact_id"]] = $arr["contact_id"];
    }
    $ret = $form->getSubmitValue("host_cs");
    if (is_array($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($cs[$ret[$i]])) {
                $rq = "INSERT INTO contact_host_relation ";
                $rq .= "(host_host_id, contact_id) ";
                $rq .= "VALUES ";
                $rq .= "('" . $host_id . "', '" . $ret[$i] . "')";
                $dbResult = $pearDB->query($rq);
            }
        }
    }
}

/**
 *
 */
function updateHostNotifs($host_id = null, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    if (isset($ret["host_notifOpts"])) {
        $ret = $ret["host_notifOpts"];
    } else {
        $ret = $form->getSubmitValue("host_notifOpts");
    }

    $rq = "UPDATE host SET ";
    $rq .= "host_notification_options  = ";
    isset($ret) && $ret != null ? $rq .= "'" . implode(",", array_keys($ret)) . "' " : $rq .= "NULL ";
    $rq .= "WHERE host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
}

// For massive change. incremental mode
function updateHostNotifs_MC($host_id = null)
{
    if (!$host_id) {
        return;
    }

    global $form;
    global $pearDB;

    $rq = "SELECT host_notification_options FROM host ";
    $rq .= "WHERE host_id = '" . $host_id . "' LIMIT 1";
    $dbResult = $pearDB->query($rq);
    $host = array_map("myDecode", $dbResult->fetch());

    $ret = $form->getSubmitValue("host_notifOpts");
    if (!isset($ret) || !$ret) {
        return;
    }

    $temp = (isset($host["host_notification_options"]))
        ? $host["host_notification_options"] . "," . implode(",", array_keys($ret))
        : implode(",", array_keys($ret));

    $rq = "UPDATE host SET ";
    $rq .= "host_notification_options = '" . trim($temp, ',') . "' ";
    $rq .= "WHERE host_id = '" . $host_id . "'";
    $pearDB->query($rq);
}

function updateHostNotifOptionInterval($host_id = null, $ret = array())
{
    if (!$host_id) {
        return;
    }
    global $form;
    global $pearDB;

    if (isset($ret["host_notification_interval"])) {
        $ret = $ret["host_notification_interval"];
    } else {
        $ret = $form->getSubmitValue("host_notification_interval");
    }

    $rq = "UPDATE host SET ";
    $rq .= "host_notification_interval = ";
    isset($ret) && $ret != null ? $rq .= "'" . $ret . "' " : $rq .= "NULL ";
    $rq .= "WHERE host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
}

/**
 * For massive change. incremental mode
 */
function updateHostNotifOptionInterval_MC($host_id = null)
{
    if (!$host_id) {
        return;
    }
    global $form;
    global $pearDB;

    $ret = $form->getSubmitValue("host_notification_interval");

    if (isset($ret) && $ret != null) {
        $rq = "UPDATE host SET ";
        $rq .= "host_notification_interval = '" . $ret . "' ";
        $rq .= "WHERE host_id = '" . $host_id . "'";
        $dbResult = $pearDB->query($rq);
    }
}

function updateHostNotifOptionTimeperiod($host_id = null, $ret = array())
{
    if (!$host_id) {
        return;
    }
    global $form;
    global $pearDB;

    if (isset($ret["timeperiod_tp_id2"])) {
        $ret = $ret["timeperiod_tp_id2"];
    } else {
        $ret = $form->getSubmitValue("timeperiod_tp_id2");
    }

    $rq = "UPDATE host SET ";
    $rq .= "timeperiod_tp_id2 = ";
    isset($ret) && $ret != null ? $rq .= "'" . $ret . "' " : $rq .= "NULL ";
    $rq .= "WHERE host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
}

/**
 * For massive change. incremental mode
 */
function updateHostNotifOptionTimeperiod_MC($host_id = null)
{
    if (!$host_id) {
        return;
    }
    global $form;
    global $pearDB;

    $ret = $form->getSubmitValue("timeperiod_tp_id2");

    if (isset($ret) && $ret != null) {
        $rq = "UPDATE host SET ";
        $rq .= "timeperiod_tp_id2 = '" . $ret . "' ";
        $rq .= "WHERE host_id = '" . $host_id . "'";
        $dbResult = $pearDB->query($rq);
    }
}

function updateHostNotifOptionFirstNotificationDelay($host_id = null, $ret = array())
{
    if (!$host_id) {
        return;
    }
    global $form;
    global $pearDB;

    if (isset($ret["host_first_notification_delay"])) {
        $ret = $ret["host_first_notification_delay"];
    } else {
        $ret = $form->getSubmitValue("host_first_notification_delay");
    }


    $rq = "UPDATE host SET ";
    $rq .= "host_first_notification_delay = ";
    isset($ret) && $ret != null ? $rq .= "'" . $ret . "' " : $rq .= "NULL ";
    $rq .= "WHERE host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
}

/**
 * For massive change. incremental mode
 */
function updateHostNotifOptionFirstNotificationDelay_MC($host_id = null)
{
    if (!$host_id) {
        return;
    }

    global $form;
    global $pearDB;

    $ret = $form->getSubmitValue("host_first_notification_delay");

    if (isset($ret) && $ret != null) {
        $rq = "UPDATE host SET ";
        $rq .= "host_first_notification_delay = '" . $ret . "' ";
        $rq .= "WHERE host_id = '" . $host_id . "'";
        $dbResult = $pearDB->query($rq);
    }
}


function updateHostNotifOptionRecoveryNotificationDelay($host_id = null, $ret = array())
{
    if (!$host_id) {
        return;
    }
    global $form;
    global $pearDB;

    if (isset($ret["host_recovery_notification_delay"])) {
        $ret = $ret["host_recovery_notification_delay"];
    } else {
        $ret = $form->getSubmitValue("host_recovery_notification_delay");
    }

    if ($ret == '') {
        return;
    }
    $rq = "UPDATE host SET ";
    $rq .= "host_recovery_notification_delay = ";
    isset($ret) && $ret != null ? $rq .= "'" . $ret . "' " : $rq .= "NULL ";
    $rq .= "WHERE host_id = '" . $host_id . "'";
    $pearDB->query($rq);
}




function updateHostHostGroup($host_id, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    /* Special Case, delete relation between host/service, when service is linked
     * to hostgroup in escalation, dependencies.
     * Get initial Hostgroup list to make a diff after deletion
     */
    $rq = "SELECT hostgroup_hg_id FROM hostgroup_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
    $hgsOLD = array();
    while ($hg = $dbResult->fetch()) {
        $hgsOLD[$hg["hostgroup_hg_id"]] = $hg["hostgroup_hg_id"];
    }

    // Get service lists linked to hostgroup
    $hgSVS = array();
    foreach ($hgsOLD as $hg) {
        $rq = "SELECT service_service_id FROM host_service_relation ";
        $rq .= "WHERE hostgroup_hg_id = '" . $hg . "' AND host_host_id IS NULL";
        $dbResult = $pearDB->query($rq);
        while ($sv = $dbResult->fetch()) {
            $hgSVS[$hg][$sv["service_service_id"]] = $sv["service_service_id"];
        }
    }

    $rq = "DELETE FROM hostgroup_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
    isset($ret["host_hgs"]) ? $ret = $ret["host_hgs"] : $ret = $form->getSubmitValue("host_hgs");
    $hgsNEW = array();

    if ($ret) {
        for ($i = 0; $i < count($ret); $i++) {
            $rq = "INSERT INTO hostgroup_relation ";
            $rq .= "(hostgroup_hg_id, host_host_id) ";
            $rq .= "VALUES ";
            $rq .= "('" . $ret[$i] . "', '" . $host_id . "')";
            $dbResult = $pearDB->query($rq);
            $hgsNEW[$ret[$i]] = $ret[$i];
        }
    }

    // Special Case, delete relation between host/service,
    // when service is linked to hostgroup in escalation, dependencies
    if (count($hgSVS)) {
        foreach ($hgsOLD as $hg) {
            if (!isset($hgsNEW[$hg])) {
                if (isset($hgSVS[$hg])) {
                    foreach ($hgSVS[$hg] as $sv) {
                        // Delete in escalation
                        $rq = "DELETE FROM escalation_service_relation ";
                        $rq .= "WHERE host_host_id = '" . $host_id . "' AND service_service_id = '" . $sv . "'";
                        $dbResult = $pearDB->query($rq);
                        // Delete in dependencies
                        $rq = "DELETE FROM dependency_serviceChild_relation ";
                        $rq .= "WHERE host_host_id = '" . $host_id . "' AND service_service_id = '" . $sv . "'";
                        $dbResult = $pearDB->query($rq);
                        $rq = "DELETE FROM dependency_serviceParent_relation ";
                        $rq .= "WHERE host_host_id = '" . $host_id . "' AND service_service_id = '" . $sv . "'";
                        $dbResult = $pearDB->query($rq);
                    }
                }
            }
        }
    }
    #
}

/**
 * For massive change. We just add the new list if the elem doesn't exist yet
 */
function updateHostHostGroup_MC($host_id, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "SELECT * FROM hostgroup_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
    $hgs = array();
    while ($arr = $dbResult->fetch()) {
        $hgs[$arr["hostgroup_hg_id"]] = $arr["hostgroup_hg_id"];
    }

    $ret = $form->getSubmitValue("host_hgs");
    if (is_array($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($hgs[$ret[$i]])) {
                $rq = "INSERT INTO hostgroup_relation ";
                $rq .= "(hostgroup_hg_id, host_host_id) ";
                $rq .= "VALUES ";
                $rq .= "('" . $ret[$i] . "', '" . $host_id . "')";
                $dbResult = $pearDB->query($rq);
            }
        }
    }
}

function updateHostHostCategory($host_id, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "DELETE FROM hostcategories_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "' ";
    $rq .= "AND NOT EXISTS(
                            SELECT hc_id 
                            FROM hostcategories hc 
                            WHERE hc.hc_id = hostcategories_relation.hostcategories_hc_id
                            AND hc.level IS NOT NULL) ";
    $dbResult = $pearDB->query($rq);

    $ret = isset($ret["host_hcs"]) ? $ret["host_hcs"] : $ret = $form->getSubmitValue("host_hcs");
    $hcsNEW = array();

    if (!$ret) {
        return;
    }

    for ($i = 0; $i < count($ret); $i++) {
        $rq = "INSERT INTO hostcategories_relation ";
        $rq .= "(hostcategories_hc_id, host_host_id) ";
        $rq .= "VALUES ";
        $rq .= "('" . $ret[$i] . "', '" . $host_id . "')";
        $dbResult = $pearDB->query($rq);
        $hcsNEW[$ret[$i]] = $ret[$i];
    }
}

/**
 * For massive change. We just add the new list if the elem doesn't exist yet
 */
function updateHostHostCategory_MC($host_id, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    $rq = "SELECT * FROM hostcategories_relation ";
    $rq .= "WHERE host_host_id = '" . $host_id . "'";
    $dbResult = $pearDB->query($rq);
    $hcs = array();
    while ($arr = $dbResult->fetch()) {
        $hcs[$arr["hostcategories_hc_id"]] = $arr["hostcategories_hc_id"];
    }
    $ret = $form->getSubmitValue("host_hcs");
    if (is_array($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($hcs[$ret[$i]])) {
                $rq = "INSERT INTO hostcategories_relation ";
                $rq .= "(hostcategories_hc_id, host_host_id) ";
                $rq .= "VALUES ";
                $rq .= "('" . $ret[$i] . "', '" . $host_id . "')";
                $dbResult = $pearDB->query($rq);
            }
        }
    }
}

function generateHostServiceMultiTemplate($hID, $hID2 = null, $antiLoop = null)
{
    global $pearDB, $path, $centreon;

    if (isset($antiLoop[$hID2]) && $antiLoop[$hID2]) {
        return 0;
    }

    require_once $path . "../service/DB-Func.php";

    $dbResult = $pearDB->query("SELECT host_tpl_id 
                                FROM `host_template_relation` 
                                WHERE host_host_id = " . $hID2 . " 
                                ORDER BY `order`");
    while ($hTpl = $dbResult->fetch()) {
        $rq2 = "SELECT service_service_id, service_register 
                FROM `host_service_relation`, service 
                WHERE service_service_id = service_id 
                AND host_host_id = '" . $hTpl['host_tpl_id'] . "'";
        $dbResult2 = $pearDB->query($rq2);
        while ($hTpl2 = $dbResult2->fetch()) {
            $alias = getMyServiceAlias($hTpl2["service_service_id"]);

            $service_sgs = array();
            $dbResult3 = $pearDB->query("SELECT DISTINCT servicegroup_sg_id 
                                        FROM servicegroup_relation 
                                        WHERE service_service_id = '" . $hTpl2["service_service_id"] . "'");
            for ($i = 0; $sg = $dbResult3->fetch(); $i++) {
                $service_sgs[$i] = $sg["servicegroup_sg_id"];
            }
            $dbResult3->closeCursor();

            if (testServiceExistence($alias, array(0 => $hID))) {
                $service = array(
                    "service_template_model_stm_id" => $hTpl2["service_service_id"],
                    "service_description" => $alias,
                    "service_register" => ($hTpl2["service_register"] + 1),
                    "service_activate" => array("service_activate" => 1),
                    "service_hPars" => array("0" => $hID),
                    "service_sgs" => $service_sgs
                );
                $service_id = insertServiceInDB($service, array());
            }
        }
        $antiLoop[$hID2] = 1;
        generateHostServiceMultiTemplate($hID, $hTpl['host_tpl_id'], $antiLoop);
    }
}

function createHostTemplateService($host_id = null, $htm_id = null)
{
    global $pearDB, $path, $centreon, $form;

    if (!$host_id) {
        return;
    }

    /*
     * If we select a host template model,
     * we create the services linked to this host template model
     */
    $ret = $form->getSubmitValues();
    if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"]) {
        generateHostServiceMultiTemplate($host_id, $host_id);
    }
}

function updateHostTemplateService($host_id = null)
{
    global $form, $pearDB, $centreon, $path;

    if (!$host_id) {
        return;
    }

    $dbResult = $pearDB->query("SELECT host_register FROM host WHERE host_id = '" . $host_id . "'");
    $row = $dbResult->fetch();
    if ($row["host_register"] == 0) {
        $rq = "DELETE FROM host_service_relation ";
        $rq .= "WHERE host_host_id = '" . $host_id . "'";
        $dbResult2 = $pearDB->query($rq);
        $ret = array();
        $ret = $form->getSubmitValue("host_svTpls");
        if ($ret) {
            for ($i = 0; $i < count($ret); $i++) {
                if (isset($ret[$i]) && $ret[$i] != "") {
                    $rq = "INSERT INTO host_service_relation ";
                    $rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
                    $rq .= "VALUES ";
                    $rq .= "(NULL, '" . $host_id . "', NULL, '" . $ret[$i] . "')";
                    $dbResult2 = $pearDB->query($rq);
                }
            }
        }
    } elseif ($centreon->user->get_version() >= 3) {
        if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"]) {
            generateHostServiceMultiTemplate($host_id, $host_id);
        }
    }
}

function updateHostTemplateService_MC($host_id = null)
{
    global $form, $pearDB, $centreon, $path;

    if (!$host_id) {
        return;
    }

    $dbResult = $pearDB->query("SELECT host_register FROM host WHERE host_id = '" . (int)$host_id . "'");
    $row = $dbResult->fetch();
    if ($row["host_register"] == 0) {
        $dbResult2 = $pearDB->query("SELECT * 
                                      FROM host_service_relation 
                                      WHERE host_host_id = '" . (int)$host_id . "'");
        $svtpls = array();
        while ($arr = $dbResult2->fetch()) {
            $svtpls [$arr["service_service_id"]] = $arr["service_service_id"];
        }

        $ret = $form->getSubmitValue("host_svTpls");
        for ($i = 0; $i < count($ret); $i++) {
            if (!isset($svtpls[$ret[$i]])) {
                $rq = "INSERT INTO host_service_relation ";
                $rq .= "(hostgroup_hg_id, host_host_id, servicegroup_sg_id, service_service_id) ";
                $rq .= "VALUES ";
                $rq .= "(NULL, '" . (int)$host_id . "', NULL, '" . $ret[$i] . "')";
                $dbResult2 = $pearDB->query($rq);
            }
        }
    } elseif ($centreon->user->get_version() >= 3) {
        if (isset($ret["dupSvTplAssoc"]["dupSvTplAssoc"]) && $ret["dupSvTplAssoc"]["dupSvTplAssoc"]) {
            generateHostServiceMultiTemplate($host_id, $host_id);
        }
    }
}

function updateHostTemplateUsed($useTpls = array())
{
    global $pearDB;

    if (!count($useTpls)) {
        return;
    }

    require_once "./include/common/common-Func.php";

    foreach ($useTpls as $key => $value) {
        $dbResult = $pearDB->query("UPDATE host 
                                    SET host_template_model_htm_id = '" . getMyHostID($value) . "' 
                                    WHERE host_id = '" . $key . "'");
    }
}

/**
 *
 */
function updateNagiosServerRelation($host_id, $ret = array())
{
    global $form, $pearDB;

    if (!$host_id) {
        return;
    }

    isset($ret["nagios_server_id"])
        ? $ret = $ret["nagios_server_id"]
        : $ret = $form->getSubmitValue("nagios_server_id");

    if (isset($ret) && $ret != "" && $ret != 0) {
        $dbResult = $pearDB->query("DELETE FROM `ns_host_relation` WHERE `host_host_id` = '" . (int)$host_id . "'");

        $rq = "INSERT INTO `ns_host_relation` ";
        $rq .= "(`host_host_id`, `nagios_server_id`) ";
        $rq .= "VALUES ";
        $rq .= "('" . (int)$host_id . "', '" . $ret . "')";

        $dbResult = $pearDB->query($rq);
    }
}

/**
 * Inserts criticality relations
 *
 * @param int $hostId
 * @param int $criticalityId
 * @return void
 */
function setHostCriticality($hostId, $criticalityId)
{
    global $pearDB;

    $pearDB->query("DELETE FROM hostcategories_relation  
                WHERE host_host_id = " . $pearDB->escape($hostId) . "
                AND NOT EXISTS(
                    SELECT hc_id 
                    FROM hostcategories hc 
                    WHERE hc.hc_id = hostcategories_relation.hostcategories_hc_id
                    AND hc.level IS NULL)");
    if ($criticalityId) {
        $pearDB->query("INSERT INTO hostcategories_relation (hostcategories_hc_id, host_host_id)
                                VALUES (" . $pearDB->escape($criticalityId) . ", " . $pearDB->escape($hostId) . ")");
    }
}

/**
 * Rule for test if a ldap contactgroup name already exists
 *
 * @param array $listCgs The list of contactgroups to validate
 * @return boolean
 */
function testCg($list)
{
    return CentreonContactgroup::verifiedExists($list);
}


/**
 * Apply template in order to deploy services
 *
 * @param array $hosts
 * @return void
 */
function applytpl($hosts)
{
    global $pearDB;

    $hostObj = new CentreonHost($pearDB);

    foreach ($hosts as $key => $value) {
        $hostObj->deployServices($key);
    }
}

/**
 * Sanitize all the host parameters from the host form and return a ready to bind array.
 *
 * @param array $ret
 * @return array
 */
function sanitizeFormHostParameters(array $ret): array
{
    $bindParams = [];
    foreach ($ret as $inputName => $inputValue) {
        switch ($inputName) {
            case 'host_template_model_htm_id':
            case 'command_command_id':
            case 'timeperiod_tp_id':
            case 'timeperiod_tp_id2':
            case 'command_command_id2':
            case 'host_max_check_attempts':
            case 'host_check_interval':
            case 'host_retry_check_interval':
            case 'host_freshness_threshold':
            case 'host_low_flap_threshold':
            case 'host_high_flap_threshold':
            case 'host_notification_interval':
            case 'host_first_notification_delay':
            case 'host_recovery_notification_delay':
            case 'host_location':
            case 'host_acknowledgement_timeout':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_INT => (filter_var($inputValue, FILTER_VALIDATE_INT) === false)
                        ? null
                        : (int) $inputValue
                ];
                break;
            case 'command_command_id_arg1':
            case 'command_command_id_arg2':
            case 'host_name':
            case 'host_alias':
            case 'host_address':
            case 'host_snmp_community':
            case 'host_snmp_version':
            case 'host_comment':
            case 'geo_coords':
                if (!empty($inputValue)) {
                    $bindParams[':' . $inputName] = [
                        \PDO::PARAM_STR => (($inputValue = filter_var($inputValue, FILTER_SANITIZE_STRING)) === '')
                            ? null
                            : $inputValue
                    ];
                }
                break;
            case 'host_active_checks_enabled':
            case 'host_passive_checks_enabled':
            case 'host_checks_enabled':
            case 'host_obsess_over_host':
            case 'host_check_freshness':
            case 'host_event_handler_enabled':
            case 'host_flap_detection_enabled':
            case 'host_retain_status_information':
            case 'host_retain_nonstatus_information':
            case 'host_notifications_enabled':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR => in_array($inputValue[$inputName], ['0', '1', '2'])
                        ? $inputValue[$inputName]
                        : '2'
                ];
                break;
            case 'host_notifOpts':
                if (!empty($inputValue)) {
                    $bindParams[':host_notification_options'] = [
                        \PDO::PARAM_STR => (($inputValue = filter_var(
                            implode(",", array_keys($inputValue)),
                            FILTER_SANITIZE_STRING
                        )) === '')
                        ? null
                        : $inputValue
                    ];
                }
                break;
            case 'contact_additive_inheritance':
            case 'cg_additive_inheritance':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_INT => (isset($ret[$inputName]) ? 1 : 0)
                ];
                break;
            case 'mc_contact_additive_inheritance':
            case 'mc_cg_additive_inheritance':
                $bindParams[':' . ltrim($inputName, 'mc_')] = [
                    \PDO::PARAM_INT => (isset($ret[$inputName]) ? 1 : 0)
                ];
                break;
            case 'host_stalOpts':
                if (!empty($inputValue)) {
                    $bindParams[':host_stalking_options'] = [
                        \PDO::PARAM_STR => (($inputValue = filter_var(
                            implode(",", array_keys($inputValue)),
                            FILTER_SANITIZE_STRING
                        )) === '')
                        ? null
                        : $inputValue
                    ];
                }
                break;
            case 'host_register':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR => in_array($inputValue, ['0', '1', '2', '3'])
                        ? $inputValue
                        : null
                ];
                break;
            case 'host_activate':
                $bindParams[':' . $inputName] = [
                    \PDO::PARAM_STR => in_array($inputValue[$inputName], ['0', '1', '2'])
                        ? $inputValue[$inputName]
                        : null
                ];
                break;
        }
    }
    return $bindParams;
}
