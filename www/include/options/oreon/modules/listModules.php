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

if (!isset($centreon)) {
    exit();
}

/**
 * Parsing a Zend license file
 *
 * @param string $file The file name
 * @return array
 */
function parse_zend_license_file($file)
{
    $lines = preg_split('/\n/', file_get_contents($file));
    $infos = array();
    foreach ($lines as $line) {
        if (preg_match('/^([^= ]+)\s*=\s*(.+)$/', $line, $match)) {
            $infos[$match[1]] = $match[2];
        }
    }
    return $infos;
}

/*
 * Test Modules Existence for deletion
 */
if ($id && $o == "d" && testModuleExistence($id)) {
    $moduleinfo = getModuleInfoInDB(null, $id);
    deleteModuleInDB($id);

    if ($moduleinfo["is_removeable"]) {
        /*
         * SQL deletion
         */
        $sql_file = "uninstall.sql";
        $sql_file_path = "./modules/".$moduleinfo["name"]."/sql/";
        if ($moduleinfo["sql_files"] && file_exists($sql_file_path.$sql_file)) {
            execute_sql_file($sql_file, $sql_file_path);
        }
        
        /*
         * PHP deletion
         */
        $php_file = "uninstall.php";
        $php_file_path = "./modules/".$moduleinfo["name"]."/php/";
        if ($moduleinfo["php_files"] && file_exists($php_file_path.$php_file)) {
            include_once($php_file_path.$php_file);
        }
        
        /*
         * SESSION deletion
         */
        if (isset($oreon->modules[$moduleinfo["name"]])) {
            unset($oreon->modules[$moduleinfo["name"]]);
        }
    }
    $centreon->initHooks();
}

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Get Modules List
 */
$handle = opendir("./modules/");

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
$i = 0;
while (false !== ($filename = readdir($handle))) {
    if (is_dir(_CENTREON_PATH_ . "www/modules/" . $filename) && $filename != "." && $filename != "..") {
        $moduleinfo = getModuleInfoInDB($filename, null);
        
        /*
         * Package already installed
         */
        if (isset($moduleinfo["rname"])) {
            if (function_exists('zend_loader_enabled') && file_exists(_CENTREON_PATH_ . "www/modules/" . $filename . "/license/merethis_lic.zl")) {
                if (zend_loader_file_encoded(_CENTREON_PATH_ . "www/modules/" . $filename . "/license/merethis_lic.zl")) {
                    $zend_info = zend_loader_file_licensed(_CENTREON_PATH_ . "www/modules/" . $filename . "/license/merethis_lic.zl");
                } else {
                    $zend_info = parse_zend_license_file(_CENTREON_PATH_ . "www/modules/" . $filename . "/license/merethis_lic.zl");
                }
                $license_expires = date("d/m/Y", strtotime($zend_info['Expires']));
            } else {
                $license_expires = "N/A";
            }
            $elemArr[$i] = array(   "MenuClass" => "list_".$style,
                                    "RowMenu_name" => $moduleinfo["name"],
                                    "RowMenu_rname" => $moduleinfo["rname"],
                                    "RowMenu_release" => $moduleinfo["mod_release"],
                                    "RowMenu_infos" => $moduleinfo["infos"],
                                    "RowMenu_moduleId" => $moduleinfo["id"],
                                    "RowMenu_author" => $moduleinfo["author"],
                                    "RowMenu_licenseExpire" => $license_expires,
                                    "RowMenu_upgrade" => 0,
                                    "RowMenu_isinstalled" => _("Yes"),
                                    "RowMenu_link" => "?p=".$p."&o=w&id=".$moduleinfo["id"],
                                    "RowMenu_link_install" => null,
                                    "RowMenu_link_delete" => "?p=".$p."&o=w&id=".$moduleinfo["id"]."&o=d",
                                    "RowMenu_link_upgrade" => "?p=".$p."&o=w&id=".$moduleinfo["id"]."&o=u");

            /*
             * Check Update
             */
            $upgradeAvailable = false;
            if (!file_exists(_CENTREON_PATH_ . "www/modules/" . $filename . "/license")) {
                $upgradeAvailable = true;
            } else {
                if (function_exists('zend_loader_enabled') && file_exists(_CENTREON_PATH_ . "www/modules/" . $filename . "/license/merethis_lic.zl")) {
                    $upgradeAvailable = true;
                }
            }
            if ($upgradeAvailable) {
                if (is_dir("./modules/".$moduleinfo["name"]."/UPGRADE")) {
                    $handle2 = opendir("./modules/".$moduleinfo["name"]."/UPGRADE");
                    while (false !== ($filename2 = readdir($handle2))) {
                        if (substr($filename2, 0, 1) != "." && strstr($filename2, $moduleinfo["name"]) && file_exists("./modules/".$moduleinfo["name"]."/UPGRADE/".$filename2."/conf.php")) {
                            @include_once("./modules/".$moduleinfo["name"]."/UPGRADE/".$filename2."/conf.php");
                            if (isset($upgrade_conf[$moduleinfo["name"]]["release_from"]) && $moduleinfo["mod_release"] == $upgrade_conf[$moduleinfo["name"]]["release_from"]) {
                                $elemArr[$i]["RowMenu_upgrade"] = 1;
                            }
                        }
                    }
                    closedir($handle2);
                }
            }
        } else {
            /*
             * Valid package to install
             */
            if (is_file(_CENTREON_PATH_ . "www/modules/".$filename."/conf.php")) {
                include_once(_CENTREON_PATH_ . "www/modules/".$filename."/conf.php");
            } elseif (is_file(_CENTREON_PATH_ . "www/modules/".$filename."/.api/conf.php")) {
                include_once(_CENTREON_PATH_ . "www/modules/".$filename."/.api/conf.php");
            }

            if (isset($module_conf[$filename]["name"])) {
                $picturePath = "./img/icones/16x16/component_green.gif";
                if (file_exists(_CENTREON_PATH_ . "www/modules/".$filename."/icone.gif")) {
                    $picturePath =  "./modules/".$filename."/icone.gif";
                }
                if (file_exists(_CENTREON_PATH_ . "www/modules/".$filename."/.api/icone.gif")) {
                    $picturePath =  "./modules/".$filename."/.api/icone.gif";
                }
                if (function_exists('zend_loader_enabled') && file_exists(_CENTREON_PATH_ . "www/modules/" . $filename . "/license/merethis_lic.zl")) {
                    if (zend_loader_file_encoded(_CENTREON_PATH_ . "www/modules/" . $filename . "/license/merethis_lic.zl")) {
                        $zend_info = zend_loader_file_licensed(_CENTREON_PATH_ . "www/modules/" . $filename . "/license/merethis_lic.zl");
                    } else {
                        $zend_info = parse_zend_license_file(_CENTREON_PATH_ . "www/modules/" . $filename . "/license/merethis_lic.zl");
                    }
                    $license_expires = date("d/m/Y", strtotime($zend_info['Expires']));
                } else {
                    $license_expires = "N/A";
                }

                $elemArr[$i] = array(   "MenuClass" => "list_".$style,
                                        "RowMenu_name" => $module_conf[$filename]["name"],
                                        "RowMenu_rname" => $module_conf[$filename]["rname"],
                                        "RowMenu_release" => $module_conf[$filename]["mod_release"],
                                        "RowMenu_author" => $module_conf[$filename]["author"],
                                        "RowMenu_licenseExpire" => $license_expires,
                                        "RowMenu_infos" =>  (isset($module_conf[$filename]["infos"]) ? $module_conf[$filename]["infos"] : ""),
                                        "RowMenu_picture"  =>  $picturePath,
                                        "RowMenu_isinstalled" => _("No"),
                                        "RowMenu_link" => "?p=".$p."&o=w&name=".$module_conf[$filename]["name"],
                                        "RowMenu_link_install" => "?p=".$p."&o=w&name=".$module_conf[$filename]["name"]."&o=i",
                                        "RowMenu_link_delete" => null,
                                        "RowMenu_link_upgrade" => null);
            } else {
                /*
                 * Non valid package
                 */
                $elemArr[$i] = array(   "MenuClass" => "list_".$style,
                                        "RowMenu_name" => $filename,
                                        "RowMenu_rname" => _("NA"),
                                        "RowMenu_release" => _("NA"),
                                        "RowMenu_author" => _("NA"),
                                        "RowMenu_isinstalled" => _("Impossible"),
                                        "RowMenu_link" => null);
            }
        }
        $style != "two" ? $style = "two" : $style = "one";
        $i++;
    }
}
closedir($handle);

/*
 * Init Template Var
 */
$tpl->assign("elemArr", $elemArr);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$tpl->display("listModules.ihtml");
