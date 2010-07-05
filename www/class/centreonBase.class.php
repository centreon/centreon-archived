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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/class/centreonXMLBGRequest.class.php $
 * SVN : $Id: centreon.class.php 9656 2010-01-04 09:05:23Z jmathis $
 *
 */


/*
 * Class for request
 *
 */
class CentreonBase
{
	/*
	 * Objects
	 */
	var $DB;
	var $DBC;

	var $GMT;

	var $hostObj;
	var $serviceObj;

	var $session_id;

	/*
	 * Variables
	 */
	var $debug;
	var $compress;
	var $user_id;
	var $general_opt;

	/*
	 * Class constructor
	 *
	 * <code>
	 * $obj = new CentreonBGRequest($_GET["session_id"], 1, 1, 0, 1);
	 * </code>
	 *
	 * $session_id 	char 	session id
	 * $dbneeds		bool 	flag for enable ndo connexion
	 * $headType	bool 	send XML header
	 * $debug		bool 	debug flag.
	 */
	function CentreonBase($session_id, $index, $debug, $compress = null)
	{
		if (!isset($debug)) {
			$this->debug = 0;
		}

		(!isset($compress)) ? $this->compress = 1 : $this->compress = $compress;

		if (!isset($session_id)) {
			print "Your must check your session id";
			exit(1);
		} else {
			$this->session_id = htmlentities($session_id, ENT_QUOTES);
		}

		$this->index = htmlentities($index, ENT_QUOTES);

		/*
		 * Enable Database Connexions
		 */
		$this->DB 		= new CentreonDB();
		$this->DBC 		= new CentreonDB("centstorage");

		/*
		 * Init Objects
		 */
		$this->hostObj		= new CentreonHost($this->DB);
		$this->serviceObj	= new CentreonService($this->DB);

		/*
		 * Timezone management
		 */
		$this->GMT = new CentreonGMT($this->DB);
		$this->GMT->getMyGMTFromSession($this->session_id, $this->DB);
	}

	/*
	 * Set General options
	 */
	public function setGeneralOption($options)
	{
		$this->general_opt = $options;
	}

	/*
	 * Get user id from session_id
	 */
	private function getUserIdFromSID()
	{
		$DBRESULT =& $this->DB->query("SELECT user_id FROM session WHERE session_id = '".$this->session_id."' LIMIT 1");
		$admin =& $DBRESULT->fetchRow();
		unset($DBRESULT);
		if (isset($admin["user_id"])) {
			$this->user_id = $admin["user_id"];
		}
	}
}
?>