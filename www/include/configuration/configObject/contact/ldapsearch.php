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

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once(_CENTREON_PATH_ . "www/include/common/common-Func.php");
require_once(_CENTREON_PATH_ . "www/class/centreonSession.class.php");
require_once(_CENTREON_PATH_ . "www/class/centreon.class.php");
require_once(_CENTREON_PATH_ . "www/class/centreonXML.class.php");
require_once(_CENTREON_PATH_ . "www/class/centreonDB.class.php");
require_once(_CENTREON_PATH_ . "www/class/centreonLDAP.class.php");

CentreonSession::start(1);

if (!isset($_SESSION["centreon"])) {
    exit();
} else {
    $oreon = $_SESSION["centreon"];
}

if (!isset($_POST['confList']) || !strlen($_POST['confList'])) {
    exit();
}
$confList = $_POST['confList'];

$ldap_search_filters = array();
if (isset($_POST['ldap_search_filter'])) {
    $ldap_search_filters = $_POST['ldap_search_filter'];
}

global $buffer;
$pearDB = new CentreonDB();

/* Debug options */
$debug_ldap_import = false;
$dbresult = $pearDB->query("SELECT `key`, `value` FROM `options` WHERE `key` IN ('debug_ldap_import', 'debug_path')");
while ($row = $dbresult->fetchRow()) {
    if ($row['key'] == 'debug_ldap_import') {
        if ($row['value'] == 1) {
            $debug_ldap_import = true;
        }
    } elseif ($row['key'] == 'debug_path') {
        $debug_path = trim($row['value']);
    }
}
$dbresult->free();
if ($debug_path == '') {
    $debug_ldap_import = false;
}

/* Get ldap users in database */
$queryGetLdap = 'SELECT contact_alias
		 FROM contact
                 WHERE contact_register = 1';
$res = $pearDB->query($queryGetLdap);
$listLdapUsers = array();
if (!PEAR::isError($res)) {
    while ($row = $res->fetchRow()) {
        $listLdapUsers[] = $row['contact_alias'];
    }
}

$buffer = new CentreonXML();
$buffer->startElement("reponse");


$ids = explode(",", $confList);
foreach ($ids as $arId) {
    $ldap = new CentreonLDAP($pearDB, null, $arId);
    $connect = false;
    if ($ldap->connect()) {
        $connect = true;
    }
    if ($connect) {
        $ldap_search_filter = "";
        $ldap_base_dn = "";
        $ldap_search_limit = 0;
        $ldap_search_timeout = 0;

        $query = "SELECT ari_name, ari_value
                  FROM auth_ressource_info
    	    	  WHERE ar_id = ?";
        $stmt = $pearDB->prepare($query);
        $res = $pearDB->execute($stmt, array($arId));

        while ($row = $res->fetchRow()) {
            switch ($row['ari_name']) {
                case "user_filter":
                    $ldap_search_filter = sprintf($row['ari_value'], '*');
                    break;
                case "user_base_search":
                    $ldap_base_dn = $row['ari_value'];
                    break;
                case "ldap_search_timeout":
                    $ldap_search_timeout = $row['ari_value'];
                    break;
                case "ldap_search_limit":
                    $ldap_search_limit = $row['ari_value'];
                    break;
                default:
                    break;
            }
        }

        if (isset($ldap_search_filters[$arId]) && $ldap_search_filters[$arId]) {
            $ldap_search_filter = $ldap_search_filters[$arId];
        }
        
        $searchResult = $ldap->search($ldap_search_filter, $ldap_base_dn, $ldap_search_limit, $ldap_search_timeout);
        $number_returned = count($searchResult);
        if ($number_returned) {
            $buffer->writeElement("entries", $number_returned);
            for ($i = 0; $i < $number_returned; $i++) {
                if (isset($searchResult[$i]["dn"])) {
                    $isvalid = "0";
                    if ($searchResult[$i]["alias"] != "") {
                        $isvalid = "1";
                    }
                    $in_database = "0";
                    if (in_array(htmlentities($searchResult[$i]["alias"], ENT_QUOTES, 'UTF-8'), $listLdapUsers)) {
                        $in_database = "1";
                    }

                    $searchResult[$i]["firstname"] = str_replace("'", "", $searchResult[$i]["firstname"]);
                    $searchResult[$i]["firstname"] = str_replace("\"", "", $searchResult[$i]["firstname"]);
                    $searchResult[$i]["firstname"] = str_replace("\'", "\\\'", $searchResult[$i]["firstname"]);

                    $searchResult[$i]["lastname"] = str_replace("'", "", $searchResult[$i]["lastname"]);
                    $searchResult[$i]["lastname"] = str_replace("\"", "", $searchResult[$i]["lastname"]);
                    $searchResult[$i]["lastname"] = str_replace("\'", "\\\'", $searchResult[$i]["lastname"]);

                    $searchResult[$i]["name"] = str_replace("'", "", $searchResult[$i]["name"]);
                    $searchResult[$i]["name"] = str_replace("\"", "", $searchResult[$i]["name"]);
                    $searchResult[$i]["name"] = str_replace("\'", "\\\'", $searchResult[$i]["name"]);

                    $buffer->startElement("user");
                    $query = "SELECT `ar_id`, `ar_name` 
                              FROM auth_ressource
                              WHERE ar_id = " . $pearDB->escape($arId);
                    $resServer = $pearDB->query($query);
                    $row = $resServer->fetchRow();
                    $buffer->writeAttribute("server", $row['ar_name']);
                    $buffer->writeAttribute("ar_id", $row['ar_id']);
                    $buffer->writeAttribute("isvalid", $isvalid);
                    $buffer->startElement("dn");
                    $buffer->writeAttribute("isvalid", (($searchResult[$i]['dn'] != "") ? "1" : "0"));
                    $buffer->text($searchResult[$i]['dn'], 1, 0);
                    $buffer->endElement();
                    $buffer->startElement("sn");
                    $buffer->writeAttribute("isvalid", (($searchResult[$i]['lastname'] != "") ? "1" : "0"));
                    $buffer->text($searchResult[$i]['lastname'], 1, 0);
                    $buffer->endElement();
                    $buffer->startElement("givenname");
                    $buffer->writeAttribute("isvalid", (($searchResult[$i]['firstname'] != "") ? "1" : "0"));
                    $buffer->text($searchResult[$i]['firstname'], 1, 0);
                    $buffer->endElement();
                    $buffer->startElement("mail");
                    $buffer->writeAttribute("isvalid", (($searchResult[$i]['email'] != "") ? "1" : "0"));
                    $buffer->text($searchResult[$i]['email'], 1, 0);
                    $buffer->endElement();
                    $buffer->startElement('pager');
                    $buffer->writeAttribute("isvalid", (($searchResult[$i]['pager'] != "") ? "1" : "0"));
                    $buffer->text($searchResult[$i]['pager'], 1, 0);
                    $buffer->endElement();
                    $buffer->startElement("cn");
                    $buffer->writeAttribute("isvalid", (($searchResult[$i]['name'] != '') ? "1" : "0"));
                    $buffer->text($searchResult[$i]['name'], 1, 0);
                    $buffer->endElement();
                    $buffer->startElement("uid");
                    $buffer->writeAttribute("isvalid", (($searchResult[$i]['alias'] != '') ? "1" : "0"), 1, 0);
                    $buffer->text($searchResult[$i]['alias'], 1, 0);
                    $buffer->endElement();
                    $buffer->startElement("in_database");
                    $buffer->text($in_database, 1, 0);
                    $buffer->endElement();
                    $buffer->endElement();
                }
            }
        } else {
            $buffer->writeElement("entries", "0");
            $buffer->writeElement("error", ldap_err2str($ldap->getDs()));
        }
    }
}
if (isset($error)) {
    $buffer->writeElement("error", $error);
}
$buffer->endElement();

header('Content-Type: text/xml');
$buffer->output();
if (isset($debug_ldap_import) && $debug_ldap_import) {
    error_log("[" . date("d/m/Y H:s") . "] LDAP Search : XML Output : " . $buffer->output() . "\n", 3, $debug_path . "ldapsearch.log");
}
