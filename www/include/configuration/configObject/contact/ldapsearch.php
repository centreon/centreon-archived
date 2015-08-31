<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

//require_once("/etc/centreon/centreon.conf.php");
require_once("@CENTREON_ETC@/centreon.conf.php");
require_once($centreon_path . "www/include/common/common-Func.php");
require_once($centreon_path . "www/class/centreonSession.class.php");
require_once($centreon_path . "www/class/centreon.class.php");
require_once($centreon_path . "www/class/centreonXML.class.php");
require_once($centreon_path . "www/class/centreonDB.class.php");
require_once($centreon_path . "www/class/centreonLDAP.class.php");

CentreonSession::start();

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
    	    	  WHERE ar_id = " . $pearDB->escape($arId);
        $res = $pearDB->query($query);
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
?>