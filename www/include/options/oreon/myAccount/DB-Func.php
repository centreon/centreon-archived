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

	function testExistence ($name = NULL)	{
		global $pearDB, $form, $oreon;
		
		$DBRESULT = $pearDB->query("SELECT contact_name, contact_id FROM contact WHERE contact_name = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
		$contact = $DBRESULT->fetchRow();
		/*
		 * Modif case
		 */
		if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $oreon->user->get_id())	
			return true;
		
		/*
		 * Duplicate entry
		 */
		else if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $oreon->user->get_id())
			return false;
		else
			return true;
	}	
	
	function testAliasExistence ($alias = NULL)	{
		global $pearDB, $form, $oreon;
		
		$DBRESULT = $pearDB->query("SELECT contact_alias, contact_id FROM contact WHERE contact_alias = '".htmlentities($alias, ENT_QUOTES, "UTF-8")."'");
		$contact = $DBRESULT->fetchRow();
		
		/*
		 * Modif case
		 */
		if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $oreon->user->get_id())	
			return true;
		/*
		 * Duplicate entry
		 */
		else if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $oreon->user->get_id())
			return false;
		else
			return true;
	}
	
	function updateContactInDB ($contact_id = NULL)	{
		if (!$contact_id) 
			return;
		updateContact($contact_id);
	}
	
	function updateContact($contact_id = null)	{
		global $form, $pearDB, $oreon, $encryptType;
		
		if (!$contact_id) 
			return;
		
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE contact SET ";
		$rq .= "contact_name = ";
		isset($ret["contact_name"]) && $ret["contact_name"] != NULL ? $rq .= "'".htmlentities($ret["contact_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "contact_alias = ";
		isset($ret["contact_alias"]) && $ret["contact_alias"] != NULL ? $rq .= "'".htmlentities($ret["contact_alias"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		
		if (isset($ret["contact_passwd"]) && $ret["contact_passwd"]) {
			if ($encryptType == 1)
				$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";	
			else if ($encryptType == 2)
				$rq .= "contact_passwd = '".sha1($ret["contact_passwd"])."', ";	
			else
				$rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";				
		}
			
		$rq .=	"contact_lang = ";
		isset($ret["contact_lang"]) && $ret["contact_lang"] != NULL ? $rq .= "'".htmlentities($ret["contact_lang"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "contact_email = ";
		isset($ret["contact_email"]) && $ret["contact_email"] != NULL ? $rq .= "'".htmlentities($ret["contact_email"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "contact_pager = ";
		isset($ret["contact_pager"]) && $ret["contact_pager"] != NULL ? $rq .= "'".htmlentities($ret["contact_pager"], ENT_QUOTES, "UTF-8")."' ": $rq .= "NULL ";
		$rq .= "WHERE contact_id = '".$contact_id."'";
		$DBRESULT = $pearDB->query($rq);
		
		/*
		 * Update user object..
		 */
		$oreon->user->name = $ret["contact_name"];
		$oreon->user->alias = $ret["contact_alias"];
		$oreon->user->lang = $ret["contact_lang"];
		$oreon->user->email = $ret["contact_email"];
	}
?>