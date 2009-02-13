<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	function testExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		global $oreon;
		$DBRESULT =& $pearDB->query("SELECT contact_name, contact_id FROM contact WHERE contact_name = '".htmlentities($name, ENT_QUOTES)."'");
		$contact =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $oreon->user->get_id())	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $oreon->user->get_id())
			return false;
		else
			return true;
	}	
	
	function testAliasExistence ($alias = NULL)	{
		global $pearDB;
		global $form;
		global $oreon;
		$DBRESULT =& $pearDB->query("SELECT contact_alias, contact_id FROM contact WHERE contact_alias = '".htmlentities($alias, ENT_QUOTES)."'");
		$contact =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $oreon->user->get_id())	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $oreon->user->get_id())
			return false;
		else
			return true;
	}
	
	function updateContactInDB ($contact_id = NULL)	{
		if (!$contact_id) return;
		updateContact($contact_id);
	}
	
	function updateContact($contact_id = null)	{
		if (!$contact_id) return;
		global $form;
		global $pearDB;
		global $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE contact SET ";
		$rq .= "contact_name = ";
		isset($ret["contact_name"]) && $ret["contact_name"] != NULL ? $rq .= "'".htmlentities($ret["contact_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "contact_alias = ";
		isset($ret["contact_alias"]) && $ret["contact_alias"] != NULL ? $rq .= "'".htmlentities($ret["contact_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		if (isset($ret["contact_passwd"]) && $ret["contact_passwd"])
			$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";
		$rq .=	"contact_lang = ";
		isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL ? $rq .= "'".htmlentities($ret["contact_lang"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "contact_email = ";
		isset($ret["contact_email"]) && $ret["contact_email"] != NULL ? $rq .= "'".htmlentities($ret["contact_email"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "contact_pager = ";
		isset($ret["contact_pager"]) && $ret["contact_pager"] != NULL ? $rq .= "'".htmlentities($ret["contact_pager"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE contact_id = '".$contact_id."'";
		$DBRESULT =& $pearDB->query($rq);
		$oreon->user->name = $ret["contact_name"];
		$oreon->user->alias = $ret["contact_alias"];
		$oreon->user->lang = $ret["contact_lang"];
		$oreon->user->email = $ret["contact_email"];
	}
?>