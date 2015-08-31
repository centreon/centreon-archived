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
	
        function updateNotificationOptions($contact_id) {
            global $form, $pearDB;

            $pearDB->query("DELETE FROM contact_param 
                WHERE cp_contact_id = ".$pearDB->escape($contact_id)."
                AND cp_key LIKE 'monitoring%notification%'");
            $data = $form->getSubmitValues();
            foreach ($data as $k => $v) {
                if (preg_match("/^monitoring_(host|svc)_notification/", $k)) {
                        $pearDB->query("INSERT INTO contact_param (cp_key, cp_value, cp_contact_id) 
                            VALUES ('".$pearDB->escape($k)."', '1', ".$pearDB->escape($contact_id).")");
                } elseif (preg_match("/^monitoring_sound/", $k)) {
                        $pearDB->query("INSERT INTO contact_param (cp_key, cp_value, cp_contact_id) 
                            VALUES ('".$pearDB->escape($k)."', '".$pearDB->escape($v)."', ".$pearDB->escape($contact_id).")");
                }
            }
            unset($_SESSION['centreon_notification_preferences']);
        }

	function updateContactInDB ($contact_id = NULL)	{
		if (!$contact_id) 
			return;
		updateContact($contact_id);
                updateNotificationOptions($contact_id);
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
		isset($ret["contact_pager"]) && $ret["contact_pager"] != NULL ? $rq .= "'".htmlentities($ret["contact_pager"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "contact_js_effects = ";
		isset($ret["contact_js_effects"]) ? $rq .= "'1', ": $rq .= "'0', ";
                $rq .= "contact_autologin_key = ";
                $rq .= isset($ret["contact_autologin_key"]) ? "'".$pearDB->escape($ret['contact_autologin_key'])."'" : "''";
		$rq .= "WHERE contact_id = '".$contact_id."'";
		$DBRESULT = $pearDB->query($rq);
		
		/*
		 * Update user object..
		 */
		$oreon->user->name = $ret["contact_name"];
		$oreon->user->alias = $ret["contact_alias"];
		$oreon->user->lang = $ret["contact_lang"];
		$oreon->user->email = $ret["contact_email"];
                $oreon->user->setToken(isset($ret["contact_autologin_key"]) ? $ret['contact_autologin_key'] : "''");
	}
?>
