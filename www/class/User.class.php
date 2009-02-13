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

require_once("centreonACL.class.php");
require_once("centreonLog.class.php");

class User	{

	var $user_id;
	var $name;    
	var $alias;
	var $passwd;
	var $email;
	var $lang;
	var $version;
	var $admin;
	var $limit;
	var $num;
	var $gmt;
	var $is_admin;
	var $groupList;
	var $groupListStr;
	var $access;
	var $log;
	
	# User LCA
	# Array with elements ID for loop test
	var $lcaTopo;
	
	# String with elements ID separated by commas for DB requests
	var $lcaTStr;
	  
  	function User($user = array(), $nagios_version = NULL)  {
		global $pearDB;
		
		$this->user_id = $user["contact_id"];
		$this->name = html_entity_decode($user["contact_name"], ENT_QUOTES);
		$this->alias = html_entity_decode($user["contact_alias"], ENT_QUOTES);
		$this->email = html_entity_decode($user["contact_email"], ENT_QUOTES);
		$this->lang = $user["contact_lang"];
		$this->passwd = $user["contact_passwd"];
		$this->admin = $user["contact_admin"];
		$this->version = $nagios_version;
	  	$this->lcaTopo = array();
	  	$this->gmt = $user["contact_location"];
	  	$this->is_admin = NULL;
	  	$this->access = new CentreonACL($this->user_id, $this->admin);
	  	$this->log = new CentreonUserLog($this->user_id, $pearDB);
  	}
    
  	public function showDiv($div_name = NULL) {
  		global $pearDB;
  		
  		if (!isset($div_name))
  			return 0;
  		
  		$query = "SELECT cp_value " .
  				"FROM contact_param " .
  				"WHERE cp_contact_id = '".$this->user_id."' " .
  				"AND cp_key LIKE '_Div_".$div_name."' LIMIT 1" ;
  		$DBRESULT =& $pearDB->query($query);
  		while ($row  =& $DBRESULT->fetchRow())
  			return $row['cp_value'];  		
  		return "1";  		
  	}
  
  	function getAllTopology($pearDB){
	  	$DBRESULT =& $pearDB->query("SELECT topology_page FROM topology WHERE topology_page IS NOT NULL");	
		while ($topo =& $DBRESULT->fetchRow())
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
		$DBRESULT =& $pearDB->query("SELECT contact_admin, contact_id FROM session, contact WHERE session.session_id = '".$sid."' AND contact.contact_id = session.user_id");
		$admin =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		
		$DBRESULT =& $pearDB->query("SELECT count(*) FROM `acl_group_contacts_relations` WHERE contact_contact_id = '".$admin["contact_id"]."'");
		$admin2 =& $DBRESULT->fetchRow();
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
  	
  	/*
  	 * Init topology restriction reference for centreon
  	 * Create a liste of page who user has access
  	 */
  
  	function createLCA($pearDB = NULL)	{
	  	$have_an_lca = 0;
	  	$num = 0;
	  	$i = 0;
	   	if (!$pearDB)
	  		return; 
	  	
	  	$res1 =& $pearDB->query("SELECT acl_group_id FROM acl_group_contacts_relations WHERE acl_group_contacts_relations.contact_contact_id = '".$this->user_id."'");		
		
		if ($num = $res1->numRows())	{
			for ($str = "", $i = 0; $group = $res1->fetchRow() ; $i++)	{
				if ($str != "")
					$str .= ", ";
				$str .= $group["acl_group_id"];
			}
		}
	  	
	  	$str_topo = "";
	  	
	  	if ($this->admin || $i == 0){
			$this->lcaTopo = $this->getAllTopology($pearDB);
	  	} else {  		
			
			$DBRESULT =& $pearDB->query(	"SELECT DISTINCT acl_topology_id " .
											"FROM `acl_group_topology_relations`, `acl_topology`, `acl_topology_relations` " .
											"WHERE acl_topology_relations.acl_topo_id = acl_topology.acl_topo_id " .
											"AND acl_group_topology_relations.acl_group_id IN ($str) " .
											"AND acl_topology.acl_topo_activate = '1'");
			
			if (!$DBRESULT->numRows()){
				
				$DBRESULT2 =& $pearDB->query("SELECT topology_page FROM topology WHERE topology_page IS NOT NULL");	
				for ($str_topo = ""; $topo = $DBRESULT2->fetchRow(); )
					if (isset($topo["topology_page"]))
						$this->lcaTopo[$topo["topology_page"]] = 1;
				unset($str_topo);
				$DBRESULT2->free();
				
			} else {
				while ($topo_group = $DBRESULT->fetchRow()){
					$DBRESULT2 =& $pearDB->query(	"SELECT topology_topology_id " .
			  										"FROM `acl_topology_relations`, acl_topology " .
			  										"WHERE acl_topology_relations.acl_topo_id = '".$topo_group["acl_topology_id"]."' " .
			  												"AND acl_topology.acl_topo_activate = '1' " .
			  												"AND acl_topology.acl_topo_id = acl_topology_relations.acl_topo_id");
			  		
			  		$count = 0;
			  		while ($topo_page =& $DBRESULT2->fetchRow()){
			  			$have_an_lca = 1;
			  			if ($str_topo != "")
			  				$str_topo .= ", ";
			  			$str_topo .= $topo_page["topology_topology_id"];
			  			$count++;
			  		}
			  		$DBRESULT2->free();
		  		}
		  		unset($topo_group);
		  		unset($topo_page);
		  		$count ? $ACL = "topology_id IN ($str_topo) AND ": $ACL = "";
		  		unset($DBRESULT);
		  		$DBRESULT =& $pearDB->query("SELECT topology_page FROM topology WHERE $ACL topology_page IS NOT NULL");	
				
				while ($topo_page =& $DBRESULT->fetchRow())						
					$this->lcaTopo[$topo_page["topology_page"]] = 1;
				unset($topo_page);
				$DBRESULT->free();
				
			}
			unset($DBRESULT);
	  	}
	
	  	$this->lcaTStr = '';
	  	foreach ($this->lcaTopo as $key => $tmp){
	  		if (isset($key) && $key){
		  		if ($this->lcaTStr != "")
		  			$this->lcaTStr .= ", ";
		  		$this->lcaTStr .= $key;
	  		}
	  	}
	  	unset($key);
	  	if (!$this->lcaTStr) 
	  		$this->lcaTStr = "\'\'";  	
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
  
  function getMyGMT(){
  	return $this->gmt;
  }
} /* end class User */
?>