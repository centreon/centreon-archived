<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * SVN : $URL
 * SVN : $Id
 * 
 */
 
class centreonAuth {
	var $login;
	var $password;
	var $enable;
	var $userExists;
	var $cryptEngine;
	var $autologin;
	var $userInfos;
	
	/*
	 * Flags
	 */
	
	var $passwdOk;
	var $authType;
	
    function centreonAuth($username, $password, $autologin, $encryptType = 0) {
    	$this->userInfos = getUserInfos($username, $password, $autologin, $encryptType);
    	if (count($this->userInfos))
    		$this->userExists = 1;
    	//$cryptEngine = $userInfos["Crypt"];
    	$this->cryptEngine = $this->userInfos["contact_auth_type"];
    	
    }
    
    function getUserInfos($username, $password, $autologin, $encryptType) {
    	if ($autologin == 0) {
	    	$DBRESULT =& $pearDB->query("SELECT * FROM `contact` WHERE `contact_alias` = '".htmlentities($useralias, ENT_QUOTES)."' LIMIT 1");
	    	if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	    	$this->$DBRESULT->fetchRow();
    	} else {
    		switch ($autologin)	{
    			case 0 : ;
    			case 1 : ;
    			default : ;
    		}
    	}
    }
    
    function checkPasswd($username, $password, $Crypt) {
    	$DBRESULT =& $pearDB->query("SELECT * FROM `contact` WHERE `contact_alias` = '".htmlentities($useralias, ENT_QUOTES)."' AND `contact_password` = '".htmlentities($password, ENT_QUOTES)."' LIMIT 1");
    	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
    	$this->$DBRESULT->fetchRow();
    }

    function getCryptEngine() {
    	return $this->cryptEngine;
    }
    
    function userExists() {
    	return $this->userExists;
    }
    
    function userIsEnable() {
    	return $this->enable;
    }
    
    function passwordIsOk() {
    	return $this->passwdOk;
    }

	function getAuthType() {
		return $this->authType;
	}

}
?>