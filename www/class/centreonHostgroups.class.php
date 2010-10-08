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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/class/centreonHost.class.php $
 * SVN : $Id: centreonHost.class.php 10875 2010-10-04 08:40:34Z jmathis $
 *
 */

 /**
  *
  * Hostgroups objects
  * @author jmathis
  *
  */
 class CentreonHostroups {
 	private $DB;

 	/**
 	 *
 	 * Constructor
 	 * @param $pearDB
 	 */
 	function __construct($pearDB) {
 		$this->DB = $pearDB;
 	}

 	/**
 	 *
 	 * Enter description here ...
 	 * @param unknown_type $hg_id
 	 * @param unknown_type $searchHost
 	 * @param unknown_type $level
 	 */
 	public function getMyHostGroupHosts($hg_id = NULL, $searchHost = NULL, $level = 1)	{
		if (!$hg_id) {
			return;
		}

		$searchSTR = "";
		if (isset($searchHost) && $searchHost != "") {
			$searchSTR = " AND h.host_name LIKE '%$searchHost%' ";
		}

		$hosts = array();
		$DBRESULT =& $this->DB->query("SELECT hgr.host_host_id " .
									"FROM hostgroup_relation hgr, host h " .
									"WHERE hgr.hostgroup_hg_id = '".$hg_id."' " .
									"AND h.host_id = hgr.host_host_id $searchSTR " .
									"ORDER by h.host_name");
		while ($elem =& $DBRESULT->fetchRow()) {
			$hosts[$elem["host_host_id"]] = $elem["host_host_id"];
		}
		$DBRESULT->free();
		unset($elem);

		if ($level) {
			$hgHgCache = setHgHgCache($pearDB);
			$hostgroups = getMyHostGroupHostGroups($hg_id);
			if (isset($hostgroups) && count($hostgroups)) {
				foreach ($hostgroups as $hg_id2) {
					$tmp = getMyHostGroupHosts($hg_id2, "", 1);
					foreach ($tmp as $id) {
						$hosts[$id] = $id;
					}
					unset($tmp);
				}
			}

		}
		return $hosts;
	}

	/**
	 *
	 * Enter description here ...
	 */
	private function setHgHgCache() {
		$hgHgCache = array();
		$DBRESULT =& $this->DB->query("SELECT /* SQL_CACHE */ hg_parent_id, hg_child_id FROM hostgroup_hg_relation");
		while ($data =& $DBRESULT->fetchRow()) {
			if (!isset($hgHgCache[$data["hg_parent_id"]])) {
				$hgHgCache[$data["hg_parent_id"]] = array();
			}
			$hgHgCache[$data["hg_parent_id"]][$data["hg_child_id"]] = 1;
		}
		$DBRESULT->free();
		unset($data);
		return $hgHgCache;
	}

}

?>