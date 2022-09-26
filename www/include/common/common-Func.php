<?php

/*
 * Copyright 2005-2015 Centreon
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
 * Displays the SVG file in HTML.
 *
 * @param string $svgPath SVG file path from _CENTREON_PATH_
 * @param string $color Color to be used in SVG image
 * @param float $height Height of image
 * @param float $width Width of image
 * @example <?php displaySvg('www/img/centreon.svg', '#000', 50, 50); ?>
 */
function displaySvg(string $svgPath, string $color, float $height, float $width): void
{
    $path = pathinfo($svgPath);
    $svgPath = str_replace('.', '', $path['dirname']) . DIRECTORY_SEPARATOR . $path['basename'];
    $path = _CENTREON_PATH_ . DIRECTORY_SEPARATOR . $svgPath;
    if (file_exists($path)) {
        $data = file_get_contents($path);
        $data = str_replace('<svg ', "<svg height='$height' width='$width' ", $data);
        echo "<span style='fill:$color; vertical-align: middle'>" . $data . '</span>';
    } else {
        echo 'SVG file not found: ' . $svgPath;
    }
}

/**
 * Return the SVG file.
 *
 * @param string $svgPath SVG file path from _CENTREON_PATH_
 * @param string $color Color to be used in SVG image
 * @param float $height Height of image
 * @param float $width Width of image
 * @example <?php returnSvg('www/img/centreon.svg', '#000', 50, 50); ?>
 */
function returnSvg(string $svgPath, string $color, float $height, float $width): string
{
    $path = pathinfo($svgPath);
    $svgPath = str_replace('.', '', $path['dirname']) . DIRECTORY_SEPARATOR . $path['basename'];
    $path = _CENTREON_PATH_ . DIRECTORY_SEPARATOR . $svgPath;
    if (file_exists($path)) {
        $data = file_get_contents($path);
        $data = str_replace('<svg ', "<svg height='$height' width='$width' ", $data);
        return "<span style='fill:$color ; vertical-align: middle'>" . $data . '</span>';
    } else {
        return 'SVG file not found: ' . $svgPath;
    }
}

/*
 * Form Rules
 */
function slash($elem = null)
{
    if ($elem) {
        return rtrim($elem, "/") . "/";
    }
}

/*
 * function table_not_exists()
 * - This function test if a table exist in database.
 *
 * @param	string	$table_name (the name of the table to test)
 * @return	int		0 			(return 0 if the table exists)
 */

function isUserAdmin($sid = null)
{
    global $pearDB;
    if (!isset($sid)) {
        return;
    }


    $DBRESULT = $pearDB->query("SELECT contact_admin, contact_id FROM session, contact
WHERE session.session_id = ? AND contact.contact_id = session.user_id", CentreonDB::escape($sid));
    $admin = $DBRESULT->fetchRow();
    $DBRESULT->closeCursor();

    if ($admin["contact_admin"]) {
        return 1;
    } else {
        return 0;
    }
}


function myDecode($data)
{
    if (is_string($data)) {
        $data = html_entity_decode($data, ENT_QUOTES, "UTF-8");
    }
    return $data;
}

function myEncode($data)
{
    if (is_string($data)) {
        $data = htmlentities($data);
    }
    return $data;
}


function getStatusColor($pearDB)
{
    $colors = array();
    $DBRESULT = $pearDB->query("SELECT * FROM `options` WHERE `key` LIKE 'color%'");
    while ($c = $DBRESULT->fetchRow()) {
        $colors[$c["key"]] = myDecode($c["value"]);
    }
    $DBRESULT->closeCursor();
    return $colors;
}

function tidySearchKey($search, $advanced_search)
{
    if ($advanced_search == 1) {
        if (isset($search) && !strstr($search, "*") && !strstr($search, "%")) {
            $search = "'" . $search . "'";
        } elseif (
            isset($search) &&
            isset($search[0]) &&
            isset($search[strlen($search) - 1]) &&
            $search[0] == "%" &&
            $search[strlen($search) - 1] == "%"
        ) {
            $search = str_replace("%", "", $search);
        } elseif (strpos($search, "%")) {
            $search = str_replace("%", "*", $search);
        }
    }
    return $search;
}

#
## SMARTY

#

/**
 * Allows to load Smarty's configuration in relation to a path
 *
 * @param {string} [$path=null] Path to the default template directory
 * @param {object} [$tpl=null] A Smarty instance
 * @param {string} [$subDir=null] A subdirectory of path
 *
 * @return {empty|object} A Smarty instance with configuration parameters
 */
function initSmartyTpl($path = null, &$tpl = null, $subDir = null)
{
    $tpl = new \SmartyBC();

    $tpl->setTemplateDir($path . $subDir);
    $tpl->setCompileDir(__DIR__ . '/../../../GPL_LIB/SmartyCache/compile');
    $tpl->setConfigDir(__DIR__ . '/../../../GPL_LIB/SmartyCache/config');
    $tpl->setCacheDir(__DIR__ . '/../../../GPL_LIB/SmartyCache/cache');
    $tpl->addPluginsDir(__DIR__ . '/../../../GPL_LIB/smarty-plugins');
    $tpl->loadPlugin('smarty_function_eval');
    $tpl->setForceCompile(true);
    $tpl->setAutoLiteral(false);
    $tpl->allow_ambiguous_resources = true;

    return $tpl;
}

/**
 * This function is mainly used in widgets
 */
function initSmartyTplForPopup($path = null, $tpl = null, $subDir = null, $centreonPath = null)
{
    return initSmartyTpl($path, $tpl, $subDir);
}

/*
 * FORM VALIDATION
 */

function myTrim($str)
{
    global $form;
    $str = rtrim($str, '\\');
    return (trim($str));
}

/**
 * @param string $value
 * @return string
 */
function limitNotesLength(string $value): string
{
    return substr($value, 0, 512);
}

/**
 * @param string $value
 * @return string
 */
function limitUrlLength(string $value): string
{
    return substr($value, 0, 2048);
}


function getMyHostName($host_id = null)
{
    global $pearDB;

    if (!$host_id) {
        return;
    }
    $query = "SELECT host_name FROM host WHERE host_id = :host_id LIMIT 1";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':host_id', (int) $host_id, \PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetchRow();
    if ($row["host_name"]) {
        return $row["host_name"];
    }
}


function getMyHostField($host_id, $field)
{
    if (!$host_id) {
        return;
    }
    global $pearDB;

    $query = "SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = :host_id ORDER BY `order` ASC";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':host_id', (int) $host_id, \PDO::PARAM_INT);
    $statement->execute();
    $hostStatement = $pearDB->prepare("SELECT `" . $field . "` FROM host WHERE host_id = :host_tpl_id");
    while ($row = $statement->fetchRow()) {
        $hostStatement->bindValue(':host_tpl_id', (int) $row['host_tpl_id'], \PDO::PARAM_INT);
        $hostStatement->execute();
        while ($row2 = $hostStatement->fetchRow()) {
            if (isset($row2[$field]) && $row2[$field]) {
                return $row2[$field];
            }
            if ($tmp = getMyHostField($row['host_tpl_id'], $field)) {
                return $tmp;
            }
        }
    }
    return null;
}

/*
 *  This functions is called recursively until it finds an ehi field
 */

function getMyHostExtendedInfoFieldFromMultiTemplates($host_id, $field)
{
    if (!$host_id) {
        return null;
    }
    global $pearDB;
    $rq = "SELECT host_tpl_id " .
        "FROM host_template_relation " .
        "WHERE host_host_id = :host_host_id " .
        "ORDER BY `order`";
    $statement = $pearDB->prepare($rq);
    $statement->bindValue(':host_host_id', (int)$host_id, \PDO::PARAM_INT);
    $statement->execute();
    $rq2 = "SELECT ehi.`" . $field . "` " .
        " FROM extended_host_information ehi " .
        "WHERE ehi.host_host_id = :host_host_id LIMIT 1";
    $statement2 = $pearDB->prepare($rq2);
    while ($row = $statement->fetchRow()) {
        $statement2->bindValue(":host_host_id", (int)$row['host_tpl_id'], \PDO::PARAM_INT);
        $statement2->execute();

        $row2 = $statement2->fetchRow();
        if (isset($row2[$field]) && $row2[$field]) {
            return $row2[$field];
        } else {
            if ($result_field = getMyHostExtendedInfoFieldFromMultiTemplates($row['host_tpl_id'], $field)) {
                return $result_field;
            }
        }
    }
    return null;
}

function getMyHostMacroFromMultiTemplates($host_id, $field)
{
    if (!$host_id) {
        return null;
    }
    global $pearDB;
    $statement = $pearDB->prepare("SELECT host_tpl_id " .
        "FROM host_template_relation " .
        "WHERE host_host_id = :host_host_id " .
        "ORDER BY `order`");
    $statement->bindValue(':host_host_id', (int)$host_id, \PDO::PARAM_INT);
    $statement->execute();
    $statement2 = $pearDB->prepare("SELECT macro.host_macro_value " .
        "FROM on_demand_macro_host macro " .
        "WHERE macro.host_host_id = :host_host_id  AND macro.host_macro_name = :field LIMIT 1");
    while ($row = $statement->fetchRow()) {
        $statement2->bindValue(':host_host_id', (int) $row["host_tpl_id"], \PDO::PARAM_INT);
        $statement2->bindValue(':field', "\$_HOST" . $field . "\$", \PDO::PARAM_STR);
        $statement2->execute();
        $row2 = $statement2->fetchRow();
        if (isset($row2["host_macro_value"]) && $row2["host_macro_value"]) {
            $macroValue = str_replace("#S#", "/", $row2["host_macro_value"]);
            $macroValue = str_replace("#BS#", "\\", $macroValue);
            return $macroValue;
        } else {
            if ($result_field = getMyHostMacroFromMultiTemplates($row['host_tpl_id'], $field)) {
                $macroValue = str_replace("#S#", "/", $result_field);
                $macroValue = str_replace("#BS#", "\\", $macroValue);
                return $macroValue;
            }
        }
    }
    return null;
}

function getMyServiceCategories($service_id = null)
{
    global $pearDB, $oreon;
    if (!$service_id) {
        return;
    }
    $tab = array();
    while (1) {
        $statement = $pearDB->prepare("SELECT sc.sc_id FROM service_categories_relation scr, service_categories sc " .
            "WHERE scr.service_service_id = :service_id AND sc.sc_id = scr.sc_id AND sc.sc_activate = '1'");
        $statement->bindValue(':service_id', (int) $service_id, \PDO::PARAM_INT);
        $statement->execute();
        if ($statement->rowCount()) {
            $tabSC = array();
            while ($row = $statement->fetchRow()) {
                $tabSC[$row["sc_id"]] = $row["sc_id"];
            }
            return $tabSC;
        } else {
            $statement = $pearDB->prepare("SELECT service_template_model_stm_id " .
                " FROM service WHERE service_id = :service_id");
            $statement->bindValue(':service_id', (int)$service_id, \PDO::PARAM_INT);
            $statement->execute();
            $row = $statement->fetchRow();
            if ($row["service_template_model_stm_id"]) {
                if (isset($tab[$row['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                return array();
            }
        }
    }
}

function getMyCategorieName($sc_id = null)
{
    if (!$sc_id) {
        return;
    }
    global $pearDB, $oreon;
    $statement = $pearDB->prepare("SELECT sc_name FROM service_categories WHERE sc_id = :sc_id");
    $statement->bindValue(':sc_id', (int) $sc_id, \PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetchRow();
    return $row["sc_name"];
}

function getMyServiceMacro($service_id, $field)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $query = "SELECT macro.svc_macro_value " .
        "FROM on_demand_macro_service macro " .
        "WHERE macro.svc_svc_id = :svc_svc_id 
        AND macro.svc_macro_name = :svc_macro_name LIMIT 1";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':svc_svc_id', (int) $service_id, \PDO::PARAM_INT);
    $statement->bindValue(':svc_macro_name', '$_SERVICE' . $field . '$', \PDO::PARAM_STR);
    $statement->execute();
    $row = $statement->fetch(\PDO::FETCH_ASSOC);
    if (isset($row["svc_macro_value"]) && $row["svc_macro_value"]) {
        $macroValue = str_replace("#S#", "/", $row['svc_macro_value']);
        $macroValue = str_replace("#BS#", "\\", $macroValue);
        return $macroValue;
    } else {
        $service_id = getMyServiceField($service_id, "service_template_model_stm_id");
        return getMyServiceMacro($service_id, $field);
    }
}

function getMyHostExtendedInfoField($host_id, $field)
{
    if (!$host_id) {
        return;
    }
    global $pearDB, $oreon;

    $rq = "SELECT ehi.`" . $field . "` " .
        "FROM extended_host_information ehi " .
        "WHERE ehi.host_host_id = :host_id LIMIT 1";
    $statement = $pearDB->prepare($rq);
    $statement->bindValue(':host_id', (int) $host_id, \PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetchRow();
    if (isset($row[$field]) && $row[$field]) {
        return $row[$field];
    } else {
        return getMyHostExtendedInfoFieldFromMultiTemplates($host_id, $field);
    }
}

function getMyHostExtendedInfoImage($host_id, $field, $flag1stLevel = null, $antiLoop = null)
{
    global $pearDB, $oreon;

    if (!$host_id) {
        return;
    }

    if (isset($flag1stLevel) && $flag1stLevel) {
        $rq = "SELECT ehi.`" . $field . "` " .
            "FROM extended_host_information ehi " .
            "WHERE ehi.host_host_id = :host_host_id LIMIT 1";
        $statement = $pearDB->prepare($rq);
        $statement->bindValue(':host_host_id', (int) $host_id, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if (isset($row[$field]) && $row[$field]) {
            $query = "SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr " .
                "WHERE vi.img_id = :img_id 
                AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1";
            $statement = $pearDB->prepare($query);
            $statement->bindValue(':img_id', (int) $row[$field], \PDO::PARAM_INT);
            $statement->execute();
            $row2 = $statement->fetch(\PDO::FETCH_ASSOC);
            if (isset($row2["dir_alias"]) && isset($row2["img_path"]) && $row2["dir_alias"] && $row2["img_path"]) {
                return $row2["dir_alias"] . "/" . $row2["img_path"];
            }
        } else {
            if ($result_field = getMyHostExtendedInfoImage($host_id, $field)) {
                return $result_field;
            }
        }
        return null;
    } else {
        $rq = "SELECT host_tpl_id " .
            "FROM host_template_relation " .
            "WHERE host_host_id = :host_host_id " .
            "ORDER BY `order`";
        $htStatement = $pearDB->prepare($rq);
        $htStatement->bindValue(':host_host_id', (int) $host_id, \PDO::PARAM_INT);
        $htStatement->execute();
        $rq2 = "SELECT `" . $field . "` " .
               "FROM extended_host_information ehi " .
               "WHERE ehi.host_host_id = :host_host_id LIMIT 1";
        $ehiStatement = $pearDB->prepare($rq2);
        $query = "SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr " .
                 "WHERE vi.img_id = :img_id 
                 AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1";
        $imgStatement = $pearDB->prepare($query);
        while ($row = $htStatement->fetch(\PDO::FETCH_ASSOC)) {
            $ehiStatement->bindValue(':host_host_id', (int) $row['host_tpl_id'], \PDO::PARAM_INT);
            $ehiStatement->execute();
            $row2 = $ehiStatement->fetch(\PDO::FETCH_ASSOC);
            if (isset($row2[$field]) && $row2[$field]) {
                $imgStatement->bindValue(':img_id', (int) $row2[$field], \PDO::PARAM_INT);
                $imgStatement->execute();
                $row3 = $imgStatement->fetch(\PDO::FETCH_ASSOC);
                if (isset($row3["dir_alias"]) && isset($row3["img_path"]) && $row3["dir_alias"] && $row3["img_path"]) {
                    return $row3["dir_alias"] . "/" . $row3["img_path"];
                }
            } else {
                if (isset($antiLoop) && $antiLoop) {
                    if ($antiLoop != $row['host_tpl_id']) {
                        if ($result_field = getMyHostExtendedInfoImage($row['host_tpl_id'], $field, null, $antiLoop)) {
                            return $result_field;
                        }
                    }
                } else {
                    if (
                        $result_field = getMyHostExtendedInfoImage(
                            $row['host_tpl_id'],
                            $field,
                            null,
                            $row['host_tpl_id']
                        )
                    ) {
                        return $result_field;
                    }
                }
            }
        }
        return null;
    }
}

function getMyHostMultipleTemplateModels($host_id = null)
{
    if (!$host_id) {
        return [];
    }

    global $pearDB;
    $tplArr = array();
    $query = "SELECT host_tpl_id FROM `host_template_relation` WHERE host_host_id = :host_id ORDER BY `order`";
    $statement0 = $pearDB->prepare($query);
    $statement0->bindValue(':host_id', (int)$host_id, \PDO::PARAM_INT);
    $statement0->execute();
    $statement = $pearDB->prepare("SELECT host_name FROM host WHERE host_id = :host_id LIMIT 1");
    while ($row = $statement0->fetchRow()) {
        $statement->bindValue(':host_id', (int)$row['host_tpl_id'], \PDO::PARAM_INT);
        $statement->execute();
        $hTpl = $statement->fetch(\PDO::FETCH_ASSOC);
        $tplArr[$row['host_tpl_id']] = html_entity_decode($hTpl["host_name"], ENT_QUOTES, "UTF-8");
    }
    return $tplArr;
}

#
## HOST GROUP

#

function getMyHostGroupName($hg_id = null)
{
    if (!$hg_id) {
        return;
    }
    global $pearDB;

    $query = "SELECT hg_name FROM hostgroup WHERE hg_id = :hg_id LIMIT 1";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':hg_id', (int) $hg_id, \PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetchRow();
    if ($row["hg_name"]) {
        return html_entity_decode($row["hg_name"], ENT_QUOTES, "UTF-8");
    }
    return null;
}

/* * ******************************
 * Get all host for a specific hostgroup
 *
 * @var hostgroup id
 * @var search string
 *
 * @return list of host
 */

function getMyHostGroupHosts($hg_id = null, $searchHost = null, $level = 1)
{
    global $pearDB;

    if (!$hg_id) {
        return;
    }

    $searchSTR = "";
    if (isset($searchHost) && $searchHost != "") {
        $searchSTR = " AND h.host_name LIKE '%" . CentreonDB::escape($searchHost) . "%' ";
    }
    $hosts = array();
    $statement = $pearDB->prepare("SELECT hgr.host_host_id " .
        "FROM hostgroup_relation hgr, host h " .
        "WHERE hgr.hostgroup_hg_id = :hostgroup_hg_id " .
        "AND h.host_id = hgr.host_host_id $searchSTR " .
        "ORDER by h.host_name");
    $statement->bindValue(':hostgroup_hg_id', (int) $hg_id, \PDO::PARAM_INT);
    $statement->execute();
    while ($elem = $statement->fetch(\PDO::FETCH_ASSOC)) {
        $hosts[$elem["host_host_id"]] = $elem["host_host_id"];
    }
    $statement->closeCursor();
    unset($elem);

    if ($level) {
        $hgHgCache = setHgHgCache($pearDB);
        $hostgroups = getMyHostGroupHostGroups($hg_id);
        if (isset($hostgroups) && count($hostgroups)) {
            foreach ($hostgroups as $hg_id2) {
                $tmp = getMyHostGroupHosts($hg_id2, "", 1);
                foreach ($tmp as $id) {
                    $hosts[$id] = $id;
                }
                unset($tmp);
            }
        }
    }

    return $hosts;
}

function setHgHgCache($pearDB)
{
    $hgHgCache = array();
    $DBRESULT = $pearDB->query("SELECT /* SQL_CACHE */ hg_parent_id, hg_child_id FROM hostgroup_hg_relation");
    while ($data = $DBRESULT->fetchRow()) {
        if (!isset($hgHgCache[$data["hg_parent_id"]])) {
            $hgHgCache[$data["hg_parent_id"]] = array();
        }
        $hgHgCache[$data["hg_parent_id"]][$data["hg_child_id"]] = 1;
    }
    $DBRESULT->closeCursor();
    unset($data);
    return $hgHgCache;
}

function getMyHostGroupHostGroups($hg_id = null)
{
    global $pearDB;

    if (!$hg_id) {
        return;
    }

    $hosts = array();

    $statement = $pearDB->prepare("SELECT hg_child_id " .
        "FROM hostgroup_hg_relation, hostgroup " .
        "WHERE hostgroup_hg_relation.hg_parent_id = :hg_parent_id " .
        "AND hostgroup.hg_id = hostgroup_hg_relation.hg_child_id " .
        "ORDER BY hostgroup.hg_name");
    $statement->bindValue(':hg_parent_id', (int) $hg_id, \PDO::PARAM_INT);
    $statement->execute();
    while ($elem = $statement->fetch(\PDO::FETCH_ASSOC)) {
        $hosts[$elem["hg_child_id"]] = $elem["hg_child_id"];
    }
    $statement->closeCursor();
    unset($elem);
    return $hosts;
}

#
## SERVICE GROUP

#
function getMyServiceGroupServices($sg_id = null)
{
    global $pearDB;
    if (!$sg_id) {
        return;
    }
    /*
     * ServiceGroups by host
     */
    $svs = array();
    $statement = $pearDB->prepare("SELECT service_description, service_id, host_host_id, host_name " .
        "FROM servicegroup_relation, service, host " .
        "WHERE servicegroup_sg_id = :sg_id " .
        "AND servicegroup_relation.servicegroup_sg_id = servicegroup_sg_id " .
        "AND service.service_id = servicegroup_relation.service_service_id " .
        "AND servicegroup_relation.host_host_id = host.host_id " .
        "AND servicegroup_relation.host_host_id IS NOT NULL");
    $statement->bindValue(':sg_id', (int)$sg_id, \PDO::PARAM_INT);
    $statement->execute();
    while ($elem = $statement->fetchRow()) {
        $svs[$elem["host_host_id"] . "_" . $elem["service_id"]] =
            db2str($elem["service_description"]) . ":::" . $elem["host_name"];
    }

    /*
     * ServiceGroups by hostGroups
     */
    $statement1 = $pearDB->prepare("SELECT service_description, service_id, hostgroup_hg_id, hg_name " .
        "FROM servicegroup_relation, service, hostgroup " .
        "WHERE servicegroup_sg_id = :sg_id " .
        "AND servicegroup_relation.servicegroup_sg_id = servicegroup_sg_id " .
        "AND service.service_id = servicegroup_relation.service_service_id " .
        "AND servicegroup_relation.hostgroup_hg_id = hostgroup.hg_id " .
        "AND servicegroup_relation.hostgroup_hg_id IS NOT NULL");
    $statement1->bindValue(':sg_id', (int)$sg_id, \PDO::PARAM_INT);
    $statement1->execute();
    while ($elem = $statement1->fetchRow()) {
        $hosts = getMyHostGroupHosts($elem["hostgroup_hg_id"]);
        foreach ($hosts as $key => $value) {
            $svs[$key . "_" . $elem["service_id"]] = db2str($elem["service_description"]) . ":::" . $value;
        }
    }
    $statement1->closeCursor();
    return $svs;
}

#
## SERVICE

#

function getMyServiceField($service_id, $field)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $tab = array();

    while (1) {
        $statement = $pearDB->prepare("SELECT `" . $field . "` , service_template_model_stm_id " .
            "FROM service WHERE service_id = :service_id LIMIT 1");
        $statement->bindValue(':service_id', (int) $service_id, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetchRow();
        if ($row[$field]) {
            return $row[$field];
        } elseif ($row["service_template_model_stm_id"]) {
            if (isset($tab[$row['service_template_model_stm_id']])) {
                break;
            }
            $service_id = $row["service_template_model_stm_id"];
            $tab[$service_id] = 1;
        } else {
            break;
        }
    }
}

function getMyServiceExtendedInfoField($service_id, $field)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;

    $tab = array();
    while (1) {
        $statement = $pearDB->prepare("SELECT `extended_service_information`.`" . $field . "` ," .
            " `service`.`service_template_model_stm_id` " .
            "FROM `service`, `extended_service_information` " .
            "WHERE `extended_service_information`.`service_service_id` =:service_id " .
            "AND `service`.`service_id` = :service_id LIMIT 1");

        $statement->bindValue(':service_id', (int) $service_id, \PDO::PARAM_INT);
        $statement->execute();

        if ($row = $statement->fetch()) {
            if ($row[$field]) {
                return $row[$field];
            } elseif ($row["service_template_model_stm_id"]) {
                if (isset($tab[$row['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                break;
            }
        } else {
            break;
        }
    }
}

function getMyServiceName($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $tab = array();

    while (1) {
        $statement = $pearDB->prepare("SELECT service_description, service_template_model_stm_id FROM service " .
            " WHERE service_id = :service_id LIMIT 1");
        $statement->bindValue(':service_id', (int) $service_id, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetchRow();
        if ($row["service_description"]) {
            return html_entity_decode(db2str($row["service_description"]), ENT_QUOTES, "UTF-8");
        } elseif ($row["service_template_model_stm_id"]) {
            if (isset($tab[$row['service_template_model_stm_id']])) {
                break;
            }
            $service_id = $row["service_template_model_stm_id"];
            $tab[$service_id] = 1;
        } else {
            break;
        }
    }
}

function getMyServiceAlias($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;

    $tab = array();

    while (1) {
        $statement = $pearDB->prepare("SELECT service_alias, service_template_model_stm_id FROM service " .
            "WHERE service_id = :service_id LIMIT 1");
        $statement->bindValue(':service_id', (int) $service_id, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetchRow();
        if ($row["service_alias"]) {
            return html_entity_decode(db2str($row["service_alias"]), ENT_QUOTES, "UTF-8");
        } elseif ($row["service_template_model_stm_id"]) {
            if (isset($tab[$row['service_template_model_stm_id']])) {
                break;
            }
            $service_id = $row["service_template_model_stm_id"];
            $tab[$service_id] = 1;
        } else {
            break;
        }
    }
}

function getMyServiceGraphID($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;

    $tab = array();
    while (1) {
        $statement = $pearDB->prepare("SELECT esi.graph_id, service_template_model_stm_id" .
            " FROM service, extended_service_information esi " .
            "WHERE service_id = :service_id AND esi.service_service_id = service_id LIMIT 1");
        $statement->bindValue(':service_id', (int) $service_id, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetchRow();
        if ($row["graph_id"]) {
            return $row["graph_id"];
        } elseif ($row["service_template_model_stm_id"]) {
            if (isset($tab[$row['service_template_model_stm_id']])) {
                break;
            }
            $service_id = $row["service_template_model_stm_id"];
            $tab[$service_id] = 1;
        } else {
            break;
        }
    }
    return null;
}

function getMyServiceIDStorage($service_description, $host_id)
{
    $dbb = new CentreonDB("centstorage");
    $statement = $dbb->prepare("SELECT s.service_id FROM services s " .
        " WHERE (s.description = :service_description
                        OR s.description = :utf8_uncoded_service_description ) "
        . " AND s.host_id = :host_id LIMIT 1");
    $statement->bindValue(':service_description', $service_description, \PDO::PARAM_STR);
    $statement->bindValue(':utf8_uncoded_service_description', utf8_encode($service_description), \PDO::PARAM_STR);
    $statement->bindValue(':host_id', (int) $host_id, \PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetchRow();
    if ($row["service_id"]) {
        return $row["service_id"];
    }
}


function getMyServiceID($service_description = null, $host_id = null, $hg_id = null)
{
    if (!$service_description && (!$host_id || !$hg_id)) {
        return;
    }
    global $pearDB;

    $service_description = str2db($service_description);
    if ($host_id) {
        $statement = $pearDB->prepare("SELECT service_id FROM service, host_service_relation hsr " .
            "WHERE hsr.host_host_id = :host_host_id AND hsr.service_service_id = service_id " .
            "AND (service_description = :service_description OR" .
            " service_description = :utf8_encoded_service_description ) LIMIT 1");
        $statement->bindValue(':host_host_id', (int) $host_id, \PDO::PARAM_INT);
        $statement->bindValue(':utf8_encoded_service_description', utf8_encode($service_description), \PDO::PARAM_STR);
        $statement->bindValue(':service_description', $service_description, \PDO::PARAM_STR);
        $statement->execute();
        $row = $statement->fetchRow();
        # Service is directely link to a host, no problem
        if ($row["service_id"]) {
            return $row["service_id"];
        }
        # The Service might be link with a HostGroup
        $statement1 = $pearDB->prepare("SELECT service_id " .
            " FROM hostgroup_relation hgr, service, host_service_relation hsr" .
            " WHERE hgr.host_host_id = :host_host_id AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
            " AND service_id = hsr.service_service_id AND service_description = :service_description");

        $statement1->bindValue(':host_host_id', (int)$host_id, \PDO::PARAM_INT);
        $statement1->bindValue(':service_description', $service_description, \PDO::PARAM_STR);
        $statement1->execute();
        $row = $statement1->fetchRow();
        if ($row["service_id"]) {
            return $row["service_id"];
        }
    }
    if ($hg_id) {
        $statement2 = $pearDB->prepare("SELECT service_id FROM service, host_service_relation hsr " .
            "WHERE hsr.hostgroup_hg_id = :hostgroup_hg_id AND hsr.service_service_id = service_id " .
            "AND service_description = :service_description LIMIT 1");

        $statement2->bindValue(':hostgroup_hg_id', (int)$hg_id, \PDO::PARAM_INT);
        $statement2->bindValue(':service_description', $service_description, \PDO::PARAM_STR);
        $statement2->execute();

        $row = $statement2->fetchRow();
        if ($row["service_id"]) {
            return $row["service_id"];
        }
    }
    return null;
}

function getMyHostServices($host_id = null, $search = 0)
{
    global $pearDB;

    if (!$host_id) {
        return;
    }

    $hSvs = array();

    /*
     * Get Services attached to hosts
     */
    if ($search !== 0) {
        $statement = $pearDB->prepare("SELECT service_id, service_description" .
            " FROM service, host_service_relation hsr " .
            "WHERE hsr.host_host_id = :host_id AND hsr.service_service_id = service_id " .
            "AND service_description LIKE :service_description ");
        $statement->bindValue(':host_id', (int)$host_id, \PDO::PARAM_INT);
        $statement->bindValue(':service_description', "'%" . $search . "%'", \PDO::PARAM_STR);
        $statement->execute();
    } else {
        $statement = $pearDB->prepare("SELECT service_id, service_description " .
            "FROM service, host_service_relation hsr " .
            "WHERE hsr.host_host_id = :host_id AND hsr.service_service_id = service_id");
        $statement->bindValue(':host_id', (int)$host_id, \PDO::PARAM_INT);
        $statement->execute();
    }
    while ($elem = $statement->fetchRow()) {
        $hSvs[$elem["service_id"]] = html_entity_decode(db2str($elem["service_description"]), ENT_QUOTES, "UTF-8");
    }
    $statement->closeCursor();

    /*
     * Get Services attached to hostgroups
     */
    $statement1 = $pearDB->prepare("SELECT service_id, service_description " .
        "FROM hostgroup_relation hgr, service, host_service_relation hsr" .
        " WHERE hgr.host_host_id = :host_host_id" .
        " AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
        " AND service_id = hsr.service_service_id");
    $statement1->bindValue(':host_host_id', (int)$host_id, \PDO::PARAM_INT);
    $statement1->execute();
    while ($elem = $statement1->fetchRow()) {
        $hSvs[$elem["service_id"]] = html_entity_decode(db2str($elem["service_description"]), ENT_QUOTES, "UTF-8");
    }
    $statement1->closeCursor();
    asort($hSvs);
    return $hSvs;
}

function getMyHostServicesByName($host_id = null)
{
    if (!$host_id) {
        return;
    }
    global $pearDB;
    $hSvs = array();
    $statement = $pearDB->prepare("SELECT service_id, service_description FROM service, host_service_relation hsr " .
        "WHERE hsr.host_host_id =  :host_host_id " .
        "AND hsr.service_service_id = service_id");
    $statement->bindValue(':host_host_id', (int)$host_id, \PDO::PARAM_INT);
    $statement->execute();
    while ($elem = $statement->fetchRow()) {
        $hSvs[db2str($elem["service_description"])] = html_entity_decode($elem["service_id"], ENT_QUOTES, "UTF-8");
    }
    $statement->closeCursor();
    $statement = $pearDB->prepare("SELECT service_id, service_description " .
        "FROM hostgroup_relation hgr, service, host_service_relation hsr" .
        " WHERE hgr.host_host_id = :host_host_id " .
        " AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
        " AND service_id = hsr.service_service_id");
    $statement->bindValue(':host_host_id', (int)$host_id, \PDO::PARAM_INT);
    $statement->execute();

    while ($elem = $statement->fetchRow()) {
        $hSvs[db2str($elem["service_description"])] = html_entity_decode($elem["service_id"], ENT_QUOTES, "UTF-8");
    }
    $statement->closeCursor();
    return $hSvs;
}

function getMyServiceHosts($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $hosts = array();
    $statement = $pearDB->prepare("SELECT DISTINCT host_host_id FROM host_service_relation hsr " .
        "WHERE hsr.service_service_id = :service_service_id ");
    $statement->bindValue(':service_service_id', (int)$service_id, \PDO::PARAM_INT);
    $statement->execute();

    while ($elem = $statement->fetchRow()) {
        if ($elem["host_host_id"]) {
            $hosts[$elem["host_host_id"]] = $elem["host_host_id"];
        }
    }
    $statement->closeCursor();
    return $hosts;
}

function getMyServiceHostGroups($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $hgs = array();
    $statement = $pearDB->prepare("SELECT DISTINCT hostgroup_hg_id FROM host_service_relation hsr " .
        "WHERE hsr.service_service_id =  :service_service_id ");
    $statement->bindValue(':service_service_id', (int)$service_id, \PDO::PARAM_INT);
    $statement->execute();

    while ($elem = $statement->fetchRow()) {
        if ($elem["hostgroup_hg_id"]) {
            $hgs[$elem["hostgroup_hg_id"]] = $elem["hostgroup_hg_id"];
        }
    }
    $statement->closeCursor();
    return $hgs;
}

function getMyServiceTPLID($service_description = null)
{
    if (!$service_description) {
        return;
    }
    global $pearDB;
    $statement = $pearDB->prepare("SELECT service_id FROM service " .
        "WHERE service_description = :srv_desc AND service_register = '0' LIMIT 1");
    $statement->bindValue(':srv_desc', htmlentities(str2db($service_description), ENT_QUOTES, "UTF-8"));
    $statement->execute();
    $row = $statement->fetchRow();
    if ($row["service_id"]) {
        return $row["service_id"];
    }
    return null;
}

function getMyServiceTemplateModels($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $tplArr = array();
    while (1) {
        $statement = $pearDB->prepare("SELECT service_description, service_template_model_stm_id FROM service " .
            "WHERE service_id = :service_id LIMIT 1");
        $statement->bindValue(':service_id', (int)$service_id, \PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetchRow();
        if ($row["service_description"]) {
            $tplArr[$service_id] = html_entity_decode(db2str($row["service_description"]), ENT_QUOTES, "UTF-8");
        } else {
            break;
        }
        if ($row["service_template_model_stm_id"]) {
            if (isset($tplArr[$row['service_template_model_stm_id']])) {
                break;
            }
            $service_id = $row["service_template_model_stm_id"];
        } else {
            break;
        }
    }
    return ($tplArr);
}

#
## COMMAND

#

function getMyCheckCmdName($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;

    $tab = array();

    $query = "SELECT command_name FROM command WHERE command_id = :command_id LIMIT 1";
    $statement = $pearDB->prepare($query);
    while (1) {
        $statement2 = $pearDB->prepare("SELECT command_command_id, service_template_model_stm_id FROM service " .
            "WHERE service_id = :service_id LIMIT 1");
        $statement2->bindValue(':service_id', (int) $service_id, \PDO::PARAM_INT);
        $statement2->execute();
        $row = $statement2->fetchRow();
        if ($row["command_command_id"]) {
            $statement->bindValue(':command_id', (int) $row["command_command_id"], \PDO::PARAM_INT);
            $statement->execute();
            $row2 = $statement->fetch(\PDO::FETCH_ASSOC);
            return ($row2["command_name"]);
        } elseif ($row["service_template_model_stm_id"]) {
            if (isset($tab[$row['service_template_model_stm_id']])) {
                break;
            }
            $service_id = $row["service_template_model_stm_id"];
            $tab[$service_id] = 1;
        } else {
            return null;
        }
    }
    return null;
}

function getMyCheckCmdArg($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $tab = array();

    while (1) {
        $query = "SELECT command_command_id_arg, service_template_model_stm_id FROM service " .
            "WHERE service_id = :service_id LIMIT 1";
        $statement = $pearDB->prepare($query);
        $statement->bindValue(':service_id', (int) $service_id, \PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetchRow();
        if ($row["command_command_id_arg"]) {
            return (db2str($row["command_command_id_arg"]));
        } elseif ($row["service_template_model_stm_id"]) {
            if (isset($tab[$row['service_template_model_stm_id']])) {
                break;
            }
            $service_id = $row["service_template_model_stm_id"];
            $tab[$service_id] = 1;
        } else {
            return null;
        }
    }
    return null;
}

/**
 *
 * @param $service_id
 * @return unknown_type
 */
#
## Upload conf needs

#

function getMyHostID($host_name = null)
{
    if (!$host_name) {
        return;
    }
    global $pearDB;
    $statement = $pearDB->prepare("SELECT host_id FROM host WHERE host_name = :host_name " .
        "OR host_name = :ut8_encoded_host_name LIMIT 1");
    $statement->bindValue(':ut8_encoded_host_name', utf8_encode($host_name), \PDO::PARAM_STR);
    $statement->bindValue('host_name', $host_name, \PDO::PARAM_STR);
    $statement->execute();
    if ($statement->rowCount()) {
        $row = $statement->fetchRow();
        return $row["host_id"];
    }
    return null;
}

#
## GRAPHS

#

# Nagios Images

function return_image_list($mode = 0, $rep = null, $full = true, $origin_path = null)
{
    global $pearDB;

    $images = array();

    if ($full) {
        $images = array(null => null);
    }

    $is_not_an_image = array(".", "..", "README", "readme", "LICENCE", "licence");
    $is_a_valid_image = array(
        0 => array('png' => 'png'),
        1 => array('gif' => 'gif', 'png' => 'png', 'jpg' => 'jpg'),
        2 => array('gif' => 'gif', 'png' => 'png', 'jpg' => 'jpg', 'gd2' => 'gd2')
    );

    $query = "SELECT img_id, img_name, img_path, dir_name FROM view_img_dir, view_img, view_img_dir_relation vidr " .
        "WHERE img_id = vidr.img_img_id AND dir_id = vidr.dir_dir_parent_id ORDER BY dir_name, img_name";
    $DBRESULT = $pearDB->query($query);
    $dir_name = null;
    $dir_name2 = null;
    $cpt = 1;
    while ($elem = $DBRESULT->fetchRow()) {
        $dir_name = $elem["dir_name"];
        if ($dir_name2 != $dir_name) {
            $dir_name2 = $dir_name;
            $images["REP_" . $cpt] = $dir_name;
            $cpt++;
        }
        $ext = null;
        $pinfo = pathinfo($elem["img_path"]);
        if (isset($pinfo["extension"]) && isset($is_a_valid_image[$mode][$pinfo["extension"]])) {
            $ext = "&nbsp;(" . $pinfo["extension"] . ")";
        }
        $images[$elem["img_id"]] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
            html_entity_decode($elem["img_name"], ENT_NOQUOTES) . $ext;
    }
    return ($images);
}

function getLangs()
{
    $langs = array('browser' => _("Detection by browser"));
    $chemintotal = "./locale/";

    if (is_dir($chemintotal)) {
        if ($handle = opendir($chemintotal)) {
            while ($file = readdir($handle)) {
                if (is_dir("$chemintotal/$file") && strcmp($file, ".") && strcmp($file, "..")) {
                    $langTitle = str_replace('.UTF-8', '', $file);
                    $langs[$file] = $langTitle;
                }
            }
            closedir($handle);
        }
    }
    if (!array_key_exists('en_US.UTF-8', $langs)) {
        $langs["en_US.UTF-8"] = "en_US";
    }

    return $langs;
}

function service_has_graph($host, $service, $dbo = null)
{
    global $pearDBO;
    if (is_null($dbo)) {
        $dbo = $pearDBO;
    }
    if (is_numeric($host) && is_numeric($service)) {
        $statement = $pearDBO->prepare("SELECT i.* FROM index_data i, metrics m WHERE i.id = m.index_id " .
            "AND i.host_id = :host_id  AND i.service_id = :service_id");
        $statement->bindValue(':host_id', (int)$host, \PDO::PARAM_INT);
        $statement->bindValue(':service_id', (int)$service, \PDO::PARAM_INT);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            return true;
        }
    }
    if (!is_numeric($host) && !is_numeric($service)) {
        $statement = $pearDBO->prepare("SELECT i.* FROM index_data i, metrics m WHERE i.id = m.index_id " .
            "AND i.host_name = :host_name AND i.service_description = :service_description");
        $statement->bindValue(':host_name', $host, \PDO::PARAM_STR);
        $statement->bindValue(':service_description', $service, \PDO::PARAM_STR);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            return true;
        }
    }
    return false;
}

/**
 * Send a well formatted error.
 *
 * @param string $message Message to send
 * @param int $code HTTP error code
 * @param string $type Response type (json by default)
 */
function sendError(string $message, int $code = 500, string $type = 'json')
{
    switch ($type) {
        case 'xml':
            header('Content-Type: text/xml');
            echo '<message>' . $message . '</message>';
            break;
        case 'json':
        default:
            header('Content-Type: application/json');
            echo json_encode(['message' => $message]);
            break;
    }
    switch ($code) {
        case 401:
            header("HTTP/1.0 401 Unauthorized");
            break;
        case 500:
        default:
            header("HTTP/1.0 500 Internal Server Error");
    }
    exit();
}

/* Ajax tests */

function get_error($motif)
{
    $buffer = null;
    $buffer .= '<reponse>';
    $buffer .= $motif;
    $buffer .= '</reponse>';
    header('Content-Type: text/xml');
    echo $buffer;
    exit(0);
}

/* End Ajax Test */
function GetMyHostPoller($pearDB, $host_name = null)
{
    if (!isset($host_name)) {
        return 0;
    }
     $statement = $pearDB->prepare("SELECT `id` FROM nagios_server, ns_host_relation, host " .
        "WHERE host.host_name = :host_name AND host.host_id = ns_host_relation.host_host_id " .
        "AND ns_host_relation.nagios_server_id = nagios_server.id LIMIT 1");
    $statement->bindValue(':host_name', $host_name, \PDO::PARAM_STR);
    $statement->execute();

    $nagios_server = $statement->fetchRow();
    if (isset($nagios_server['id'])) {
        return $nagios_server['id'];
    }
    $sql = "SELECT id FROM nagios_server WHERE localhost = '1' LIMIT 1";
    $res = $pearDB->query($sql);
    if ($res->rowCount()) {
        $row = $res->fetchRow();
        return $row['id'];
    }
    return 0;
}

function check_session($sid, $pearDB)
{
    if (isset($sid)) {
        $statement = $pearDB->prepare("SELECT * FROM session WHERE session_id = :sid");
        $statement->bindValue(':sid', htmlentities($sid, ENT_QUOTES, "UTF-8"));
        $statement->execute();
        if ($session = $statement->fetchRow()) {
            return $session["user_id"];
        } else {
            get_error('bad session id');
        }
    } else {
        get_error('need session identifiant !');
    }
    return 0;
}


function db2str($string)
{
    $string = str_replace('#BR#', "\\n", $string);
    $string = str_replace('#T#', "\\t", $string);
    $string = str_replace('#R#', "\\r", $string);
    $string = str_replace('#S#', "/", $string);
    $string = str_replace('#BS#', "\\", $string);
    return $string;
}

function str2db($string)
{
    /* $string = str_replace("\\n", '#BR#', $string);
      $string = str_replace("\\t", '#T#', $string);
      $string = str_replace("\\r", '#R#', $string);
      $string = str_replace("/", '#S#', $string);
      $string = str_replace("\\", '#BS#', $string); */
    return $string;
}

/**
 * Return the list of template
 *
 * @param int $svcId The service ID
 * @return array
 */
function getListTemplates($pearDB, $svcId, $alreadyProcessed = array())
{
    $svcTmpl = array();
    if (in_array($svcId, $alreadyProcessed)) {
        return $svcTmpl;
    } else {
        $alreadyProcessed[] = $svcId;
        $statement = $pearDB->prepare("SELECT * FROM service WHERE service_id = :service_id");
        $statement->bindValue(':service_id', (int)$svcId, \PDO::PARAM_INT);
        $statement->execute();

        if ($row = $statement->fetch()) {
            if ($row['service_template_model_stm_id'] !== null) {
                $svcTmpl = array_merge(
                    $svcTmpl,
                    getListTemplates($pearDB, $row['service_template_model_stm_id'], $alreadyProcessed)
                );
            }
            $svcTmpl[] = $row;
        }
        return $svcTmpl;
    }
}

if (!function_exists("array_column")) {
    function array_column($array, $column_name)
    {
        return array_map(function ($element) use ($column_name) {
            return $element[$column_name];
        }, $array);
    }

}


// Global Function

/**
 * get first menu entry allowed to a given user
 *
 * @param string $lcaTStr Allowed topology pages separated by comma
 * @param int $defaultPage User default page
 * @return array The topology information (url, options, name...)
 */
function getFirstAllowedMenu($lcaTStr, $defaultPage)
{
    global $pearDB;

    $defaultPageOptions = null;

    // manage default page with option (eg: 50110&o=general)
    if (preg_match('/(\d+)(\D+)/', $defaultPage, $matches)) {
        $defaultPage = $matches[1];
        $defaultPageOptions = $matches[2];
    }

    $statement = $pearDB->prepare(
        "SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt, is_react "
        . "FROM topology "
        . "WHERE " . (trim($lcaTStr) != "" ? "topology_page IN ({$lcaTStr}) AND " : "")
        . "topology_page IS NOT NULL AND topology_show = '1' AND topology_url IS NOT NULL "
        . "ORDER BY FIELD(topology_page, :default_page) DESC, "
        . "FIELD(topology_url_opt, :default_page_options) DESC, "
        . "topology_page ASC, topology_id ASC "
        . "LIMIT 1"
    );

    $statement->bindValue(':default_page', (int)$defaultPage, \PDO::PARAM_INT);
    $statement->bindValue(':default_page_options', $defaultPageOptions, \PDO::PARAM_STR);

    $statement->execute();

    if (!$statement->rowCount()) {
        return [];
    }

    return $statement->fetch();
}

function reset_search_page($url)
{
    # Clean Vars
    global $centreon;
    if (!isset($url)) {
        return;
    }
    if (
        isset($_GET['search'])
        && isset($centreon->historySearch[$url])
        && $_GET['search'] != $centreon->historySearch[$url]
        && !isset($_GET['num'])
        && !isset($_POST['num'])
    ) {
        $_POST['num'] = 0;
        $_GET['num'] = 0;
    } elseif (
        isset($_GET["search"])
        && isset($_POST["search"])
        && $_GET["search"] === $_POST["search"]
    ) {
        //if the user change the search filter, we reset the num argument sent in the hybride POST and GET request
        $_POST['num'] = $_GET['num'] = 0;
    }
}

function get_child($id_page, $lcaTStr)
{
    global $pearDB;

    if ($lcaTStr != "") {
        $rq = " SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt, is_react
                FROM topology
                WHERE topology_page IN ($lcaTStr)
                AND topology_parent = '" . $id_page . "' AND topology_page IS NOT NULL AND topology_show = '1'
                ORDER BY topology_order, topology_group ";
    } else {
        $rq = " SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt, is_react
                FROM topology
                WHERE topology_parent = '" . $id_page . "' AND topology_page IS NOT NULL AND topology_show = '1'
                ORDER BY topology_order, topology_group ";
    }

    $DBRESULT = $pearDB->query($rq);
    $redirect = $DBRESULT->fetch();
    return $redirect;
}

/**
 * Quickform rule that validate geo_coords
 *
 * @return bool
 * @throws HTML_QuickForm_Error
 */
function validateGeoCoords()
{
    global $form;
    $coords = $form->getElementValue('geo_coords');
    if (
        preg_match(
            '/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/',
            $coords
        )
    ) {
        return true;
    }
    return false;
}

/**
 * Get the select option.
 *
 * @return array<int, int>
 */
function getSelectOption()
{
    $stringToArray = function (string $value): array {
        if (strpos($value, ',') !== false) {
            $value = explode(',', rtrim($value, ','));
            return array_flip($value);
        }
        return [$value => '1'];
    };
    if (isset($_GET["select"])) {
        return is_array($_GET["select"])
            ? $_GET["select"]
            : $stringToArray($_GET["select"]);
    } elseif (isset($_POST["select"])) {
        return is_array($_POST["select"])
            ? $_POST["select"]
            : $stringToArray($_POST["select"]);
    } else {
        return [];
    }
}


/**
 * Get the duplicate number option.
 *
 * @return array<int, int>
 */
function getDuplicateNumberOption()
{
    if (isset($_GET["dupNbr"])) {
        return is_array($_GET["dupNbr"])
            ? $_GET["dupNbr"]
            : [];
    } elseif (isset($_POST["dupNbr"])) {
        return is_array($_POST["dupNbr"])
            ? $_POST["dupNbr"]
            : [];
    } else {
        return [];
    }
}

function isNotEmptyAfterStringSanitize($test): bool
{
    if (empty(\HtmlAnalyzer::sanitizeAndRemoveTags($test))) {
        return false;
    } else {
        return true;
    }
}

/**
 * Create a CSRF token
 *
 * @return string
 */
function createCSRFToken(): string
{
    $token = bin2hex(openssl_random_pseudo_bytes(16));

    if (!isset($_SESSION['x-centreon-token']) || !is_array($_SESSION['x-centreon-token'])) {
        $_SESSION['x-centreon-token'] = [];
        $_SESSION['x-centreon-token-generated-at'] = [];
    }

    $_SESSION['x-centreon-token'][] = $token;
    $_SESSION['x-centreon-token-generated-at'][(string)$token] = time();

    return $token;
}

/**
 * Remove CSRF tokens older than 15min form session
 */
function purgeOutdatedCSRFTokens()
{
    foreach ($_SESSION['x-centreon-token-generated-at'] as $key => $value) {
        $elapsedTime = time() - $value;

        if ($elapsedTime > (15 * 60)) {
            $tokenKey = array_search((string) $key, $_SESSION['x-centreon-token']);
            unset($_SESSION['x-centreon-token'][$tokenKey]);
            unset($_SESSION['x-centreon-token-generated-at'][(string) $key]);
        }
    }
}

/**
 * Remove CSRF Token from session
 */
function purgeCSRFToken()
{
    $token = $_POST['centreon_token'] ?? $_GET['centreon_token'] ?? null;

    $key = array_search((string) $token, $_SESSION['x-centreon-token']);
    unset($_SESSION['x-centreon-token'][$key]);
    unset($_SESSION['x-centreon-token-generated-at'][(string) $token]);
}

/**
 * Check CRSF token validity
 *
 * @return boolean
 */
function isCSRFTokenValid()
{
    $isValid = false;

    $token = $_POST['centreon_token'] ?? $_GET['centreon_token'] ?? null;
    if ($token !== null && in_array($token, $_SESSION['x-centreon-token'])) {
        $isValid = true;
    }

    return $isValid;
}

/**
 * Display error message for unvalid form (CSRF token unvalid or too old)
 */
function unvalidFormMessage()
{
    echo "<div class='msg' align='center'>" .
        _("The form has not been submitted since 15 minutes. Please retry to resubmit") .
        "</div>";
}

/**
 * Return ids of hosts linked to hostgroups
 *
 * @param int[] $hostgroupIds
 * @param bool $shouldHostgroupBeEnabled (default true)
 * @return int[]
 * @throws \Exception
 */
function findHostsForConfigChangeFlagFromHostGroupIds(array $hostgroupIds, bool $shouldHostgroupBeEnabled = true): array
{
    if (empty($hostgroupIds)) {
        return [];
    }

    global $pearDB;

    $bindedParams = [];
    foreach ($hostgroupIds as $key => $hostgroupId) {
        $bindedParams[':hostgroup_id_' . $key] = $hostgroupId;
    }

    if ($shouldHostgroupBeEnabled) {
        $query = "SELECT DISTINCT(hgr.host_host_id)
            FROM hostgroup_relation hgr
            JOIN hostgroup ON hostgroup.hg_id = hgr.hostgroup_hg_id
            WHERE hostgroup.hg_activate = '1'
            AND hgr.hostgroup_hg_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
    } else {
        $query = "SELECT DISTINCT(hgr.host_host_id) FROM hostgroup_relation hgr
            WHERE hgr.hostgroup_hg_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
    }

    $stmt = $pearDB->prepare($query);
    foreach ($bindedParams as $bindedParam => $bindedValue) {
        $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Return ids of hosts linked to services
 *
 * @param int[] $serviceIds
 * @param bool $shouldServiceBeEnabled (default true)
 * @return int[]
 * @throws \Exception
 */
function findHostsForConfigChangeFlagFromServiceIds(array $serviceIds, bool $shoudlServiceBeEnabled = true): array
{
    if (empty($serviceIds)) {
        return [];
    }

    global $pearDB;

    $bindedParams = [];
    foreach ($serviceIds as $key => $serviceId) {
        $bindedParams[':service_id_' . $key] = $serviceId;
    }

    if ($shoudlServiceBeEnabled) {
        $query = "SELECT DISTINCT(hsr.host_host_id)
            FROM host_service_relation hsr
            JOIN service ON service.service_id = hsr.service_service_id
            WHERE service.service_activate = '1' AND hsr.service_service_id IN ("
            . implode(', ', array_keys($bindedParams)) . ")";
    } else {
        $query = "SELECT DISTINCT(hsr.host_host_id)
            FROM host_service_relation hsr
            WHERE hsr.service_service_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
    }

    $stmt = $pearDB->prepare($query);
    foreach ($bindedParams as $bindedParam => $bindedValue) {
        $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Return ids of services linked to templates recursively
 *
 * @param int[] $serviceTemplateIds
 * @return int[]
 * @throws \Exception
 */
function findServicesForConfigChangeFlagFromServiceTemplateIds(array $serviceTemplateIds): array
{
    if (empty($serviceTemplateIds)) {
        return [];
    }

    global $pearDB;

    $bindedParams = [];
    foreach ($serviceTemplateIds as $key => $serviceTemplateId) {
        $bindedParams[':servicetemplate_id_' . $key] = $serviceTemplateId;
    }

    $query = "SELECT service_id, service_register FROM service
        WHERE service.service_activate = '1'
        AND service_template_model_stm_id IN (" . implode(', ', array_keys($bindedParams)) . ")";

    $stmt = $pearDB->prepare($query);
    foreach ($bindedParams as $bindedParam => $bindedValue) {
        $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
    }
    $stmt->execute();

    $serviceIds = [];
    $serviceTemplateIds2 = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
        if ($value['service_register'] === '0') {
            $serviceTemplateIds2[] = $value['service_id'];
        } else {
            $serviceIds[] = $value['service_id'];
        }
    }
    return array_merge(
        $serviceIds,
        findServicesForConfigChangeFlagFromServiceTemplateIds($serviceTemplateIds2)
    );
}

/**
 * Return ids of hosts linked to service
 *
 * @param int $servicegroupId
 * @param bool $shouldServicegroupBeEnabled (default true)
 * @return int[]
 * @throws \Exception
 */
function findHostsForConfigChangeFlagFromServiceGroupId(
    int $servicegroupId,
    bool $shouldServicegroupBeEnabled = true
): array {
    global $pearDB;

    $query = "SELECT sgr.*, service.service_register
        FROM servicegroup_relation sgr
        JOIN servicegroup ON servicegroup.sg_id = sgr.servicegroup_sg_id
        JOIN service ON service.service_id = sgr.service_service_id
        WHERE service.service_activate = '1' AND sgr.servicegroup_sg_id = :servicegroup_id"
        . ($shouldServicegroupBeEnabled ? " AND servicegroup.sg_activate = '1'" : "");

    $stmt = $pearDB->prepare($query);
    $stmt->bindValue(':servicegroup_id', $servicegroupId, \PDO::PARAM_INT);
    $stmt->execute();

    $hostIds = [];
    $hostgroupIds = [];
    $serviceTemplateIds = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
        if ($value['service_register'] === '0') {
            $serviceTemplateIds[] = $value['service_service_id'];
        } elseif ($value['hostgroup_hg_id'] !== null) {
            $hostgroupIds[] = $value['hostgroup_hg_id'];
        } else {
            $hostIds[] = $value['host_host_id'];
        }
    }

    $serviceIds = findServicesForConfigChangeFlagFromServiceTemplateIds($serviceTemplateIds);

    return array_merge(
        $hostIds,
        findHostsForConfigChangeFlagFromHostGroupIds($hostgroupIds),
        findHostsForConfigChangeFlagFromServiceIds($serviceIds)
    );
}

/**
 * Return ids of pollers linked to hosts
 *
 * @param int[] $hostIds
 * @param bool $shouldHostBeEnabled (default true)
 * @return int[]
 * @throws \Exception
 */
function findPollersForConfigChangeFlagFromHostIds(array $hostIds, bool $shouldHostBeEnabled = true): array
{
    if (empty($hostIds)) {
        return [];
    }

    global $pearDB;

    $bindedParams = [];
    foreach ($hostIds as $key => $hostId) {
        $bindedParams[':host_id_' . $key] = $hostId;
    }

    if ($shouldHostBeEnabled) {
        $query = "SELECT DISTINCT(phr.nagios_server_id)
        FROM ns_host_relation phr
        JOIN host ON host.host_id = phr.host_host_id
        WHERE host.host_activate = '1' AND phr.host_host_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
    } else {
        $query = "SELECT DISTINCT(phr.nagios_server_id) FROM ns_host_relation phr
           WHERE phr.host_host_id IN (" . implode(', ', array_keys($bindedParams)) . ")";
    }

    $stmt = $pearDB->prepare($query);
    foreach ($bindedParams as $bindedParam => $bindedValue) {
        $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Set 'updated' flag to '1' for all listed poller ids
 *
 * @param int[] $pollerIds
 * @throws \Exception
 */
function definePollersToUpdated(array $pollerIds): void
{
    if (empty($pollerIds)) {
        return;
    }

    global $pearDB;

    $bindedParams = [];
    foreach ($pollerIds as $key => $pollerId) {
        $bindedParams[':poller_id_' . $key] = $pollerId;
    }
    $query = "UPDATE nagios_server SET updated = '1' WHERE id IN (" . implode(', ', array_keys($bindedParams)) . ")";
    $stmt = $pearDB->prepare($query);
    foreach ($bindedParams as $bindedParam => $bindedValue) {
        $stmt->bindValue($bindedParam, $bindedValue, \PDO::PARAM_INT);
    }
    $stmt->execute();
}

/**
 * Get current Centreon version
 */
function getCentreonVersion($pearDB)
{
    $query = 'SELECT `value` FROM `informations` WHERE `key` = "version"';
    try {
        $res = $pearDB->query($query);
    } catch (PDOException $e) {
        return null;
    }
    $row = $res->fetchRow();
    return $row['value'];
}


/**
 * Set relevent pollers as updated
 *
 * @param string $resourceType
 * @param int $resourceId
 * @param int[] $previousPollers
 * @param bool $shouldResourceBeEnabled (default true)
 * @throws \Exception
 */
function signalConfigurationChange(
    string $resourceType,
    int $resourceId,
    array $previousPollers = [],
    bool $shouldResourceBeEnabled = true
): void {
    $hostIds = [];
    switch ($resourceType) {
        case 'host':
            $hostIds[] = $resourceId;
            break;
        case 'hostgroup':
            $hostIds = array_merge(
                $hostIds,
                findHostsForConfigChangeFlagFromHostGroupIds([$resourceId], $shouldResourceBeEnabled)
            );
            break;
        case 'service':
            $hostIds = array_merge(
                $hostIds,
                findHostsForConfigChangeFlagFromServiceIds([$resourceId], $shouldResourceBeEnabled)
            );
            break;
        case 'servicegroup':
            $hostIds = array_merge(
                $hostIds,
                findHostsForConfigChangeFlagFromServiceGroupId($resourceId, $shouldResourceBeEnabled)
            );
            break;
        default:
            throw new \Exception("Unknown resource type:" . $resourceType);
            break;
    }
    $pollerIds = findPollersForConfigChangeFlagFromHostIds(
        $hostIds,
        $resourceType === 'host' ? $shouldResourceBeEnabled : true
    );

    definePollersToUpdated(array_merge($pollerIds, $previousPollers));
}
