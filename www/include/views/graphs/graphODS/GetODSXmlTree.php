<?php
/**
Created on 3 janv. 08

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

	$oreonPath = '../../../../../';

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
			if(service_has_graph($host_id, $svc_id))
			return true;
		}
	}
	return false;	
}



if (isset($_GET["id"]))
        $url_var=$_GET["id"];
else
        $url_var=0;

if (isset($_GET["openid"]))
        $openid=$_GET["openid"];
else
        $openid=0;


$is_open = array();

$is_open["HG"] = -1;
$is_open["HH"] = -1;
$is_open["SS"] = -1;

$_type = substr($openid, 0, 2);
$_id = substr($openid, 3, strlen($openid));

if($_type == "HG")
	$is_open["HG"] = $_id;

else if($_type == "HH")
{
	$rq = "SELECT DISTINCT * FROM hostgroup ORDER BY `hg_name`";
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while ($DBRESULT->fetchInto($HG)){
			$hosts = getMyHostGroupHosts($HG["hg_id"]);
			foreach($hosts as $h_id)
			{
				if($h_id == $_id)
					{
						$is_open["HG"] = $HG["hg_id"];
						$is_open["HH"] = $_id;									
					}
			}
	}
}



$type = "root";
$id = "0";
if(strlen($url_var) > 1){
$id = "42";
	$type = substr($url_var, 0, 2);
	$id = substr($url_var, 3, strlen($url_var));
}

print("<tree id='".$url_var."' >");

$i = 0;

if($type == "HG") // get hosts
{
	$hosts = getMyHostGroupHosts($id);
	foreach($hosts as $host)
	{
		if(host_has_one_or_more_GraphService($host)){
        	print("<item child='1' id='HH_".$host."' text='".getMyHostName($host)."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>");
			print("</item>");
		}
	}
}
else if($type == "HH") // get services for host
{
	$services = getMyHostServices($id);
	foreach($services as $svc_id => $svc_name)
	{
		if(service_has_graph($id,$svc_id)){
	        print("<item child='1' id='HS_".$svc_id."' text='".$svc_name."' im0='../16x16/element_new_after.gif' im1='../16x16/element_new_after.gif' im2='../16x16/element_new_after.gif'>");
			print("</item>");			
		}
	}
}
else if($type == "HS") // get services for host
{	
	;
}
else if($type == "HO") // get services for host
{
	$rq2 = "SELECT DISTINCT * FROM host WHERE host_id NOT IN (select host_host_id from hostgroup_relation) order by host_name";
	$DBRESULT2 =& $pearDB->query($rq2);
	if (PEAR::isError($DBRESULT2))
		print "Mysql Error : ".$DBRESULT2->getDebugInfo();
	while ($DBRESULT2->fetchInto($host)){
			$i++;
		if(host_has_one_or_more_GraphService($host["host_id"])){
           	print("<item child='1' id='HH_".$host["host_id"]."' text='".$host["host_name"]."' im0='../16x16/server_network.gif' im1='../16x16/server_network.gif' im2='../16x16/server_network.gif'>");
			print("</item>");
		}
	}
}
else
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

	print("<item child='1' id='HO_0' text='Others' im0='../16x16/clients.gif' im1='../16x16/clients.gif' im2='../16x16/clients.gif' >");
	print("</item>");


	
}


print("</tree>");


?>
