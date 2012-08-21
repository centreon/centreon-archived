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

require_once ("centreonACL.class.php");
require_once ("centreonLog.class.php");

class CentreonUser	{

	var $user_id;
	var $name;
	var $alias;
	var $passwd;
	var $email;
	var $lang;
	var $charset;
	var $version;
	var $admin;
	var $limit;
    var $js_effects;
	var $num;
	var $gmt;
	var $is_admin;
	var $groupList;
	var $groupListStr;
	var $access;
	var $log;
	var $userCrypted;

	# User LCA
	# Array with elements ID for loop test
	var $lcaTopo;

	# String with elements ID separated by commas for DB requests
	var $lcaTStr;

  	function CentreonUser($user = array())  {
		global $pearDB;

		$this->user_id = $user["contact_id"];
		$this->name = html_entity_decode($user["contact_name"], ENT_QUOTES, "UTF-8");
		$this->alias = html_entity_decode($user["contact_alias"], ENT_QUOTES, "UTF-8");
		$this->email = html_entity_decode($user["contact_email"], ENT_QUOTES, "UTF-8");
		$this->lang = $user["contact_lang"];
		$this->charset = "UTF-8";
		$this->passwd = $user["contact_passwd"];
		$this->admin = $user["contact_admin"];
		$this->version = 3;
	  	$this->gmt = $user["contact_location"];
        $this->js_effects = $user["contact_js_effects"];
	  	$this->is_admin = NULL;
	  	/*
	  	 * Initiate ACL
	  	 */
	  	$this->access = new CentreonACL($this->user_id, $this->admin);
	  	$this->lcaTopo = $this->access->topology;
	  	$this->lcaTStr = $this->access->topologyStr;
	  	/*
	  	 * Initiate Log Class
	  	 */
	  	$this->log = new CentreonUserLog($this->user_id, $pearDB);
  		$this->userCrypted = md5($this->alias);
  	}

  	public function showDiv($div_name = NULL) {
  		global $pearDB;

  		if (!isset($div_name))
  			return 0;

  		if (isset($_SESSION['_Div_' . $div_name])) {
  			return $_SESSION['_Div_' . $div_name];
  		}
  		return 1;
  	}

  	function getAllTopology($pearDB){
	  	$DBRESULT = $pearDB->query("SELECT topology_page FROM topology WHERE topology_page IS NOT NULL");
		while ($topo = $DBRESULT->fetchRow())
			if (isset($topo["topology_page"]))
				$lcaTopo[$topo["topology_page"]] = 1;
		unset($topo);
		$DBRESULT->free();
		return $lcaTopo;
  	}

  	/*
  	 * Check if user is admin or had ACL
  	 */

  	function checkUserStatus($sid = NULL, $pearDB){
		$DBRESULT = $pearDB->query("SELECT contact_admin, contact_id FROM session, contact WHERE session.session_id = '".$sid."' AND contact.contact_id = session.user_id AND contact.contact_register = '1'");
		$admin = $DBRESULT->fetchRow();
		$DBRESULT->free();

		$DBRESULT = $pearDB->query("SELECT count(*) FROM `acl_group_contacts_relations` WHERE contact_contact_id = '".$admin["contact_id"]."'");
		$admin2 = $DBRESULT->fetchRow();
		$DBRESULT->free();

		if ($admin["contact_admin"]){
			unset($admin);
			$this->is_admin = 1 ;
		} else if (!$admin2["count(*)"]) {
			unset($admin2);
			$this->is_admin = 1;
		}
		$this->is_admin = 0;
	}

  // Get

  function get_id(){
  	return $this->user_id;
  }

  function get_name(){
  	return $this->name;
  }

  function get_email(){
  	return $this->email;
  }

  function get_alias(){
  	return $this->alias;
  }

  function get_version()	{
  	return $this->version;
  }

  function get_lang(){
  	return $this->lang;
  }

  function get_passwd(){
  	return $this->passwd;
  }

  function get_admin(){
  	return $this->admin;
  }

  function is_admin(){
  	return $this->is_admin;
  }

  function get_js_effects(){
    return $this->js_effects;
  }
  
  // Set

  function set_id($id)	{
  	$this->user_id = $id;
  }

  function set_name($name)	{
  	$this->name = $name;
  }

  function set_email($email)	{
  	$this->email = $email;
  }

  function set_lang($lang)	{
  	$this->lang = $lang;
  }

  function set_alias($alias)	{
  	$this->alias = $alias;
  }

  function set_version($version)	{
  	$this->version = $version;
  }
  
  function set_js_effects($js_effects){
    $this->js_effects = $js_effects;
  }

  function getMyGMT(){
  	return $this->gmt;
  }

  /**
   * Get User List
   *
   * @return array
   */
  public function getUserList($db)
  {
      static $userList;

      if (!isset($userList)) {
        $userList = array();
        $res = $db->query("SELECT contact_id, contact_name
        				  FROM contact
        				  WHERE contact_register = '1'
        				  AND contact_activate = '1'
        				  ORDER BY contact_name");
        while ($row = $res->fetchRow()) {
            $userList[$row['contact_id']] = $row['contact_name'];
        }
      }
      return $userList;
  }

  /**
   * Get Contact Name
   *
   * @param int $userId
   * @param CentreonDB $db
   * @return string
   */
  public function getContactName($db, $userId)
  {
      static $userNames;

      if (!isset($userNames)) {
          $userNames = array();
          $res = $db->query("SELECT contact_name, contact_id FROM contact");
          while ($row = $res->fetchRow()) {
            $userNames[$row['contact_id']] = $row['contact_name'];
          }
      }
      if (isset($userNames[$userId])) {
        return $userNames[$userId];
      }
      return null;
  }
} /* end class User */
?>