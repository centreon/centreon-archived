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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($oreon)) {
    exit();
}

function getModuleInfoInDB($name = null, $id = null)
{
    if (!$name && !$id) {
        return;
    }
    global $pearDB;
    if ($id) {
        $rq = "SELECT * FROM modules_informations WHERE id='".$id."'  LIMIT 1";
    } elseif ($name) {
        $rq = "SELECT * FROM modules_informations WHERE name='".$name."' LIMIT 1";
    }
    $DBRESULT = $pearDB->query($rq);
    if ($DBRESULT->rowCount()) {
        return ($DBRESULT->fetchRow());
    } else {
        return array();
    }
}

function testModuleExistence($id = null, $name = null)
{
    if (!$id && !$name) {
        return false;
    }
    global $pearDB;
    if ($id) {
        $rq = "SELECT id FROM modules_informations WHERE id = '".$id."'  LIMIT 1";
    } elseif ($name) {
        $rq = "SELECT id FROM modules_informations WHERE name = '".$name."'  LIMIT 1";
    }
    $DBRESULT = $pearDB->query($rq);
    if ($DBRESULT->rowCount()) {
        return true;
    } else {
        return false;
    }
}

function testUpgradeExistence($id = null, $release = null)
{
    if (!$id || !$release) {
        return true;
    }
    global $pearDB;
    $DBRESULT = $pearDB->query("SELECT mod_release FROM modules_informations WHERE id = '".$id."' LIMIT 1");
    $module = $DBRESULT->fetchRow();
    if ($module["mod_release"] == $release) {
        return true;
    } else {
        return false;
    }
}

function upgradeModuleInDB($id = null, $upgrade_conf = array())
{
    if (!$id) {
        return null;
    }
    if (testUpgradeExistence($id, $upgrade_conf["release_to"])) {
        return null;
    }
    global $pearDB;
    $rq = "UPDATE `modules_informations` SET ";
    if (isset($upgrade_conf["rname"]) && $upgrade_conf["rname"]) {
        $rq .= "rname = '".htmlentities($upgrade_conf["rname"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (isset($upgrade_conf["release_to"]) && $upgrade_conf["release_to"]) {
        $rq .= "mod_release = '".htmlentities($upgrade_conf["release_to"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (isset($upgrade_conf["is_removeable"]) && $upgrade_conf["is_removeable"]) {
        $rq .= "is_removeable = '".htmlentities($upgrade_conf["is_removeable"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (isset($upgrade_conf["infos"]) && $upgrade_conf["infos"]) {
        $rq .= "infos = '".htmlentities($upgrade_conf["infos"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (isset($upgrade_conf["author"]) && $upgrade_conf["author"]) {
        $rq .= "author = '".htmlentities($upgrade_conf["author"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (isset($upgrade_conf["lang_files"]) && $upgrade_conf["lang_files"]) {
        $rq .= "lang_files = '".htmlentities($upgrade_conf["lang_files"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (isset($upgrade_conf["sql_files"]) && $upgrade_conf["sql_files"]) {
        $rq .= "sql_files = '".htmlentities($upgrade_conf["sql_files"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (isset($upgrade_conf["php_files"]) && $upgrade_conf["php_files"]) {
        $rq .= "php_files = '".htmlentities($upgrade_conf["php_files"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (isset($upgrade_conf["svc_tools"]) && $upgrade_conf["svc_tools"]) {
        $rq .= "svc_tools = '" . htmlentities($upgrade_conf["svc_tools"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (isset($upgrade_conf["host_tools"]) && $upgrade_conf["host_tools"]) {
        $rq .= "svc_tools = '" . htmlentities($upgrade_conf["host_tools"], ENT_QUOTES, "UTF-8")."', ";
    }
    if (strcmp("UPDATE `modules_informations` SET ", $rq)) {
        # Delete last ',' in request
        $rq[strlen($rq)-2] = " ";
        $rq .= "WHERE id = '".$id."'";
        $DBRESULT = $pearDB->query($rq);
        return true;
    }
    return null;
}

function deleteModuleInDB($id = null)
{
    if (!$id) {
        return null;
    }
    global $pearDB;
    $rq = "DELETE FROM `modules_informations` WHERE id = '".$id."'";
    $DBRESULT = $pearDB->query($rq);
    return true;
}

function execute_sql_file($name = null, $sql_file_path = null)
{
    if (!$sql_file_path || !$name) {
        return;
    }
    global $pearDB, $conf_centreon;
    $sql_stream = file($sql_file_path.$name);
    $str = null;
    for ($i = 0; $i <= count($sql_stream) - 1; $i++) {
        $line = $sql_stream[$i];
        if ($line[0] != '#') {
            $pos = strrpos($line, ";");
            if ($pos != false) {
                $str .= $line;
                $str = chop($str);
                $str = str_replace("@DB_CENTSTORAGE@", $conf_centreon['dbcstg'], $str);
                $DBRESULT = $pearDB->query($str);
                $str = null;
            } else {
                $str .= $line;
            }
        }
    }
}
