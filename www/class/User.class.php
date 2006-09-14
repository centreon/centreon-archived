<?
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

class User	{

  var $user_id;
  var $name;    
  var $alias;
  var $passwd;
  var $email;
  var $lang;
  var $version;
  var $admin;
  
  ## User LCA
  # Array with elements ID for loop test
  var $lcaTopo;
  # String with elements ID separated by commas for DB requests
  var $lcaTStr;
  
  function User($user = array(), $nagios_version = NULL)  {
	$this->user_id = $user["contact_id"];
	$this->name = html_entity_decode($user["contact_name"], ENT_QUOTES);
	$this->alias = html_entity_decode($user["contact_alias"], ENT_QUOTES);
	$this->email = html_entity_decode($user["contact_email"], ENT_QUOTES);
	$this->lang = $user["contact_lang"];
	$this->passwd = $user["contact_passwd"];
	$this->admin = $user["contact_admin"];
	$this->version = $nagios_version;
  	$this->lcaTopo = array();
  }
  
  function createLCA($pearDB = NULL)	{
  	$have_an_lca = 0;
  	$num = 0;
   	if(!$pearDB)
  		return; 
  	if ($this->admin){
	  	$res3 =& $pearDB->query("SELECT topology_id FROM topology");	
		while ($res3->fetchInto($topo))
			$this->lcaTopo[$topo["topology_id"]] = $topo["topology_id"];			
		unset($res3);
  	} else {
  		$res1 =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$this->user_id."'");
		if ($num = $res1->numRows())	{
			while($res1->fetchInto($contactGroup))	{
			 	$res2 =& $pearDB->query("SELECT lca.lca_id, lca.lca_hg_childs FROM lca_define_contactgroup_relation ldcgr, lca_define lca WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."' AND ldcgr.lca_define_lca_id = lca.lca_id AND lca.lca_activate = '1'");	
				 if ($res2->numRows())	{
					$have_an_lca = 1;
					while ($res2->fetchInto($lca))	{
  						$res3 =& $pearDB->query("SELECT topology_topology_id FROM lca_define_topology_relation WHERE lca_define_lca_id = '".$lca["lca_id"]."'");	
						while ($res3->fetchInto($topo))
							$this->lcaTopo[$topo["topology_topology_id"]] = $topo["topology_topology_id"];
						unset($res3);
					}
				}
			}
		}
  	}
  	if (!$num || !$have_an_lca) {
		$res3 =& $pearDB->query("SELECT topology_id FROM topology");	
		while ($res3->fetchInto($topo))
			$this->lcaTopo[$topo["topology_id"]] = $topo["topology_id"];			
		unset($res3);			
	}
  	$this->lcaTStr = NULL;
  	foreach ($this->lcaTopo as $tmp)
  		$this->lcaTStr ? $this->lcaTStr .= ", ".$tmp : $this->lcaTStr = $tmp;
  	if (!$this->lcaTStr) $this->lcaTStr = '\'\'';
  	
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
  
} /* end class User */
?>
