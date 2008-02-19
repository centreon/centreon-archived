<?php
/**
Created on 23 janv. 08

Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Cedrick Facon

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


if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") ) 
{ 	
	header("Content-type: application/xhtml+xml"); }
else 
{
	header("Content-type: text/xml"); 
} 

echo("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n"); 


	/* if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml) */
	$debugXML = 0;
	$buffer = '';

	$oreonPath = '../../../../';

	/* pearDB init */
	require_once 'DB.php';

	include_once($oreonPath . "etc/centreon.conf.php");
	include_once($oreonPath . "www/DBconnect.php");
	include_once($oreonPath . "www/DBOdsConnect.php");

	/* PHP functions */
	include_once($oreonPath . "www/include/common/common-Func-ACL.php");
	include_once($oreonPath . "www/include/common/common-Func.php");

	/* Connect to oreon DB */
	$dsn = array(
		     'phptype'  => 'mysql',
		     'username' => $conf_oreon['user'],
		     'password' => $conf_oreon['password'],
		     'hostspec' => $conf_oreon['host'],
		     'database' => $conf_oreon['db'],
		     );
	$options = array(
			 'debug'       => 2,
			 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
			 );
	$pearDB =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDB)) die("Connecting problems with oreon database : " . $pearDB->getMessage());
	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);

/*
	$lcaHostByID = getLcaHostByID($pearDB);
	$lcaHostByName = getLcaHostByName($pearDB);
	$LcaHostStr = getLcaHostStr($lcaHostByID["LcaHost"]);
*/


function getAllHostgroups()
{
	global $pearDB;
	$hgs = array();
	$DBRESULT =& $pearDB->query("SELECT DISTINCT * FROM hostgroup ORDER BY `hg_name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while ($DBRESULT->fetchInto($hg))
		$hgs[$hg["hg_id"]] = $hg["hg_name"];
	return $hgs;
}

function service_has_graph($host, $service)
{
	global $pearDBO;
	if(is_numeric($host) && is_numeric($service)){
		$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_id = '".$host."' AND service_id = '".$service."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		
		if($DBRESULT->numRows() > 0)
			return true;
	}
	if(!is_numeric($host) && !is_numeric($service)){
		$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_name = '".$host."' AND service_description = '".$service."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		
		if($DBRESULT->numRows() > 0)
			return true;
	}
	return false;	
}

function host_has_one_or_more_GraphService($host_id)
{
	global $pearDBO;

	$services = getMyHostServices($host_id);
	foreach($services as $svc_id => $svc_name)
	{
		if(service_has_graph($host_id, $svc_id))
		return true;
	}
	return false;	
}

function HG_has_one_or_more_host($hg_id)
{
	global $pearDBO;

	$hosts = getMyHostGroupHosts($hg_id);
	foreach($hosts as $host_id => $host_name)
	{
		$services = getMyHostServices($host_id);
		foreach($services as $svc_id => $svc_name)
		{
			return true;
		}
	}
	return false;	
}

function getMyHostServiceID($service_id = NULL)
{
	if (!$service_id) return;
	global $pearDB;
	$DBRESULT =& $pearDB->query("SELECT host_id FROM host h,host_service_relation hsr WHERE h.host_id = hsr.host_host_id AND hsr.service_service_id = '".$service_id."' LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	if ($DBRESULT->numRows())	{
		$row =& $DBRESULT->fetchRow();
		return $row["host_id"];
	}
	return NULL;		
}


$normal_mode = 1;
if (isset($_GET["mode"]))
        $normal_mode=$_GET["mode"];
else
        $normal_mode=1;


if (isset($_GET["id"]))
        $url_var=$_GET["id"];
else
        $url_var=0;



$type = "root";
$id = "0";
if(strlen($url_var) > 1){
$id = "42";
	$type = substr($url_var, 0, 2);
	$id = substr($url_var, 3, strlen($url_var));
}



if($normal_mode)
{
	print("<tree id='".$url_var."' >");
	
	$i = 0;

	if($type == "HG") // get hosts
	{
		$hosts = getMyHostGroupHosts($id);
		foreach($hosts as $host)
		{
	        	print("<item child='1' id='HH_".$host."' text='".getMyHostName($host)."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>");
				print("</item>");
		}
	}
	else if($type == "HH") // get services for host
	{
		$services = getMyHostServices($id);
		foreach($services as $svc_id => $svc_name)
		{
		        print("<item child='0' id='HS_".$svc_id."' text='".$svc_name."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif'>");
				print("</item>");			
		}
	}
	else if($type == "HS") // get services for host
	{	
		;
	}
	else if($type == "HO") // get services for host
	{
	//	$rq2 = "SELECT DISTINCT * FROM host WHERE host_id NOT IN (select host_host_id from hostgroup_relation) order by host_name";
		$rq2 = "SELECT DISTINCT * FROM host WHERE host_id NOT IN (select host_host_id from hostgroup_relation) AND host_register = '1' order by host_name";
		$DBRESULT2 =& $pearDB->query($rq2);
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		while ($DBRESULT2->fetchInto($host)){
				$i++;
	           	print("<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>");
				print("</item>");
		}
	}
	else if($type == "RR")
	{
		$rq = "SELECT DISTINCT * FROM hostgroup ORDER BY `hg_name`";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		while ($DBRESULT->fetchInto($HG)){
				$i++;
	
			if(HG_has_one_or_more_host($HG["hg_id"])){
	        	print("<item child='1' id='HG_".$HG["hg_id"]."' text='".$HG["hg_name"]."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
				print("</item>");
			}
		}
	
		print("<item child='1' id='HO_0' text='Hosts Alone' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif' >");
		print("</item>");
	}
	else
	{
		print("<item nocheckbox='1' open='1' call='1' select='1' child='1' id='RR_0' text='All logs' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
		print("<itemtext>label</itemtext>");
		print("</item>");
	}
}
else// direct to ressource (ex: pre-selected by GET)
{
	print("<tree id='1' >");

	$hg_selected = array();
	$hosts_selected = array();
	$svc_selected = array();

	$tab_id = split(",",$url_var);
	foreach($tab_id as $openid)
	{
		$type = substr($openid, 0, 2);
		$id = substr($openid, 3, strlen($openid));




		if($type == "HH") // host + svcS_child + hg_parent
		{
			// host
			$hosts_selected[$id] = getMyHostName($id);
			
			// svcS_child
			$services = getMyHostServices($id);
			foreach($services as $svc_id => $svc_name)
				$svc_selected[$svc_id] = $svc_name;
			
			// 	hg_parent
			$hgs = getMyHostGroups($id);
			foreach($hgs as $hg_id => $hg_name)
				$hg_selected[$hg_id] = $hg_name;
		}
		else if($type == "HS"){ // svc + host_parent + hg_parent
			// svc
			$svc_selected[$id] = getMyServiceName($id);

			//host_parent
			$host_id = getMyHostServiceID($id);
			$hosts_selected[$host_id] = getMyHostName($host_id);
			
			// 	hg_parent
			$hgs = getMyHostGroups($host_id);
			foreach($hgs as $hg_id => $hg_name)
				$hg_selected[$hg_id] = $hg_name;			
		}
		else if($type == "HG"){ // HG + hostS_child + svcS_child
			$host_name = getMyHostName($id);
			array_push ($hosts_selected, "'".$host_name."'");
			/* + all svc*/
		}
	}


/*
echo "<pre>";
print_r($hg_selected);
echo "</pre>";


echo "<pre>";
print_r($hosts_selected);
echo "</pre>";

echo "<pre>";
print_r($svc_selected);
echo "</pre>";
*/

	$hostgroups = getAllHostgroups();
	foreach($hostgroups as $hg_id => $hg_name){
		/*
		 * Hostgroups
		 */
		if(HG_has_one_or_more_host($hg_id)){

			$hg_open = $hg_checked = 0;
			if(isset($hg_selected[$hg_id])){
				$hg_open = $hg_checked = 1;
        		print("<item checked='".$hg_checked."' open='".$hg_open."' child='1' id='HG_".$hg_id."' text='".$hg_name."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");

			}
			else
        		print("<item  child='1' id='HG_".$hg_id."' text='".$hg_name."' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");

			/*
			 * Hosts
			 */
			if($hg_open){
				$hosts = getMyHostGroupHosts($hg_id);
				foreach($hosts as $host_id => $host_name)
				{
						$host_checked = 0;
						$host_open = 0;
						if(isset($hosts_selected[$host_id])){
							$host_open = $host_checked = 1;
				        	print("<item checked='".$host_checked."' open='".$host_open."' child='1' id='HH_".$host_id."_".$hg_id."' text='".getMyHostName($host_id)."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>");
							
						}
						else
			        		print("<item child='1' id='HH_".$host_id."_".$hg_id."' text='".getMyHostName($host_id)."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>");

						/*
						 * Services
						 */
						if($host_open){
							$services = getMyHostServices($host_id);
							foreach($services as $svc_id => $svc_name)
							{
								$svc_checked = 0;
								if(isset($svc_selected[$svc_id])){
									$svc_checked = 1;
						        	print("<item checked='".$svc_checked."' child='0' id='HS_".$svc_id."_".$host_id."_".$hg_id."' text='".$svc_name."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif'>");
								}
								else
						        	print("<item  child='0' id='HS_".$svc_id."_".$host_id."_".$hg_id."' text='".$svc_name."' im0='../16x16/gear.gif' im1='../16x16/gear.gif' im2='../16x16/gear.gif'>");
								
								print("</item>");			
							}
						}
						print("</item>");
				}
			}

			print("</item>");



		}
	}
	
	print("<item child='1' id='HO_0' text='Hosts Alone' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif' >");
	print("</item>");

}
print("</tree>");

?>

