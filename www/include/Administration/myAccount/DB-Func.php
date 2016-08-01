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

function testExistence($name = null)
{
    global $pearDB, $form, $centreon;
    
    $DBRESULT = $pearDB->query("SELECT contact_name, contact_id FROM contact WHERE contact_name = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
    $contact = $DBRESULT->fetchRow();
    /*
	 * Modif case
	 */
    if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $centreon->user->get_id()) {
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $centreon->user->get_id()) {
        /*
		 * Duplicate entry
		 */
        return false;
    } else {
        return true;
    }
}

function testAliasExistence($alias = null)
{
    global $pearDB, $form, $centreon;
    
    $DBRESULT = $pearDB->query("SELECT contact_alias, contact_id FROM contact WHERE contact_alias = '".htmlentities($alias, ENT_QUOTES, "UTF-8")."'");
    $contact = $DBRESULT->fetchRow();
    
    /*
	 * Modif case
	 */
    if ($DBRESULT->numRows() >= 1 && $contact["contact_id"] == $centreon->user->get_id()) {
        return true;
    } elseif ($DBRESULT->numRows() >= 1 && $contact["contact_id"] != $centreon->user->get_id()) {
        /*
		 * Duplicate entry
		 */
        return false;
    } else {
        return true;
    }
}

function updateNotificationOptions($contact_id)
{
    global $form, $pearDB;

    $pearDB->query("DELETE FROM contact_param 
        WHERE cp_contact_id = ".$pearDB->escape($contact_id)."
        AND cp_key LIKE 'monitoring%notification%'");
    $data = $form->getSubmitValues();
    foreach ($data as $k => $v) {
        if (preg_match("/^monitoring_(host|svc)_notification/", $k)) {
            $pearDB->query("INSERT INTO contact_param (cp_key, cp_value, cp_contact_id) VALUES ('".$pearDB->escape($k)."', '1', ".$pearDB->escape($contact_id).")");
        } elseif (preg_match("/^monitoring_sound/", $k)) {
            $pearDB->query("INSERT INTO contact_param (cp_key, cp_value, cp_contact_id) VALUES ('".$pearDB->escape($k)."', '".$pearDB->escape($v)."', ".$pearDB->escape($contact_id).")");
        }
    }
    unset($_SESSION['centreon_notification_preferences']);
}

function updateContactInDB($contact_id = null)
{
    if (!$contact_id) {
        return;
    }
    updateContact($contact_id);
    updateNotificationOptions($contact_id);
}

function updateContact($contact_id = null)
{
    global $form, $pearDB, $centreon, $encryptType;
    
    if (!$contact_id) {
        return;
    }
    
    $ret = array();
    $ret = $form->getSubmitValues();
    $rq = "UPDATE contact SET ";
    $rq .= "contact_name = ";
    isset($ret["contact_name"]) && $ret["contact_name"] != null ? $rq .= "'".htmlentities($ret["contact_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .= "contact_alias = ";
    isset($ret["contact_alias"]) && $ret["contact_alias"] != null ? $rq .= "'".htmlentities($ret["contact_alias"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    
    if (isset($ret["contact_passwd"]) && $ret["contact_passwd"]) {
        if ($encryptType == 1) {
            $rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";
        } elseif ($encryptType == 2) {
            $rq .= "contact_passwd = '".sha1($ret["contact_passwd"])."', ";
        } else {
            $rq .= "contact_passwd = '".md5($ret["contact_passwd"])."', ";
        }
    }
    
    $rq .=  "contact_location = ";
    isset($ret["contact_location"]) && $ret["contact_location"] != null ? $rq .= "'".htmlentities($ret["contact_location"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .=  "contact_lang = ";
    isset($ret["contact_lang"]) && $ret["contact_lang"] != null ? $rq .= "'".htmlentities($ret["contact_lang"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .= "contact_email = ";
    isset($ret["contact_email"]) && $ret["contact_email"] != null ? $rq .= "'".htmlentities($ret["contact_email"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .= "contact_pager = ";
    isset($ret["contact_pager"]) && $ret["contact_pager"] != null ? $rq .= "'".htmlentities($ret["contact_pager"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .=  "default_page = ";
    isset($ret["default_page"]) && $ret["default_page"] != null ? $rq .= "'".htmlentities($ret["default_page"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
    $rq .= "contact_js_effects = ";
    isset($ret["contact_js_effects"]) ? $rq .= "'1', ": $rq .= "'0', ";
    $rq .= "contact_autologin_key = ";
    $rq .= isset($ret["contact_autologin_key"]) ? "'".$pearDB->escape($ret['contact_autologin_key'])."'" : "''";
    $rq .= "WHERE contact_id = '".$contact_id."'";
    $DBRESULT = $pearDB->query($rq);
    
    /*
	 * Update user object..
	 */
    $centreon->user->name = $ret["contact_name"];
    $centreon->user->alias = $ret["contact_alias"];
    $centreon->user->lang = $ret["contact_lang"];
    $centreon->user->email = $ret["contact_email"];
    $centreon->user->setToken(isset($ret["contact_autologin_key"]) ? $ret['contact_autologin_key'] : "''");
}
