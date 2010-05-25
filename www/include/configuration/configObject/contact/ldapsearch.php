<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 
 	require_once("@CENTREON_ETC@/centreon.conf.php");
	require_once("../../../../include/common/common-Func.php");
 	require_once("../../../../$classdir/centreonSession.class.php");
 	require_once("../../../../$classdir/centreon.class.php");
 	require_once("../../../../$classdir/centreonXML.class.php");
 	require_once("../../../../$classdir/centreonDB.class.php");
 	require_once("../../../../$classdir/centreonLDAP.class.php");

 	CentreonSession::start();

	if (!isset($_SESSION["oreon"])) {
		header("Location: ../../../../index.php");
		exit();
	} else {
		$oreon =& $_SESSION["oreon"];
	}

	global $buffer;
	$pearDB = new CentreonDB();

	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
	while ($res =& $DBRESULT->fetchRow())
		$ldap_search[$res["key"]] = myDecode($res["value"]);
	$DBRESULT->free();
	$debug =& $ldap_search; 
	
	$ldap_search_filter = $ldap_search['ldap_search_filter'];
	$ldap_base_dn = $ldap_search['ldap_base_dn'];
	$ldap_search_timeout = $ldap_search['ldap_search_timeout'];
	$ldap_search_limit = $ldap_search['ldap_search_limit'];
	$ldap_login_attrib = $ldap_search['ldap_login_attrib'];
	$ldap_protocol_version = $ldap_search['ldap_protocol_version'];

	if (isset($_GET["ldap_search_filter"]) && ($_GET["ldap_search_filter"]!= "undefined") )
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

	$connect = true;
	
	$debug_ldap_import = $debug['debug_ldap_import'];
	$debug_path = $debug['debug_path'];

	if (!isset($debug_ldap_import))
		$debug_ldap_import = 0;

	if ($debug_ldap_import == 1)
		error_log("[" . date("d/m/Y H:s") ."] LDAP Search : $ldap_search_filter\n", 3, $debug_path."ldapsearch.log");

	if ($ldap_search['ldap_ssl'])
		$ldapuri = "ldaps://" ;
	else
		$ldapuri = "ldap://" ;

	if ($debug_ldap_import == 1)
		error_log("[" . date("d/m/Y H:s") ."] LDAP Search : URI : " . $ldapuri . $ldap_search['ldap_host'].":".$ldap_search['ldap_port'] ."\n", 3, $debug_path."ldapsearch.log");
 	$ds = @ldap_connect($ldapuri . $ldap_search['ldap_host'].":".$ldap_search['ldap_port']);


	@ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $ldap_protocol_version);
	@ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

	if ($debug_ldap_import == 1)
		error_log("[" . date("d/m/Y H:s") ."] LDAP Search : Credentials : " . $ldap_search['ldap_search_user'] . " :: " . $ldap_search['ldap_search_user_pwd'] ."\n", 3, $debug_path."ldapsearch.log");
	if ($ldap_search['ldap_search_user'] && $ldap_search['ldap_search_user_pwd'])
		@ldap_bind($ds,$ldap_search['ldap_search_user'],$ldap_search['ldap_search_user_pwd']);
	else
		@ldap_bind($ds);

	if ($debug_ldap_import == 1)
		error_log("[" . date("d/m/Y H:s") ."] LDAP Search : Bind : " . ldap_errno($ds) ."\n", 3, $debug_path."ldapsearch.log");
	/* In some case, we fallback to local Auth
    0 : Bind succesfull => Default case
    -1 : Can't contact LDAP server (php4) => Fallback
    51 : Server is busy => Fallback
    52 : Server is unavailable => Fallback
    81 : Can't contact LDAP server (php5) => Fallback
    Else : Go away !!
	*/
	if ($ds) {
		switch (ldap_errno($ds)) {
			case 0:
			   $connect = true;
			   if ($debug_ldap_import == 1)
			   	error_log("[" . date("d/m/Y H:s") ."] LDAP Search : Bind OK\n", 3, $debug_path."ldapsearch.log");
			   break;
			case -1:
			case 51:
			case 52:
			case 81:
				$connect = false;
			   break;
			default:
			   $connect = false;
			   break;
		}
	} else {
		$connect = false;
	}

	if ($connect) {
		if (isset($ldap_login_attrib) && $ldap_login_attrib != "") {
			$attrib = array("givenname", "mail", "uid", "cn", "sn", "samaccountname", $ldap_login_attrib);
 		} else{
 			$attrib = array("givenname", "mail", "uid", "cn", "sn", "samaccountname");
 		}		

		if ($debug_ldap_import == 1) {
			error_log("[" . date("d/m/Y H:s") ."] LDAP Search : Base DN : ". $ldap_base_dn ."\n", 3, $debug_path."ldapsearch.log");
			error_log("[" . date("d/m/Y H:s") ."] LDAP Search : Filter : ". $ldap_search_filter . "\n", 3, $debug_path."ldapsearch.log");
			error_log("[" . date("d/m/Y H:s") ."] LDAP Search : Size Limit : ". $ldap_search_limit . "\n", 3, $debug_path."ldapsearch.log");
			error_log("[" . date("d/m/Y H:s") ."] LDAP Search : Timeout : ". $ldap_search_timeout . "\n", 3, $debug_path."ldapsearch.log");
		}
		$sr = @ldap_search($ds, $ldap_base_dn, $ldap_search_filter,$attrib,0,$ldap_search_limit,$ldap_search_timeout);
	
		if ($debug_ldap_import == 1)
			error_log("[" . date("d/m/Y H:s") ."] LDAP Search : Error : ". ldap_err2str($ds)."\n", 3, $debug_path."ldapsearch.log");
	
		@ldap_sort($ds, $sr, "dn");
		$number_returned = @ldap_count_entries($ds,$sr);
		if ($debug_ldap_import == 1)
			error_log("[" . date("d/m/Y H:s") ."] LDAP Search : ". (isset($number_returned) ? $number_returned : "0") . " entries found\n", 3, $debug_path."ldapsearch.log");
	
		$info = @ldap_get_entries($ds, $sr);
		if ($debug_ldap_import == 1)
			error_log("[" . date("d/m/Y H:s") ."] LDAP Search : ". $info["count"] . " \n", 3, $debug_path."ldapsearch.log");
		@ldap_free_result($sr);
		
		$buffer = new CentreonXML();
		if ($number_returned) {
			$buffer->startElement("reponse");
			$buffer->writeElement("entries", $number_returned);			
			for ($i = 0 ; $i < $number_returned ; $i++) {
				if (isset($info[$i]["givenname"])){
					$isvalid = "0";
					
					if (isset($ldap_login_attrib) && $ldap_login_attrib != ""){
						if (isset($info[$i][$ldap_login_attrib][0])) {
							$isvalid = "1";
							$uid = $info[$i][$ldap_login_attrib][0];
						} else{
							$isvalid = "0";
							$uid = '';
						}
					} else {
						if (isset($info[$i]["uid"][0])) {
							$isvalid = "1";
							$uid = $info[$i]["uid"][0];
						} else if (isset($info[$i]["samaccountname"][0])) {
							$isvalid = "1";
							$uid = $info[$i]["samaccountname"][0];
						} else if (isset($info[$i]["samaccountname"][0])) {
							$isvalid = "1";
							$uid = $info[$i]["samaccountname"][0];
						} else {
							$isvalid = "0";
							$uid = '';
						}
					}
					
					if (!isset($info[$i]["mail"][0]) )
						$isvalid = "0";
					
					$info[$i]["givenname"][0] = str_replace("'", "", $info[$i]["givenname"][0]);
					$info[$i]["givenname"][0] = str_replace("\"", "", $info[$i]["givenname"][0]);
					
					$info[$i]["cn"][0] = str_replace("'", "", $info[$i]["cn"][0]);
					$info[$i]["cn"][0] = str_replace("\"", "", $info[$i]["cn"][0]);
					
					$buffer->startElement("user");
					$buffer->writeAttribute("isvalid", $isvalid);
					$buffer->startElement("dn");
					$buffer->writeAttribute("isvalid", (isset($info[$i]["dn"]) ? "1" : "0" )); 
					$buffer->text((isset($info[$i]["dn"]) ? $info[$i]["dn"] : "" ), 1, 0);
					$buffer->endElement();
					$buffer->startElement("sn");
					$buffer->writeAttribute("isvalid", (isset($info[$i]["sn"]) ? "1" : "0" ));					
					$buffer->text((isset($info[$i]["sn"][0]) ? $info[$i]["sn"][0] : ""), 1, 0);
					$buffer->endElement();
					$buffer->startElement("givenname");
					$buffer->writeAttribute("isvalid", (isset($info[$i]["givenname"]) ? "1" : "0" ));
					$buffer->text((isset($info[$i]["givenname"][0]) ? str_replace("\'", "\\\'", $info[$i]["givenname"][0]) : "" ), 1, 0);
					$buffer->endElement();
					$buffer->startElement("mail");
					$buffer->writeAttribute("isvalid", (isset($info[$i]["mail"]) ? "1" : "0" ));
					$buffer->text((isset($info[$i]["mail"][0]) ? $info[$i]["mail"][0] : "" ), 1, 0);
					$buffer->endElement();				
					$buffer->startElement("cn");
					$buffer->writeAttribute("isvalid", (isset($info[$i]["cn"]) ? "1" : "0" ));
					$buffer->text((isset($info[$i]["cn"][0]) ? $info[$i]["cn"][0] : "" ), 1, 0);
					$buffer->endElement();
					$buffer->startElement("uid");
					$buffer->writeAttribute("isvalid", (empty($uid) ? "0" : "1" ), 1, 0);
					$buffer->text($uid, 1, 0);
					$buffer->endElement();
					$buffer->endElement();					
				}
		   	}
		   	$buffer->endElement();		   	
		} else {
			$buffer->startElement("reponse");
			$buffer->writeElement("entries", "0");
			$buffer->writeElement("error", ldap_err2str($ds));
			$buffer->endElement();			
		}
		@ldap_close($ds);
	} 

	if (isset($error)){
		$buffer->startElement("reponse");
		$buffer->writeElement("error", $error);
		$buffer->endElement();		
	}

	header('Content-Type: text/xml');

	$buffer->output();
	
	if ($debug_ldap_import == 1)
		error_log("[" . date("d/m/Y H:s") ."] LDAP Search : XML Output : ".$buffer->output()."\n", 3, $debug_path."ldapsearch.log");
?>