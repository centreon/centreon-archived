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

/*
 *
 *
 * <code>
 *
 * </code>
 *
 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
 * @return{TAB}int{TAB}Ma valeur de retour
 */

function getUserIdFromSID($sid = null)
{
    if (!isset($sid)) {
        return;
    }
    global $pearDB;
    $DBRESULT = $pearDB->query("SELECT contact_id FROM session, contact 
WHERE session.session_id = ? AND contact.contact_id = session.user_id", CentreonDB::escape($sid));
    $admin = $DBRESULT->fetchRow();
    unset($DBRESULT);
    if (isset($admin["contact_id"])) {
        return $admin["contact_id"];
    }
    return 0;
}

function table_not_exists($table_name)
{
    global $pearDBndo;

    $DBRESULT = $pearDBndo->query("SHOW TABLES LIKE '" . $table_name . "'");

    if ($DBRESULT->rowCount() > 0) {
        return 0;
    }
}

function myDecode($arg)
{
    return html_entity_decode($arg, ENT_QUOTES, "UTF-8");
}

/*
 * Decode outputting integer values
 */
function myDecodeToInteger($arg)
{
    return intval(html_entity_decode($arg, ENT_QUOTES, "UTF-8"));
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
        } elseif (isset($search) &&
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
function initSmartyTpl($path = null, $tpl = null, $subDir = null)
{
    if (!$tpl) {
        return;
    }
    $tpl->template_dir = $path . $subDir;
    $tpl->compile_dir = __DIR__ . "/../../../GPL_LIB/SmartyCache/compile";
    $tpl->config_dir = __DIR__ . "/../../../GPL_LIB/SmartyCache/config";
    $tpl->cache_dir = __DIR__ . "/../../../GPL_LIB/SmartyCache/cache";
    $tpl->plugins_dir[] = __DIR__ . "/../../../GPL_LIB/smarty-plugins";
    $tpl->caching = 0;
    $tpl->compile_check = true;
    $tpl->force_compile = true;
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

/*
 * Hosts Functions
 */

function getMyHostTemplateModel($host_id = null)
{
    global $pearDB;

    if (!$host_id) {
        return;
    }

    $query = "SELECT host_template_model_htm_id FROM host WHERE host_id = '" .
        CentreonDB::escape($host_id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    if ($row["host_template_model_htm_id"]) {
        return $row["host_template_model_htm_id"];
    } else {
        null;
    }
}

function getMyHostName($host_id = null)
{
    global $pearDB;

    if (!$host_id) {
        return;
    }
    $query = "SELECT host_name FROM host WHERE host_id = '" . CentreonDB::escape($host_id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    if ($row["host_name"]) {
        return $row["host_name"];
    }
}

function isAHostTpl($host_id = null)
{
    global $pearDB;

    if (!$host_id) {
        return;
    }
    $query = "SELECT host_register FROM host WHERE host_id = '" . CentreonDB::escape($host_id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    if ($row["host_register"] == 1) {
        return true;
    } else {
        return false;
    }
}

function getMyHostAddress($host_id = null)
{
    if (!$host_id) {
        return;
    }
    global $pearDB;
    while (1) {
        $query = "SELECT host_address, host_template_model_htm_id FROM host WHERE host_id = '" .
            CentreonDB::escape($host_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        if ($row["host_address"]) {
            return html_entity_decode($row["host_address"], ENT_QUOTES, "UTF-8");
        } elseif ($row["host_template_model_htm_id"]) {
            $host_id = $row["host_template_model_htm_id"];
        } else {
            break;
        }
    }
}

function getMyHostAddressByName($host_name = null)
{
    if (!$host_name) {
        return;
    }
    global $pearDB;
    while (1) {
        $query = "SELECT host_address, host_template_model_htm_id FROM host WHERE host_name = '" .
            CentreonDB::escape($host_name) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        if ($row["host_address"]) {
            return html_entity_decode($row["host_address"], ENT_QUOTES, "UTF-8");
        } elseif ($row["host_template_model_htm_id"]) {
            $host_id = $row["host_template_model_htm_id"];
        } else {
            break;
        }
    }
}

function getMyHostIDByAddress($host_address = null)
{
    if (!$host_address) {
        return;
    }
    global $pearDB;
    while (1) {
        $query = "SELECT host_id, host_address, host_template_model_htm_id FROM host " .
            "WHERE host_name = '" . CentreonDB::escape($host_address) . "' or host_address = '" .
            CentreonDB::escape($host_address) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        if ($row["host_id"]) {
            return html_entity_decode($row["host_id"], ENT_QUOTES, "UTF-8");
        } elseif ($row["host_template_model_htm_id"]) {
            $host_id = $row["host_template_model_htm_id"];
        } else {
            break;
        }
    }
}

function getMyHostParents($host_id = null)
{
    if (!$host_id) {
        return;
    }
    global $pearDB;
    while (1) {
        $query = "SELECT host_template_model_htm_id AS tpl FROM host WHERE host_id = '" .
            CentreonDB::escape($host_id) . "'";
        $DBRESULT = $pearDB->query($query);
        $host = clone($DBRESULT->fetch());
        $query = "SELECT hpr.host_parent_hp_id FROM host_hostparent_relation hpr " .
            "WHERE hpr.host_host_id = '" . CentreonDB::escape($host_id) . "'";
        $DBRESULT = $pearDB->query($query);
        if ($DBRESULT->fetchColumn()) {
            return $DBRESULT;
        } elseif (isset($host["tpl"]) && $host["tpl"]) {
            $host_id = $host["tpl"];
        } else {
            return $DBRESULT;
        }
    }
}

function getMyHostGroups($host_id = null)
{
    if (!$host_id) {
        return;
    }
    global $pearDB;
    $hgs = array();

    $query = "SELECT hg.hg_name, hgr.hostgroup_hg_id FROM hostgroup hg, hostgroup_relation hgr " .
        "WHERE hgr.host_host_id = '" . CentreonDB::escape($host_id) . "' AND hgr.hostgroup_hg_id = hg.hg_id";
    $DBRESULT = $pearDB->query($query);
    while ($hg = $DBRESULT->fetchRow()) {
        $hgs[$hg["hostgroup_hg_id"]] = html_entity_decode($hg["hg_name"], ENT_QUOTES, "UTF-8");
    }
    return $hgs;
}

function getMyHostField($host_id = null, $field)
{
    if (!$host_id) {
        return;
    }
    global $pearDB;

    $query = "SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = '" .
        CentreonDB::escape($host_id) . "' ORDER BY `order` ASC";
    $DBRESULT = $pearDB->query($query);
    while ($row = $DBRESULT->fetchRow()) {
        $DBRESULT2 = $pearDB->query("SELECT `" . $field . "` FROM host WHERE host_id = '" . $row['host_tpl_id'] . "'");
        while ($row2 = $DBRESULT2->fetchRow()) {
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

function getMyHostFieldOnHost($host_id = null, $field)
{
    global $pearDB;

    if (!$host_id) {
        return;
    }

    $query = "SELECT `" . $field . "` FROM host WHERE host_id = '" . CentreonDB::escape($host_id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    if (isset($row[$field]) && $row[$field]) {
        return $row[$field];
    } else {
        return 0;
    }
}

function getMyHostFieldFromMultiTemplates($host_id, $field)
{
    global $pearDB;
    if (!$host_id) {
        return null;
    }

    $rq = "SELECT host_tpl_id " .
        "FROM host_template_relation " .
        "WHERE host_host_id = '" . CentreonDB::escape($host_id) . "' " .
        "ORDER BY `order`";
    $DBRESULT = $pearDB->query($rq);
    while ($row = $DBRESULT->fetchRow()) {
        $rq2 = "SELECT $field " .
            "FROM host " .
            "WHERE host_id = '" . $row['host_tpl_id'] . "' LIMIT 1";
        $DBRESULT2 = $pearDB->query($rq2);
        $row2 = $DBRESULT2->fetchRow();
        if (isset($row2[$field]) && $row2[$field]) {
            return $row2[$field];
        } else {
            if ($result_field = getMyHostFieldFromMultiTemplates($row['host_tpl_id'], $field)) {
                return $result_field;
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
        "WHERE host_host_id = '" . CentreonDB::escape($host_id) . "' " .
        "ORDER BY `order`";
    $DBRESULT = $pearDB->query($rq);
    while ($row = $DBRESULT->fetchRow()) {
        $rq2 = "SELECT ehi.`" . $field . "` " .
            "FROM extended_host_information ehi " .
            "WHERE ehi.host_host_id = '" . $row['host_tpl_id'] . "' LIMIT 1";
        $DBRESULT2 = $pearDB->query($rq2);
        $row2 = $DBRESULT2->fetchRow();
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

    $rq = "SELECT host_tpl_id " .
        "FROM host_template_relation " .
        "WHERE host_host_id = '" . CentreonDB::escape($host_id) . "' " .
        "ORDER BY `order`";
    $DBRESULT = $pearDB->query($rq);
    while ($row = $DBRESULT->fetchRow()) {
        $rq2 = "SELECT macro.host_macro_value " .
            "FROM on_demand_macro_host macro " .
            "WHERE macro.host_host_id = '" . $row["host_tpl_id"] .
            "' AND macro.host_macro_name = '\$_HOST" . $field . "\$' LIMIT 1";
        $DBRESULT2 = $pearDB->query($rq2);
        $row2 = $DBRESULT2->fetchRow();
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

function getMyHostMacro($host_id = null, $field)
{
    if (!$host_id) {
        return;
    }
    global $pearDB, $oreon;

    $query = "SELECT macro.host_macro_value " .
        "FROM on_demand_macro_host macro " .
        "WHERE macro.host_host_id = '" . CentreonDB::escape($host_id) .
        "' AND macro.host_macro_name = '\$_HOST" . $field . "\$' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    if (isset($row["host_macro_value"]) && $row["host_macro_value"]) {
        $macroValue = str_replace("#S#", "/", $row["host_macro_value"]);
        $macroValue = str_replace("#BS#", "\\", $macroValue);
        return $macroValue;
    } else {
        return getMyHostMacroFromMultiTemplates($host_id, $field);
    }
}

function getMyServiceCategories($service_id = null)
{
    global $pearDB, $oreon;

    if (!$service_id) {
        return;
    }

    $tab = array();
    while (1) {
        $query = "SELECT sc.sc_id FROM service_categories_relation scr, service_categories sc " .
            "WHERE scr.service_service_id = '" . CentreonDB::escape($service_id) .
            "' AND sc.sc_id = scr.sc_id AND sc.sc_activate = '1'";
        $DBRESULT = $pearDB->query($query);
        if ($DBRESULT->rowCount()) {
            $tabSC = array();
            while ($row = $DBRESULT->fetchRow()) {
                $tabSC[$row["sc_id"]] = $row["sc_id"];
            }
            return $tabSC;
        } else {
            $query = "SELECT service_template_model_stm_id FROM service WHERE service_id = '" .
                CentreonDB::escape($service_id) . "'";
            $DBRESULT = $pearDB->query($query);
            $row = $DBRESULT->fetchRow();
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

    $query = "SELECT sc_name FROM service_categories WHERE sc_id = '" . CentreonDB::escape($sc_id) . "'";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    return $row["sc_name"];
}

function getMyServiceMacro($service_id = null, $field)
{
    if (!$service_id) {
        return;
    }
    global $pearDB, $oreon;

    $query = "SELECT macro.svc_macro_value " .
        "FROM on_demand_macro_service macro " .
        "WHERE macro.svc_svc_id = '" . CentreonDB::escape($service_id) .
        "' AND macro.svc_macro_name = '\$_SERVICE" . $field . "\$' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    if (isset($row["svc_macro_value"]) && $row["svc_macro_value"]) {
        $macroValue = str_replace("#S#", "/", $row['svc_macro_value']);
        $macroValue = str_replace("#BS#", "\\", $macroValue);
        return $macroValue;
    } else {
        $service_id = getMyServiceField($service_id, "service_template_model_stm_id");
        return getMyServiceMacro($service_id, $field);
    }
}

function getMyHostExtendedInfoField($host_id = null, $field)
{
    if (!$host_id) {
        return;
    }
    global $pearDB, $oreon;

    $rq = "SELECT ehi.`" . $field . "` " .
        "FROM extended_host_information ehi " .
        "WHERE ehi.host_host_id = '" . CentreonDB::escape($host_id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($rq);
    $row = $DBRESULT->fetchRow();
    if (isset($row[$field]) && $row[$field]) {
        return $row[$field];
    } else {
        return getMyHostExtendedInfoFieldFromMultiTemplates($host_id, $field);
    }
}

function getMyHostExtendedInfoImage($host_id = null, $field, $flag1stLevel = null, $antiLoop = null)
{
    global $pearDB, $oreon;

    if (!$host_id) {
        return;
    }

    if (isset($flag1stLevel) && $flag1stLevel) {
        $rq = "SELECT ehi.`" . $field . "` " .
            "FROM extended_host_information ehi " .
            "WHERE ehi.host_host_id = '" . CentreonDB::escape($host_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($rq);
        $row = $DBRESULT->fetchRow();
        if (isset($row[$field]) && $row[$field]) {
            $query = "SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr " .
                "WHERE vi.img_id = '" . $row[$field] .
                "' AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1";
            $DBRESULT2 = $pearDB->query($query);
            $row2 = $DBRESULT2->fetchRow();
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
            "WHERE host_host_id = '" . CentreonDB::escape($host_id) . "' " .
            "ORDER BY `order`";
        $DBRESULT = $pearDB->query($rq);
        while ($row = $DBRESULT->fetchRow()) {
            $rq2 = "SELECT ehi.`" . $field . "` " .
                "FROM extended_host_information ehi " .
                "WHERE ehi.host_host_id = '" . $row['host_tpl_id'] . "' LIMIT 1";
            $DBRESULT2 = $pearDB->query($rq2);
            $row2 = $DBRESULT2->fetchRow();
            if (isset($row2[$field]) && $row2[$field]) {
                $query = "SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr " .
                    "WHERE vi.img_id = '" . $row2[$field] .
                    "' AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1";
                $DBRESULT3 = $pearDB->query($query);
                $row3 = $DBRESULT3->fetchRow();
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
                    if ($result_field = getMyHostExtendedInfoImage(
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

function getImageFilePath($image_id)
{
    global $pearDB, $oreon;

    if (!$image_id) {
        return;
    }

    if (isset($image_id) && $image_id) {
        $query = "SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr " .
            "WHERE vi.img_id = '" . CentreonDB::escape($image_id) .
            "' AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1";
        $DBRESULT2 = $pearDB->query($query);
        $row2 = $DBRESULT2->fetchRow();
        if (isset($row2["dir_alias"]) && isset($row2["img_path"]) && $row2["dir_alias"] && $row2["img_path"]) {
            return $row2["dir_alias"] . "/" . $row2["img_path"];
        }
    }
}

function getMyHostTemplateModels($host_id = null)
{
    if (!$host_id) {
        return;
    }
    global $pearDB;
    $tplArr = array();
    while (1) {
        $query = "SELECT host_name, host_template_model_htm_id FROM host WHERE host_id = '" .
            CentreonDB::escape($host_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        if ($row["host_name"]) {
            $tplArr[$host_id] = html_entity_decode($row["host_name"], ENT_QUOTES, "UTF-8");
        } else {
            break;
        }
        if ($row["host_template_model_htm_id"]) {
            $host_id = $row["host_template_model_htm_id"];
        } else {
            break;
        }
    }
    return ($tplArr);
}

function getMyHostMultipleTemplateModels($host_id = null)
{
    if (!$host_id) {
        return;
    }

    global $pearDB;
    $tplArr = array();
    $query = "SELECT host_tpl_id FROM `host_template_relation` WHERE host_host_id = '" .
        CentreonDB::escape($host_id) . "' ORDER BY `order`";
    $DBRESULT = $pearDB->query($query);
    while ($row = $DBRESULT->fetchRow()) {
        $DBRESULT2 = $pearDB->query("SELECT host_name FROM host WHERE host_id = '" . $row['host_tpl_id'] . "' LIMIT 1");
        $hTpl = $DBRESULT2->fetchRow();
        $tplArr[$row['host_tpl_id']] = html_entity_decode($hTpl["host_name"], ENT_QUOTES, "UTF-8");
    }
    return ($tplArr);
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

    $query = "SELECT hg_name FROM hostgroup WHERE hg_id = '" . CentreonDB::escape($hg_id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
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
    $DBRESULT = $pearDB->query("SELECT hgr.host_host_id " .
        "FROM hostgroup_relation hgr, host h " .
        "WHERE hgr.hostgroup_hg_id = '" . CentreonDB::escape($hg_id) . "' " .
        "AND h.host_id = hgr.host_host_id $searchSTR " .
        "ORDER by h.host_name");
    while ($elem = $DBRESULT->fetchRow()) {
        $hosts[$elem["host_host_id"]] = $elem["host_host_id"];
    }
    $DBRESULT->closeCursor();
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

    $DBRESULT = $pearDB->query("SELECT hg_child_id " .
        "FROM hostgroup_hg_relation, hostgroup " .
        "WHERE hostgroup_hg_relation.hg_parent_id = '" . CentreonDB::escape($hg_id) . "' " .
        "AND hostgroup.hg_id = hostgroup_hg_relation.hg_child_id " .
        "ORDER BY hostgroup.hg_name");
    while ($elem = $DBRESULT->fetchRow()) {
        $hosts[$elem["hg_child_id"]] = $elem["hg_child_id"];
    }
    $DBRESULT->closeCursor();
    unset($elem);
    return $hosts;
}

#
## SERVICE GROUP

#
function getMyServiceGroupName($sg_id = null)
{
    if (!$sg_id) {
        return;
    }
    global $pearDB;

    $query = "SELECT sg_name FROM servicegroup WHERE sg_id = '" . CentreonDB::escape($sg_id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    if ($row["sg_name"]) {
        return html_entity_decode($row["sg_name"], ENT_QUOTES, "UTF-8");
    }
    return null;
}

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
    $DBRESULT = $pearDB->query("SELECT service_description, service_id, host_host_id, host_name " .
        "FROM servicegroup_relation, service, host " .
        "WHERE servicegroup_sg_id = '" . CentreonDB::escape($sg_id) . "' " .
        "AND servicegroup_relation.servicegroup_sg_id = servicegroup_sg_id " .
        "AND service.service_id = servicegroup_relation.service_service_id " .
        "AND servicegroup_relation.host_host_id = host.host_id " .
        "AND servicegroup_relation.host_host_id IS NOT NULL");
    while ($elem = $DBRESULT->fetchRow()) {
        $svs[$elem["host_host_id"] . "_" . $elem["service_id"]] =
            db2str($elem["service_description"]) . ":::" . $elem["host_name"];
    }

    /*
     * ServiceGroups by hostGroups
     */
    $DBRESULT = $pearDB->query("SELECT service_description, service_id, hostgroup_hg_id, hg_name " .
        "FROM servicegroup_relation, service, hostgroup " .
        "WHERE servicegroup_sg_id = '" . CentreonDB::escape($sg_id) . "' " .
        "AND servicegroup_relation.servicegroup_sg_id = servicegroup_sg_id " .
        "AND service.service_id = servicegroup_relation.service_service_id " .
        "AND servicegroup_relation.hostgroup_hg_id = hostgroup.hg_id " .
        "AND servicegroup_relation.hostgroup_hg_id IS NOT NULL");
    while ($elem = $DBRESULT->fetchRow()) {
        $hosts = getMyHostGroupHosts($elem["hostgroup_hg_id"]);
        foreach ($hosts as $key => $value) {
            $svs[$key . "_" . $elem["service_id"]] = db2str($elem["service_description"]) . ":::" . $value;
        }
    }
    $DBRESULT->closeCursor();
    return $svs;
}

function getMyServiceGroupActivateServices($sg_id = null, $access = null)
{
    global $pearDB, $pearDBndo;

    if (!$sg_id) {
        return;
    }
    $svs = array();
    $res = $pearDB->query("SELECT service_description, service_id, host_host_id, host_name
				      FROM servicegroup_relation, service, host
				      WHERE servicegroup_sg_id = '" . CentreonDB::escape($sg_id) . "'
                                      AND servicegroup_relation.servicegroup_sg_id = servicegroup_sg_id
                                      AND service.service_id = servicegroup_relation.service_service_id
                                      AND servicegroup_relation.host_host_id = host.host_id 
                                      AND servicegroup_relation.host_host_id IS NOT NULL
                                      AND service.service_activate = '1'
                                      UNION
                                      SELECT service_description, service_id, h.host_id as host_host_id, host_name
                                      FROM servicegroup_relation, service, hostgroup, hostgroup_relation hgr, host h 
                                      WHERE servicegroup_sg_id = '" . CentreonDB::escape($sg_id) . "' 
                                      AND service.service_id = servicegroup_relation.service_service_id 
                                      AND servicegroup_relation.hostgroup_hg_id = hostgroup.hg_id
                                      AND servicegroup_relation.hostgroup_hg_id IS NOT NULL
                                      AND service.service_activate = '1'
                                      AND hgr.hostgroup_hg_id = hostgroup.hg_id
                                      AND hgr.host_host_id = h.host_id
                                      ORDER BY host_name, service_description");
    while ($row = $res->fetchRow()) {
        $svs[$row['host_host_id'] . '_' . $row['service_id']] = $row['service_description'] . ':::' . $row['host_name'];
    }
    if (!is_null($access) && !$access->admin) {
        $svcIds = $access->getHostServiceIds($pearDBndo);
        foreach ($svs as $key => $value) {
            if (false === strpos($svcIds, "'" . $key . "'")) {
                unset($svs[$key]);
            }
        }
    }
    return $svs;
}

#
## SERVICE

#

function getMyServiceField($service_id = null, $field)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $tab = array();

    while (1) {
        $query = "SELECT `" . $field . "`, service_template_model_stm_id FROM service WHERE service_id = '" .
            CentreonDB::escape($service_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        $field_result = $row[$field];
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

function getMyServiceExtendedInfoField($service_id = null, $field)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;

    $tab = array();
    while (1) {
        $query = "SELECT `extended_service_information`.`" . $field . "`, `service`.`service_template_model_stm_id` " .
            "FROM `service`, `extended_service_information` " .
            "WHERE `extended_service_information`.`service_service_id` = '" . CentreonDb::escape($service_id) .
            "' AND `service`.`service_id` = '" . CentreonDb::escape($service_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        $field_result = $row[$field];
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

function getMyServiceExtendedInfoImage($service_id = null, $field)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;

    $tab = array();
    while (1) {
        $query = "SELECT s.service_template_model_stm_id, `" . $field .
            "` FROM service s, extended_service_information esi WHERE s.service_id = '" .
            CentreonDB::escape($service_id) . "' AND esi.service_service_id = s.service_id LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        if (isset($row[$field]) && $row[$field]) {
            $query = "SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr " .
                "WHERE vi.img_id = '" . $row[$field] . "' AND vidr.img_img_id = vi.img_id " .
                "AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1";
            $DBRESULT = $pearDB->query($query);
            $row = $DBRESULT->fetchRow();
            if (isset($row["dir_alias"]) && isset($row["img_path"]) && $row["dir_alias"] && $row["img_path"]) {
                return $row["dir_alias"] . "/" . $row["img_path"];
            }
        } else {
            if ($row["service_template_model_stm_id"]) {
                if (isset($tab[$row['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                return null;
            }
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
        $query = "SELECT service_description, service_template_model_stm_id FROM service WHERE service_id = '" .
            CentreonDB::escape($service_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
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
        $query = "SELECT service_alias, service_template_model_stm_id FROM service " .
            "WHERE service_id = '" . CentreonDB::escape($service_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
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
        $query = "SELECT esi.graph_id, service_template_model_stm_id FROM service, extended_service_information esi " .
            "WHERE service_id = '" . CentreonDB::escape($service_id) .
            "' AND esi.service_service_id = service_id LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
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
    $query = "SELECT s.service_id FROM services s " .
        " WHERE (s.description = '" . $dbb->escape($service_description) . "'
                        OR s.description = '" . $dbb->escape(utf8_encode($service_description)) . "') "
        . " AND s.host_id = " . $dbb->escape($host_id) . " LIMIT 1";
    $DBRESULT = $dbb->query($query);
    $row = $DBRESULT->fetchRow();
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
        $query = "SELECT service_id FROM service, host_service_relation hsr " .
            "WHERE hsr.host_host_id = '" . CentreonDB::escape($host_id) . "' AND hsr.service_service_id = service_id " .
            "AND (service_description = '" . $pearDB->escape($service_description) .
            "' OR service_description = '" . $pearDB->escape(utf8_encode($service_description)) . "') LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        # Service is directely link to a host, no problem
        if ($row["service_id"]) {
            return $row["service_id"];
        }
        # The Service might be link with a HostGroup
        $query = "SELECT service_id FROM hostgroup_relation hgr, service, host_service_relation hsr" .
            " WHERE hgr.host_host_id = '" . CentreonDB::escape($host_id) .
            "' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
            " AND service_id = hsr.service_service_id AND service_description = '" .
            CentreonDb::escape($service_description) . "'";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        if ($row["service_id"]) {
            return $row["service_id"];
        }
    }
    if ($hg_id) {
        $query = "SELECT service_id FROM service, host_service_relation hsr " .
            "WHERE hsr.hostgroup_hg_id = '" . CentreonDB::escape($hg_id) .
            "' AND hsr.service_service_id = service_id AND service_description = '" .
            CentreonDb::escape($service_description) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
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
        $query = "SELECT service_id, service_description FROM service, host_service_relation hsr " .
            "WHERE hsr.host_host_id = '" . CentreonDB::escape($host_id) .
            "' AND hsr.service_service_id = service_id AND service_description LIKE '%" .
            CentreonDB::escape($search) . "%'";
        $DBRESULT = $pearDB->query($query);
    } else {
        $query = "SELECT service_id, service_description FROM service, host_service_relation hsr " .
            "WHERE hsr.host_host_id = '" . CentreonDB::escape($host_id) . "' AND hsr.service_service_id = service_id";
        $DBRESULT = $pearDB->query($query);
    }

    while ($elem = $DBRESULT->fetchRow()) {
        $hSvs[$elem["service_id"]] = html_entity_decode(db2str($elem["service_description"]), ENT_QUOTES, "UTF-8");
    }
    $DBRESULT->closeCursor();

    /*
     * Get Services attached to hostgroups
     */
    $query = "SELECT service_id, service_description FROM hostgroup_relation hgr, service, host_service_relation hsr" .
        " WHERE hgr.host_host_id = '" . CentreonDB::escape($host_id) .
        "' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
        " AND service_id = hsr.service_service_id";
    $DBRESULT = $pearDB->query($query);
    while ($elem = $DBRESULT->fetchRow()) {
        $hSvs[$elem["service_id"]] = html_entity_decode(db2str($elem["service_description"]), ENT_QUOTES, "UTF-8");
    }
    $DBRESULT->closeCursor();
    asort($hSvs);
    return $hSvs;
}

function getMyHostActiveServices($host_id = null, $search = null)
{
    global $pearDB;

    if (!$host_id) {
        return;
    }

    $hSvs = array();

    $searchSTR = "";
    if (isset($search) && $search) {
        $searchSTR = " AND `service_description` LIKE '%" . $pearDB->escape($search) . "%'";
    }

    /*
     * Get Services attached to hosts
     */
    $query = "SELECT service_id, service_description FROM service, host_service_relation hsr " .
        "WHERE hsr.host_host_id = '" . CentreonDB::escape($host_id) .
        "' AND hsr.service_service_id = service_id AND service_activate = '1' $searchSTR";
    $DBRESULT = $pearDB->query($query);
    while ($elem = $DBRESULT->fetchRow()) {
        $hSvs[$elem["service_id"]] = $elem["service_description"];
    }
    $DBRESULT->closeCursor();

    /*
     * Get Services attached to hostgroups
     */
    $query = "SELECT service_id, service_description FROM hostgroup_relation hgr, service, host_service_relation hsr" .
        " WHERE hgr.host_host_id = '" . CentreonDB::escape($host_id) .
        "' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
        " AND service_id = hsr.service_service_id AND service_activate = '1' $searchSTR ";
    $DBRESULT = $pearDB->query($query);
    while ($elem = $DBRESULT->fetchRow()) {
        $hSvs[$elem["service_id"]] = $elem["service_description"];
    }
    $DBRESULT->closeCursor();
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

    $query = "SELECT service_id, service_description FROM service, host_service_relation hsr " .
        "WHERE hsr.host_host_id = '" . CentreonDB::escape($host_id) . "' AND hsr.service_service_id = service_id";
    $DBRESULT = $pearDB->query($query);
    while ($elem = $DBRESULT->fetchRow()) {
        $hSvs[db2str($elem["service_description"])] = html_entity_decode($elem["service_id"], ENT_QUOTES, "UTF-8");
    }
    $DBRESULT->closeCursor();

    $query = "SELECT service_id, service_description FROM hostgroup_relation hgr, service, host_service_relation hsr" .
        " WHERE hgr.host_host_id = '" . CentreonDB::escape($host_id) .
        "' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
        " AND service_id = hsr.service_service_id";
    $DBRESULT = $pearDB->query($query);
    while ($elem = $DBRESULT->fetchRow()) {
        $hSvs[db2str($elem["service_description"])] = html_entity_decode($elem["service_id"], ENT_QUOTES, "UTF-8");
    }
    $DBRESULT->closeCursor();
    return $hSvs;
}

function getAllMyServiceHosts($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $hosts = array();

    $query = "SELECT host_host_id, hostgroup_hg_id FROM host_service_relation hsr " .
        "WHERE hsr.service_service_id = '" . CentreonDB::escape($service_id) . "'";
    $DBRESULT = $pearDB->query($query);
    while ($elem = $DBRESULT->fetchRow()) {
        if ($elem["host_host_id"]) {
            $hosts[$elem["host_host_id"]] = $elem["host_host_id"];
        } elseif ($elem["hostgroup_hg_id"]) {
            $query = "SELECT host_host_id FROM hostgroup_relation hgr " .
                "WHERE hgr.hostgroup_hg_id = '" . $elem["hostgroup_hg_id"] . "'";
            $DBRESULT2 = $pearDB->query($query);
            while ($elem2 = $DBRESULT2->fetchRow()) {
                $hosts[$elem2["host_host_id"]] = $elem2["host_host_id"];
            }
            $DBRESULT2->closeCursor();
        }
    }
    $DBRESULT->closeCursor();
    return $hosts;
}

function getMyServiceHosts($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $hosts = array();

    $query = "SELECT DISTINCT host_host_id FROM host_service_relation hsr " .
        "WHERE hsr.service_service_id = '" . CentreonDB::escape($service_id) . "'";
    $DBRESULT = $pearDB->query($query);
    while ($elem = $DBRESULT->fetchRow()) {
        if ($elem["host_host_id"]) {
            $hosts[$elem["host_host_id"]] = $elem["host_host_id"];
        }
    }
    $DBRESULT->closeCursor();
    return $hosts;
}

function getMyServiceHostGroups($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $hgs = array();

    $query = "SELECT DISTINCT hostgroup_hg_id FROM host_service_relation hsr " .
        "WHERE hsr.service_service_id = '" . CentreonDB::escape($service_id) . "'";
    $DBRESULT = $pearDB->query($query);
    while ($elem = $DBRESULT->fetchRow()) {
        if ($elem["hostgroup_hg_id"]) {
            $hgs[$elem["hostgroup_hg_id"]] = $elem["hostgroup_hg_id"];
        }
    }
    $DBRESULT->closeCursor();
    return $hgs;
}

function getMyServiceTPLID($service_description = null)
{
    if (!$service_description) {
        return;
    }
    global $pearDB;
    $query = "SELECT service_id FROM service WHERE service_description = '" .
        htmlentities(str2db($service_description), ENT_QUOTES, "UTF-8") . "' AND service_register = '0' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    if ($row["service_id"]) {
        return $row["service_id"];
    }
    return null;
}

function isACheckGraphService($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $tab = array();

    while (1) {
        $query = "SELECT command_command_id, service_template_model_stm_id FROM service " .
            "WHERE service_id = '" . CentreonDB::escape($service_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        if ($row["command_command_id"]) {
            $query = "SELECT command_name FROM command WHERE command_id = '" . $row["command_command_id"] . "' LIMIT 1";
            $DBRESULT2 = $pearDB->query($query);
            $row2 = $DBRESULT2->fetchRow();
            if (strstr($row2["command_name"], "check_graph_")) {
                return true;
            } else {
                return false;
            }
        } elseif ($row["service_template_model_stm_id"]) {
            if ($tab[$row['service_template_model_stm_id']]) {
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

function getMyServiceTemplateModels($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $tplArr = array();

    while (1) {
        $query = "SELECT service_description, service_template_model_stm_id FROM service " .
            "WHERE service_id = '" . CentreonDB::escape($service_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
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

    while (1) {
        $query = "SELECT command_command_id, service_template_model_stm_id FROM service " .
            "WHERE service_id = '" . CentreonDB::escape($service_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        if ($row["command_command_id"]) {
            $query = "SELECT command_name FROM command WHERE command_id = '" . $row["command_command_id"] . "' LIMIT 1";
            $DBRESULT2 = $pearDB->query($query);
            $row2 = $DBRESULT2->fetchRow();
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
            "WHERE service_id = '" . CentreonDB::escape($service_id) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
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
function getMyCheckCmdParam($service_id = null)
{
    global $pearDB;
    if (!$service_id) {
        return;
    }

    $cmd = null;
    $arg = null;
    $query = "SELECT command_command_id, command_command_id_arg FROM service WHERE service_id = '" .
        CentreonDB::escape($service_id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $row = $DBRESULT->fetchRow();
    if ($row["command_command_id_arg"] && !$row["command_command_id"]) {
        $cmd = getMyCheckCmdName($service_id);
        return $cmd . db2str($row["command_command_id_arg"]);
    } elseif ($row["command_command_id"] && !$row["command_command_id_arg"]) {
        $query = "SELECT command_name FROM command WHERE command_id = '" . $row["command_command_id"] . "' LIMIT 1";
        $DBRESULT2 = $pearDB->query($query);
        $row2 = $DBRESULT2->fetchRow();
        $arg = getMyCheckCmdArg($service_id);
        return $row2["command_name"] . $arg;
    } elseif ($row["command_command_id"] && $row["command_command_id_arg"]) {
        $query = "SELECT command_name FROM command WHERE command_id = '" . $row["command_command_id"] . "' LIMIT 1";
        $DBRESULT2 = $pearDB->query($query);
        $row2 = $DBRESULT2->fetchRow();
        return $row2["command_name"] . db2str($row["command_command_id_arg"]);
    } else {
        return null;
    }
}

#
## Upload conf needs

#

function getMyHostID($host_name = null)
{
    if (!$host_name) {
        return;
    }
    global $pearDB;

    $DBRESULT = $pearDB->query("SELECT host_id FROM host WHERE host_name = '" . $pearDB->escape($host_name) . "' 
			OR host_name = '" . $pearDB->escape(utf8_encode($host_name)) . "'LIMIT 1");
    if ($DBRESULT->rowCount()) {
        $row = $DBRESULT->fetchRow();
        return $row["host_id"];
    }
    return null;
}

function getMyHostGroupID($hostgroup_name = null)
{
    if (!$hostgroup_name) {
        return;
    }
    global $pearDB;

    $query = "SELECT hg_id FROM hostgroup WHERE hg_name = '" .
        htmlentities(str2db($hostgroup_name), ENT_QUOTES, "UTF-8") . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    if ($DBRESULT->rowCount()) {
        $row = $DBRESULT->fetchRow();
        return $row["hg_id"];
    }
    return null;
}

function getMyServiceGroupID($servicegroup_name = null)
{
    if (!$servicegroup_name) {
        return;
    }
    global $pearDB;
    $query = "SELECT sg_id FROM servicegroup " .
        "WHERE sg_name = '" . htmlentities(str2db($servicegroup_name), ENT_QUOTES, "UTF-8") . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    if ($DBRESULT->rowCount()) {
        $row = $DBRESULT->fetchRow();
        return $row["sg_id"];
    }
    return null;
}

/**
 * Called by configLoad
 *
 * @param string $contact_name
 * @return int
 */
function getMyContactID($contact_name = null)
{
    if (!$contact_name) {
        return;
    }
    global $pearDB;
    $query = "SELECT contact_id FROM contact WHERE contact_alias = '" . $pearDB->escape($contact_name) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    if ($DBRESULT->rowCount()) {
        $row = $DBRESULT->fetchRow();
        return $row["contact_id"];
    }
    return null;
}

function getMyContactGroupID($cg_name = null)
{
    if (!$cg_name) {
        return;
    }
    global $pearDB;
    $query = "SELECT cg_id FROM contactgroup WHERE cg_name = '" .
        htmlentities($cg_name, ENT_QUOTES, "UTF-8") . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    if ($DBRESULT->rowCount()) {
        $row = $DBRESULT->fetchRow();
        return $row["cg_id"];
    }
    return null;
}

function getMyCommandID($command_name = null)
{
    if (!$command_name) {
        return;
    }
    global $pearDB;
    $query = "SELECT command_id FROM command WHERE command_name = '" .
        htmlentities($command_name, ENT_QUOTES, "UTF-8") . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    if ($DBRESULT->rowCount()) {
        $row = $DBRESULT->fetchRow();
        return $row["command_id"];
    }
    return null;
}

function getMyTPID($tp_name = null)
{
    if (!$tp_name) {
        return;
    }
    global $pearDB;
    $query = "SELECT tp_id FROM timeperiod WHERE tp_name = '" .
        htmlentities($tp_name, ENT_QUOTES, "UTF-8") . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    if ($DBRESULT->rowCount()) {
        $row = $DBRESULT->fetchRow();
        return $row["tp_id"];
    }
    return null;
}

#
## GRAPHS

#

function getDefaultMetaGraph($meta_id = null)
{
    global $pearDB;

    $query = "SELECT graph_id FROM meta_service WHERE meta_id = '" . CentreonDB::escape($meta_id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $gt = $DBRESULT->fetchRow();
    if ($gt["graph_id"]) {
        return $gt["graph_id"];
    } else {
        $DBRESULT = $pearDB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
        if ($DBRESULT->rowCount()) {
            $gt = $DBRESULT->fetchRow();
            return $gt["graph_id"];
        }
    }
    $DBRESULT = $pearDB->query("SELECT graph_id FROM giv_graphs_template LIMIT 1");
    if ($DBRESULT->rowCount()) {
        $gt = $DBRESULT->fetchRow();
        return $gt["graph_id"];
    }
    return null;
}

function getDefaultGraph($service_id = null, $rrdType = null)
{
    global $pearDB;

    $gt["graph_id"] = getMyServiceGraphID($service_id);
    if ($gt["graph_id"]) {
        return $gt["graph_id"];
    } else {
        $command_id = getMyServiceField($service_id, "command_command_id");
        $DBRESULT = $pearDB->query("SELECT graph_id FROM command WHERE `command_id` = '" . $command_id . "'");
        if ($DBRESULT->rowCount()) {
            $gt = $DBRESULT->fetchRow();
            if ($gt["graph_id"] != null) {
                return $gt["graph_id"];
            }
        }
    }
    $DBRESULT = $pearDB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
    if ($DBRESULT->rowCount()) {
        $gt = $DBRESULT->fetchRow();
        return $gt["graph_id"];
    }
    return null;
}

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
        $query = "SELECT i.* FROM index_data i, metrics m WHERE i.id = m.index_id " .
            "AND i.host_id = '" . CentreonDB::escape($host) .
            "' AND i.service_id = '" . CentreonDB::escape($service) . "'";
        $DBRESULT = $dbo->query($query);
        if ($DBRESULT->rowCount() > 0) {
            return true;
        }
    }
    if (!is_numeric($host) && !is_numeric($service)) {
        $query = "SELECT i.* FROM index_data i, metrics m WHERE i.id = m.index_id " .
            "AND i.host_name = '" . CentreonDB::escape($host) .
            "' AND i.service_description = '" . CentreonDB::escape($service) . "'";
        $DBRESULT = $dbo->query($query);

        if ($DBRESULT->rowCount() > 0) {
            return true;
        }
    }
    return false;
}

function host_has_one_or_more_GraphService($host_id, $search = 0)
{
    global $pearDBO, $lca, $is_admin;

    $services = getMyHostServices($host_id, $search);

    foreach ($services as $svc_id => $svc_name) {
        if (service_has_graph($host_id, $svc_id) &&
            ($is_admin || (!$is_admin && isset($lca["LcaHost"][$host_id][$svc_id])))
        ) {
            return true;
        }
    }
    return false;
}

function HG_has_one_or_more_host($hg_id, $hgHCache, $hgHgCache, $is_admin, $lca)
{
    global $pearDBO, $access, $servicestr;
    static $hostHasGraph = array();

    if (isset($hgHgCache[$hg_id]) && count($hgHgCache[$hg_id])) {
        return true;
    }

    if (isset($hgHCache) && isset($hgHCache[$hg_id])) {
        if ($is_admin && count($hgHCache[$hg_id])) {
            return true;
        } elseif (!$is_admin) {
            $hostIdString = "";
            foreach ($hgHCache[$hg_id] as $host_id => $enable) {
                if (isset($hostHasGraph[$host_id])) {
                    return true;
                }
                if (isset($lca["LcaHost"][$host_id])) {
                    if ($hostIdString) {
                        $hostIdString .= ",";
                    }
                    $hostIdString .= CentreonDB::escape($host_id);
                }
            }
            if ($hostIdString) {
                $DBRESULT2 = $pearDBO->query("SELECT host_id, service_id
                                                          FROM index_data 
                                                          WHERE host_id IN ($hostIdString)");
                $result = false;
                while ($row = $DBRESULT2->fetchRow()) {
                    if (isset($hostHasGraph[$row['host_id']])) {
                        continue;
                    }
                    if (false !== strpos($servicestr, "'" . $row['service_id'] . "'")) {
                        $hostHasGraph[$row['host_id']] = true;
                        $result = true;
                    }
                }
                return $result;
            }
        }
    }
    return false;
}

function getMyHostServiceID($service_id = null)
{
    if (!$service_id) {
        return;
    }
    global $pearDB;
    $query = "SELECT host_id FROM host h,host_service_relation hsr " .
        "WHERE h.host_id = hsr.host_host_id AND hsr.service_service_id = '" . CentreonDB::escape($service_id) .
        "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    if ($DBRESULT->rowCount()) {
        $row = $DBRESULT->fetchRow();
        return $row["host_id"];
    }
    return null;
}

/*
 * function getNDOPrefix()
 * - This function return NDOPrefix tables.
 *
 * @return	string	$conf_ndo["db_prefix"]	(string contains prefix like "nagios_")
 */

function getNDOPrefix()
{
    global $pearDB;

    try {
        $DBRESULT = $pearDB->query("SELECT db_prefix FROM cfg_ndo2db LIMIT 1");
    } catch (\PDOException $e) {
        print "DB Error : " . $e->getMessage() . "<br />";
    }
    $conf_ndo = $DBRESULT->fetchRow();
    unset($DBRESULT);
    return $conf_ndo["db_prefix"];
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

function isHostLocalhost($pearDB, $host_name = null)
{
    if (!isset($host_name)) {
        return 0;
    }
    $query = "SELECT `localhost` FROM nagios_server, ns_host_relation, host " .
        "WHERE host.host_name = '" . CentreonDb::escape($host_name) .
        "' AND host.host_id = ns_host_relation.host_host_id " .
        "AND ns_host_relation.nagios_server_id = nagios_server.id LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $nagios_server = $DBRESULT->fetchRow();
    $DBRESULT->closeCursor();
    if (isset($nagios_server['localhost'])) {
        return $nagios_server['localhost'];
    }
    return 0;
}

function isPollerLocalhost($pearDB, $id = null)
{
    if (!isset($id)) {
        return 0;
    }
    $query = "SELECT `localhost` FROM nagios_server WHERE nagios_server.id = '" . CentreonDb::escape($id) . "' LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $nagios_server = $DBRESULT->fetchRow();
    $DBRESULT->closeCursor();
    if (isset($nagios_server['localhost'])) {
        return $nagios_server['localhost'];
    }
    return 0;
}

function GetMyHostPoller($pearDB, $host_name = null)
{
    if (!isset($host_name)) {
        return 0;
    }
    $query = "SELECT `id` FROM nagios_server, ns_host_relation, host " .
        "WHERE host.host_name = '" . CentreonDb::escape($host_name) .
        "' AND host.host_id = ns_host_relation.host_host_id " .
        "AND ns_host_relation.nagios_server_id = nagios_server.id LIMIT 1";
    $DBRESULT = $pearDB->query($query);
    $nagios_server = $DBRESULT->fetchRow();
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
        $sid = htmlentities($sid, ENT_QUOTES, "UTF-8");
        $res = $pearDB->query("SELECT * FROM session WHERE session_id = '" . CentreonDb::escape($sid) . "'");
        if ($session = $res->fetchRow()) {
            return $session["user_id"];
        } else {
            get_error('bad session id');
        }
    } else {
        get_error('need session identifiant !');
    }
    return 0;
}

/*
 * This functions purges the var, remove all the quotes
 * and everything that comes after a semi-colon
 */

function purgeVar($myVar)
{
    $myVar = str_replace("\'", '', $myVar);
    $myVar = str_replace("\"", '', $myVar);
    $tab_myVar = preg_split("/\;/", $myVar);
    $mhost = $tab_myVar[0];
    unset($tab_myVar);
    return $myVar;
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
 * Execute a command to the Centreon Broker socket
 *
 * @param string $command The command to execute
 * @param string $socket The socket file or tcp information
 * @return bool
 */
function sendCommandBySocket($command, $socket)
{
    ob_start();
    $stream = stream_socket_client($socket, $errno, $errstr, 10);
    ob_end_clean();
    if (false === $stream) {
        throw new Exception("Error to connect to the socket.");
    }
    fwrite($stream, $command . "\n");
    $rStream = array($stream);
    $nbStream = stream_select($rStream, $wStream = null, $eStream = null, 5);
    if (false === $nbStream || 0 === $nbStream) {
        fclose($stream);
        throw new Exception("Error to read the socket.");
    }
    $ret = explode(' ', fgets($stream), 3);
    fclose($stream);
    if ($ret[1] !== '0x1' && $ret[1] !== '0x0') {
        throw new Exception("Error when execute command : " . $ret[2]);
    }
    $running = true;
    if ($ret[1] === '0x0') {
        $running = false;
    }
    return $running;
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
        $query = "SELECT * FROM service WHERE service_id = " . (int)$svcId;
        $stmt = $pearDB->query($query);
        if ($row = $stmt->fetch()) {
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

/**
 * Get current Centreon version
 */
function getCentreonVersion($pearDB)
{
    $query = 'SELECT `value` FROM `informations` WHERE `key` = "version"';
    try {
        $res = $pearDB->query($query);
    } catch (\PDOException $e) {
        return null;
    }
    $row = $res->fetchRow();
    return $row['value'];
}

function cleanString($str)
{
    $sReturn = "";
    $str = trim($str);
    if (empty($str)) {
        return $sReturn;
    }

    $str = utf8_decode($str);
    $str = utf8_encode($str);
    $str = str_replace(array("“", "„"), '"', $str);

    return $str;
}

// Global Function 

function get_my_first_allowed_root_menu($lcaTStr)
{
    global $pearDB;

    if (trim($lcaTStr) != "") {
        $rq = " SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
                FROM topology 
                WHERE topology_page IN ($lcaTStr) 
                AND topology_parent IS NULL AND topology_page IS NOT NULL AND topology_show = '1' 
                LIMIT 1";
    } else {
        $rq = " SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
                FROM topology 
                WHERE topology_parent IS NULL AND topology_page IS NOT NULL AND topology_show = '1' 
                LIMIT 1";
    }
    $DBRESULT = $pearDB->query($rq);
    $root_menu = array();
    if ($DBRESULT->rowCount()) {
        $root_menu = $DBRESULT->fetchRow();
    }
    return $root_menu;
}

function reset_search_page($url)
{
    # Clean Vars
    global $centreon;
    if (!isset($url)) {
        return;
    }
    if (isset($_GET['search'])
        && isset($centreon->historySearch[$url])
        && $_GET['search'] != $centreon->historySearch[$url]
        && !isset($_GET['num'])
        && !isset($_POST['num'])
    ) {
        $_POST['num'] = 0;
        $_GET['num'] = 0;
    } elseif (isset($_GET["search"])
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
        $rq = " SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
                FROM topology 
                WHERE  topology_page IN ($lcaTStr) 
                AND topology_parent = '" . $id_page . "' AND topology_page IS NOT NULL AND topology_show = '1' 
                ORDER BY topology_order, topology_group ";
    } else {
        $rq = " SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
                FROM topology 
                WHERE  topology_parent = '" . $id_page . "' AND topology_page IS NOT NULL AND topology_show = '1' 
                ORDER BY topology_order, topology_group ";
    }

    $DBRESULT = $pearDB->query($rq);
    $redirect = $DBRESULT->fetch();
    return $redirect;
}
