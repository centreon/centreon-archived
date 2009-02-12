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

	/*
	 * Declare Values
	 */
	var $login;
	var $password;
	var $enable;
	var $userExists;
	var $cryptEngine;
	var $autologin;
	var $userInfos;
	
	var $cryptPossibilities;
	
	var $pearDB;
	/*
	 * Flags
	 */
	var $passwdOk;
	var $authType;
	
	/*
	 * keep log class 
	 */
	var $CentreonLog;

	/*
	 * Error Message
	 */
	var $error;
	
	/*
	 * Constructor
	 */
    function centreonAuth($username, $password, $autologin, $pearDB, $CentreonLog, $encryptType = 1) {
    	$this->cryptPossibilities = array('MD5', 'SHA1');
    	$this->CentreonLog =& $CentreonLog;
    	$this->login = $username;
    	$this->password = $password;
    	$this->pearDB = $pearDB;
    	/*
    	 * Check User acces
    	 */
    	$this->checkUser($username, $password, $autologin, $pearDB);
    }
	    
	function checkPassword($password) {
		if ($this->userInfos["contact_auth_type"] == "ldap") {
			
			/*
			 * Insert LDAP Class
			 */
			include_once ("/usr/local/centreon/www/class/centreonAuth.LDAP.class.php");
			
			/*
			 * Create Class
			 */
			$authLDAP = new CentreonAuthLDAP($this->pearDB, $this->CentreonLog, $this->login, $this->password, $this->userInfos);
			$authLDAP->connect();
			$this->passwdOk = $authLDAP->checkPassword();
			$authLDAP->close();
			
		} else if ($this->userInfos["contact_auth_type"] == "local") {
			if ($this->userInfos["contact_passwd"] == myCrypt($password))
				$this->passwdOk = 1;
			else
				$this->passwdOk = 0;
		}
	}
	    
    function checkUser($username, $password, $autologin, $pearDB) {
    	if ($autologin == 0) {
	    	$DBRESULT =& $pearDB->query("SELECT * FROM `contact` WHERE `contact_alias` = '".htmlentities($username, ENT_QUOTES)."' AND `contact_activate` = '1' LIMIT 1");
	    	if (PEAR::isError($DBRESULT))
				$this->CentreonLog->insertLog(1, "DB Error : ".$DBRESULT->getDebugInfo(), 1);
	    	if ($DBRESULT->numRows()) {
	    		$this->userInfos =& $DBRESULT->fetchRow();
	    		if ($this->userInfos["contact_oreon"]) {
					/*
					 * Check password matching
					 */
					$this->getCryptFunction();
					$this->checkPassword($password);

					if ($this->passwdOk == 1) {
						$this->CentreonLog->setUID($this->userInfos["contact_id"]);
						$this->CentreonLog->insertLog(1, "Contact '".$username."' logged in - IP : ".$_SERVER["REMOTE_ADDR"]);
					} else {
						$this->CentreonLog->insertLog(1, "Contact '".$username."' doesn't match with password");
						$this->error = "Invalid user";	
					}
				} else {
					$this->CentreonLog->insertLog(1, "Contact '".$username."' is not enable for reaching centreon");
					$this->error = "Invalid user";
				}
	    	} else {
	    		$this->CentreonLog->insertLog(1, "No contact found with this login : '$username'");
	    		$this->error = "Invalid user";
	    	}
    	} else {
	    	/*
	    	$DBRESULT =& $pearDB->query("SELECT * FROM `contact` WHERE `contact_alias` = '".htmlentities($useralias, ENT_QUOTES)."' AND `contact_activate` = '1' LIMIT 1");
    		if (PEAR::isError($DBRESULT))
				$this->CentreonLog->insertLog(1, "DB Error : ".$DBRESULT->getDebugInfo(), 1);
	    	if ($DBRESULT->numRows()) {
	    		;	
	    	}
	    	*/
	    	;
	    }
    }
    
    function checkPasswd($username, $password, $Crypt) {
    	$DBRESULT =& $pearDB->query("SELECT * FROM `contact` WHERE `contact_alias` = '".htmlentities($useralias, ENT_QUOTES)."' AND `contact_password` = '".htmlentities($password, ENT_QUOTES)."' LIMIT 1");
    	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
    	$this->$DBRESULT->fetchRow();
    }

	/*
     * Check crypt system
     */

    function getCryptFunction() {
  		switch ($this->userInfos["contact_crypt"]) {
  			case 1 : 
  				return "MD5";
  				break;
  			case 2 : 
  				return "SHA1";
  				break;
  			default : 
  				return "MD5";
  				break;
  		}
  	}

	/*
	 * Crypt String
	 */
    
    function myCrypt($str) {
  		switch ($this->cryptEngine) {
  			case 1 : 
  				return md5($str);
  				break;
  			case 2 : 
  				return sha1($str);
  				break;
  			default : 
  				return md5($str);
  				break;
  		}
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