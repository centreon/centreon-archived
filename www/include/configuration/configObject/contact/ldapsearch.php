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

	if (!isset($_POST['serverList']) || !strlen($_POST['serverList'])) {
	    exit();
	}

	$serverList = $_POST['serverList'];

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

/*
	$ldap_search_filter = null;
	$ldap_base_dn = null;
	$ldap_search_timeout = null;
	$ldap_search_limit = null;

	if (isset($_GET["ldap_search_filter"]) && ($_GET["ldap_search_filter"] != "undefined") )
		$ldap_search_filter = $_GET["ldap_search_filter"];
	else if (isset($_POST["ldap_search_filter"])  && ($_POST["ldap_search_filter"]!= "undefined"))
		$ldap_search_filter = $_POST["ldap_search_filter"];

	if (isset($_GET["ldap_base_dn"]) && ($_GET["ldap_base_dn"]!= "undefined") )
		$ldap_base_dn = $_GET["ldap_base_dn"];
	else if (isset($_POST["ldap_base_dn"])  && ($_POST["ldap_base_dn"]!= "undefined"))
		$ldap_base_dn = $_POST["ldap_base_dn"];

	if (isset($_GET["ldap_search_timeout"]) && ($_GET["ldap_search_timeout"]!= "undefined") )
		$ldap_search_timeout = $_GET["ldap_search_timeout"];
	else if (isset($_POST["ldap_search_timeout"])  && ($_POST["ldap_search_timeout"]!= "undefined"))
		$ldap_search_timeout = $_POST["ldap_search_timeout"];

	if (isset($_GET["ldap_search_limit"]) && ($_GET["ldap_search_limit"]!= "undefined") )
		$ldap_search_limit = $_GET["ldap_search_limit"];
	else if (isset($_POST["ldap_search_limit"])  && ($_POST["ldap_search_limit"]!= "undefined"))
		$ldap_search_limit = $_POST["ldap_search_limit"];
*/

	/* Get ldap users in database */
	$queryGetLdap = 'SELECT contact_ldap_dn
		FROM contact
		WHERE contact_ldap_dn IS NOT NULL
			AND contact_auth_type = "ldap"';
	$res = $pearDB->query($queryGetLdap);
	$listLdapUsers = array();
	if (!PEAR::isError($res)) {
	    while ($row = $res->fetchRow()) {
	        $listLdapUsers[] = $row['contact_ldap_dn'];
	    }
	}

	$buffer = new CentreonXML();
    $buffer->startElement("reponse");

    $ldap = new CentreonLDAP($pearDB, null);
    $ids = explode(",", $serverList);
    foreach ($ids as $id) {
        $connect = false;
    	if ($ldap->connect(null, $id)) {
    	    $connect = true;
    	}
    	if ($connect) {
    	    $ldap_search_filter = "";
    	    $ldap_base_dn = "";
    	    $ldap_search_limit = 0;
    	    $ldap_search_timeout = 0;

    	    $query = "SELECT ari_name, ari_value
    	    		  FROM auth_ressource_info
    	    		  WHERE ar_id = " . $pearDB->escape($id);
    	    $res = $pearDB->query($query);
    	    while ($row = $res->fetchRow()) {
    	        switch ($row['ari_name']) {
    	            case "user_filter":
    	                $ldap_search_filter = sprintf($row['ari_value'], '*');
    	                break;
    	            case "user_base_search":
    	                $ldap_base_dn = $row['ari_value'];
    	                break;
    	            default:
    	                break;
    	        }
    	    }

    	    $query = "SELECT `key`, `value` FROM options WHERE `key` IN ('ldap_search_limit', 'ldap_search_timeout')";
    	    $res = $pearDB->query($query);
    	    while ($row = $res->fetchRow()) {
    	        switch ($row['key']) {
    	            case "ldap_search_timeout":
                        $ldap_search_timeout = $row['value'];
    	                break;
    	            case "ldap_search_limit":
    	                $ldap_search_limit = $row['value'];
    	                break;
    	            default :
    	                break;
    	        }
    	    }
    	    $searchResult = $ldap->search($ldap_search_filter, $ldap_base_dn, $ldap_search_limit, $ldap_search_timeout);
    	    $number_returned = count($searchResult);
    		if ($number_returned) {
    			$buffer->writeElement("entries", $number_returned);
    			for ($i = 0 ; $i < $number_returned ; $i++) {
    				if (isset($searchResult[$i]["dn"])){
    					$isvalid = "0";
    					if ($searchResult[$i]["alias"] != "") {
    					    $isvalid = "1";
    					}
    					$in_database = "0";
    					if (in_array($searchResult[$i]["dn"], $listLdapUsers)) {
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
    					$query = "SELECT `ari_value` FROM auth_ressource_info WHERE ar_id = " .$pearDB->escape($id) . " AND ari_name = 'host'";
    					$resServer = $pearDB->query($query);
    					$row = $resServer->fetchRow();
    					$buffer->writeAttribute("server", $row['ari_value']);
    					$buffer->writeAttribute("isvalid", $isvalid);
    					$buffer->startElement("dn");
    					$buffer->writeAttribute("isvalid", (($searchResult[$i]['dn'] != "") ? "1" : "0" ));
    					$buffer->text($searchResult[$i]['dn'], 1, 0);
    					$buffer->endElement();
    					$buffer->startElement("sn");
    					$buffer->writeAttribute("isvalid", (($searchResult[$i]['lastname'] != "") ? "1" : "0" ));
    					$buffer->text($searchResult[$i]['lastname'], 1, 0);
    					$buffer->endElement();
    					$buffer->startElement("givenname");
    					$buffer->writeAttribute("isvalid", (($searchResult[$i]['firstname'] != "") ? "1" : "0" ));
    					$buffer->text($searchResult[$i]['firstname'], 1, 0);
    					$buffer->endElement();
    					$buffer->startElement("mail");
    					$buffer->writeAttribute("isvalid", (($searchResult[$i]['email'] != "") ? "1" : "0" ));
    					$buffer->text($searchResult[$i]['email'], 1, 0);
    					$buffer->endElement();
    					$buffer->startElement('pager');
    					$buffer->writeAttribute("isvalid", (($searchResult[$i]['pager'] != "") ? "1" : "0" ));
    					$buffer->text($searchResult[$i]['pager'], 1, 0);
    					$buffer->endElement();
    					$buffer->startElement("cn");
    					$buffer->writeAttribute("isvalid", (($searchResult[$i]['name'] != '') ? "1" : "0" ));
    					$buffer->text($searchResult[$i]['name'], 1, 0);
    					$buffer->endElement();
    					$buffer->startElement("uid");
    					$buffer->writeAttribute("isvalid", (($searchResult[$i]['alias'] != '') ? "1" : "0" ), 1, 0);
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
	if (isset($error)){
		$buffer->writeElement("error", $error);
	}
	$buffer->endElement();

	header('Content-Type: text/xml');
	$buffer->output();
	if (isset($debug_ldap_import) && $debug_ldap_import) {
		error_log("[" . date("d/m/Y H:s") ."] LDAP Search : XML Output : ".$buffer->output()."\n", 3, $debug_path."ldapsearch.log");
	}
?>