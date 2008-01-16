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

	function get_error($motif){
		$buffer = null;
		$buffer .= '<reponse>';
		$buffer .= $motif;
		$buffer .= '</reponse>';
		header('Content-Type: text/xml');
		echo $buffer;
		exit(0);
	}

	function check_injection(){
		if ( eregi("(<|>|;|UNION|ALL|OR|AND|ORDER|SELECT|WHERE)", $_GET["sid"])) {
			get_error('sql injection detected');
			return 1;
		}
		return 0;
	}


if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") ) 
{ 	
	header("Content-type: application/xhtml+xml"); }
else 
{
	header("Content-type: text/xml"); 
} 

echo("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n"); 



# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
$debugXML = 0;
$buffer = '';

$oreonPath = '../../../../../';

# pearDB init
require_once 'DB.php';

#PHP functions
include_once($oreonPath . "etc/centreon.conf.php");
include_once($oreonPath . "www/DBconnect.php");
include_once($oreonPath . "www/DBOdsConnect.php");

include_once($oreonPath . "www/include/common/common-Func-ACL.php");
include_once($oreonPath . "www/include/common/common-Func.php");


	function getMyHostIDService($svc_id = NULL)	{
		if (!$svc_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host h, host_service_relation hs WHERE h.host_id = hs.host_host_id AND hs.service_service_id = '".$svc_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["host_id"];
		}
		return NULL;
	}


/* Connect to oreon DB */
/*
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
*/
/*
	$lcaHostByID = getLcaHostByID($pearDB);
	$lcaHostByName = getLcaHostByName($pearDB);
	$LcaHostStr = getLcaHostStr($lcaHostByID["LcaHost"]);
*/



	if(isset($_GET["id"]) && !check_injection($_GET["id"])){
		$openid = htmlentities($_GET["id"]);
	}else
		$openid = "-1";


	$id = substr($openid, 3, strlen($openid));
	$type = substr($openid, 0, 2);


	if(isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = htmlentities($_GET["sid"]);
	}else
		$sid = "-1";


//$oreon->user->user_id
$contact_id = '1';

$period = 86400;
	if (!isset($start) && !isset($end)){
		$start = time() - ($period + 30);
		$end = time() + 10;
	}			






if($type == "HH"){





	# Verify if template exists
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();

/*
	if (!$DBRESULT->numRows())
		print "<div class='msg' align='center'>".$lang["no_graphtpl"]."</div>";	
*/

		
	if ($id >= 0){
//		if (!$isRestreint || ($isRestreint && ((isset($_GET["host_name"]) && isset($lcaHostByName["LcaHost"][$_GET["host_name"]]))||(isset($_GET["host_id"]) && isset($lcaHostByName["LcaHost"][$_GET["host_id"]]))))) {
			# Init variable in the page
			$label = NULL;
			
			$elem = array();

			$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` WHERE host_id = '".$id."' AND `trashed` = '0' ORDER BY service_description");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			
			while ($DBRESULT->fetchInto($index_data)){
				
				$template_id = getDefaultGraph($index_data["service_id"], 1);
				$DBRESULT2 =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
				$DBRESULT2->fetchInto($GraphTemplate);
						
				$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name FROM index_data WHERE host_id = '".$id."' AND service_description = '".$index_data["service_description"]."' ORDER BY `service_description`");	
				if (PEAR::isError($DBRESULT2))
					print "Mysql Error : ".$DBRESULT2->getDebugInfo();
				$DBRESULT2->fetchInto($svc_id);
				$service_id = $svc_id["service_id"];
				$index_id = $svc_id["id"];
				$host_name = $svc_id["host_name"];
				
				if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
					$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
					if (PEAR::isError($DBRESULT_meta))
						print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
					$DBRESULT_meta->fetchInto($meta);
					$svc_id["service_description"] = $meta["meta_name"];
				}
				
				$elem[$index_id] = array("service_id" => $service_id ,"index_id" => $index_id, "service_description" => str_replace("#S#", "/", str_replace("#BS#", "\\", $svc_id["service_description"])));
				


//				$DBRESULT_view =& $pearDB->query("SELECT `metric_id` FROM `ods_view_details` WHERE `index_id` = '".$index_id."' AND `contact_id` = '".$contact_id."'");
				$DBRESULT_view =& $pearDB->query("SELECT `metric_id` FROM `ods_view_details` WHERE `index_id` = '".$index_id."' AND `contact_id` = '".$contact_id."'");
				if (PEAR::isError($DBRESULT_view))
					print "Mysql Error : ".$DBRESULT_view->getDebugInfo();
					while ($metric_activate = $DBRESULT_view->fetchRow())
					$metrics_activate[$metric_activate["metric_id"]] = $metric_activate["metric_id"];
				
				/*
//				if ($GraphTemplate["split_component"]){
					$elem[$index_id]["split"] = 1;
					$elem[$index_id]["metrics"] = array();
//				}

 */
				$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index_id."' ORDER BY `metric_name`");
				if (PEAR::isError($DBRESULT2))
					print "Mysql Error : ".$DBRESULT2->getDebugInfo();
				while ($DBRESULT2->fetchInto($metrics_ret)){	
					$metrics[$metrics_ret["metric_id"]] = $metrics_ret;
					if (isset($elem[$index_id]["split"]))
						#if (!isset($metrics_activate) || (isset($metrics_activate) && isset($metrics_activate[$metrics_ret["metric_id"]]) && $metrics_activate[$metrics_ret["metric_id"]])){
							$elem[$index_id]["metrics"][$metrics_ret["metric_id"]] = $metrics_ret["metric_id"];	
						#}
				}
				
				# Create period
				if (isset($_GET["start"]) && isset($_GET["end"]) && $_GET["start"] && $_GET["end"]){
					preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["start"], $matches);
					$start = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3], 1) ;
					preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["end"], $matches);
					$end = mktime("23", "59", "59", $matches[1], $matches[2], $matches[3], 1)  + 10;
				} else if (!isset($_GET["period"]) || (isset($_GET["period"]) && !$_GET["period"])){
					if (!isset($graph["graph_id"]))
						$period = 86400;
					else {
						$DBRESULT2 =& $pearDB->query("SELECT period FROM giv_graphs_template WHERE graph_id = '".$graph["graph_id"]."'");
						if (PEAR::isError($DBRESULT2))
							print "Mysql Error : ".$DBRESULT2->getDebugInfo();
						$DBRESULT2->fetchInto($graph);
						$period = $graph["period"];
					}
				} else if ($_GET["period"])
					$period = $_GET["period"];
				
				if (!isset($start) && !isset($end)){
					$start = time() - ($period + 30);
					$end = time() + 10;
				}			
	
				if (isset($_GET["template_id"]))
					$elem[$index_id]['template_id'] = $_GET["template_id"];	
				unset($metrics_activate);
			}
		}

echo "<host>";
echo "<sid>".$sid."</sid>";
echo "<start>".$start."</start>";
echo "<end>".$end."</end>";
echo "<id>".$id."</id>";
echo "<opid>".$openid."</opid>";



foreach($elem as $svc)
{

	echo "<svc>";
	echo "<name>".$svc["service_description"]."</name>";
	echo "<index>".$svc["index_id"]."</index>";
	echo "<service_id>".$svc["service_id"]."</service_id>";
	echo "</svc>";
}
echo "</host>";
}

	

/*
echo "<pre>";
print_r($elem);
echo "</pre>";
*/







if($type == "HS"){
	$msg_error 		= 0;
	$tab_class 		= array("1" => "list_one", "0" => "list_two");
	$split 			= 0;
	$elem = array();

	$graphTs = array( NULL => NULL );
	$DBRESULT =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while($DBRESULT->fetchInto($graphT))
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$DBRESULT->free();
	
	# Verify if template exists
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	if (!$DBRESULT->numRows()){
;//		print "<div class='msg' align='center'>".$lang["no_graphtpl"]."</div>";	
	}
	


	$host_id = getMyHostIDService($id);
	$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name, special FROM index_data WHERE `trashed` = '0' AND host_id = '".$host_id."' AND service_id = '".$id."'");
	if (PEAR::isError($DBRESULT2))
		print "Mysql Error : ".$DBRESULT2->getDebugInfo();
	$DBRESULT2->fetchInto($svc_id);

/*
	if (preg_match("/([0-9]*)\_([0-9]*)/", $_GET["index"], $matches)){
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name, special FROM index_data WHERE `trashed` = '0' AND host_id = '".$matches[1]."' AND service_id = '".$matches[2]."'");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$DBRESULT2->fetchInto($svc_id);
	} else if (isset($_GET["index"])){
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name, special FROM index_data WHERE `trashed` = '0' AND id = '".$_GET["index"]."'");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$DBRESULT2->fetchInto($svc_id);
	} else if (isset($_GET["host_name"]) && isset($_GET["service_description"])){
		$svc_desc = str_replace("/", "#S#", $_GET["service_description"]);
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name, special FROM index_data WHERE `trashed` = '0' AND host_name = '".$_GET["host_name"]."' && service_description = '".$svc_desc."'");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$DBRESULT2->fetchInto($svc_id);
	}
*/

	$template_id = getDefaultGraph($svc_id["service_id"], 1);
	$DBRESULT2 =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
	$DBRESULT2->fetchInto($GraphTemplate);
	

	if (($GraphTemplate["split_component"] == 1 && !isset($_GET["split"])) || (isset($_GET["split"]) && $_GET["split"]["split"] == 1)){
		$split = 1;
	}
	
	$index = null;	
	if (isset($_GET["index"]))
		$index = $_GET["index"];
	else if (isset($svc_id["id"]))
		$index = $svc_id["id"];


$metrics = array();

//	if (!$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$svc_id["host_name"]]))){	
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_description  FROM index_data WHERE `trashed` = '0' AND host_name = '".$svc_id["host_name"]."' ORDER BY service_description");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$other_services = array();
		while ($DBRESULT2->fetchInto($selected_service)){
			if (preg_match("/meta_([0-9]*)/", $selected_service["service_description"], $matches)){
				$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
				if (PEAR::isError($DBRESULT_meta))
					print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
				$DBRESULT_meta->fetchInto($meta);
				$selected_service["service_description"] = $meta["meta_name"];
			}	
			$selected_service["service_description"] = str_replace("#S#", "/", $selected_service["service_description"]);
			$selected_service["service_description"] = str_replace("#BS#", "\\", $selected_service["service_description"]);
			$other_services[$selected_service["id"]] = $selected_service["service_description"];
		}
		$DBRESULT2->free();
		
		$service_id = $svc_id["service_id"];
		$index_id = $svc_id["id"];
		
				
		$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index."' AND `hidden` = '0' ORDER BY `metric_name`");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		for ($counter = 0;$DBRESULT2->fetchInto($metrics_ret); $counter++){
			$metrics[$metrics_ret["metric_id"]]["metric_name"] = str_replace('#S#', "/", $metrics_ret["metric_name"]);
			$metrics[$metrics_ret["metric_id"]]["metric_name"] = str_replace('#BS#', "\\", $metrics[$metrics_ret["metric_id"]]["metric_name"]);
			$metrics[$metrics_ret["metric_id"]]["metric_id"] = $metrics_ret["metric_id"];
			$metrics[$metrics_ret["metric_id"]]["class"] = $tab_class[$counter % 2];
		}
			
		# verify if metrics in parameter is for this index
		$metrics_active =& $_GET["metric"];
		$pass = 0;
		if (isset($metrics_active))
			foreach ($metrics_active as $key => $value)
				if (isset($metrics[$key]))
					$pass = 1;
		# 
		
		if ($msg_error == 0){
			if (isset($_GET["metric"]) && $pass){
				$DBRESULT =& $pearDB->query("DELETE FROM `ods_view_details` WHERE index_id = '".$index."'");
				if (PEAR::isError($DBRESULT))
					print "Mysql Error : ".$DBRESULT->getDebugInfo();
				foreach ($metrics_active as $key => $metric){
					if (isset($metrics_active[$key])){
						$DBRESULT =& $pearDB->query("INSERT INTO `ods_view_details` (`metric_id`, `contact_id`, `all_user`, `index_id`) VALUES ('".$key."', '".$contact_id."', '0', '".$index."');");
						if (PEAR::isError($DBRESULT))
							print "Mysql Error : ".$DBRESULT->getDebugInfo();
					}
				}
			} else {
				$DBRESULT =& $pearDB->query("SELECT metric_id FROM `ods_view_details` WHERE index_id = '".$index."' AND `contact_id` = '".$contact_id."'");
				if (PEAR::isError($DBRESULT))
					print "Mysql Error : ".$DBRESULT->getDebugInfo();
				$metrics_active = array();
				if ($DBRESULT->numRows())
					while ($DBRESULT->fetchInto($metric))
						$metrics_active[$metric["metric_id"]] = 1;		
				else
					foreach ($metrics as $key => $value)
						$metrics_active[$key] = 1;	
			}
		}
		
		if ($svc_id["host_name"] == "Meta_Module")
			$svc_id["host_name"] = "Meta Services";
			
		$svc_id["service_description"] = str_replace("#S#", "/", $svc_id["service_description"]);	
			
		if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
			$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
			if (PEAR::isError($DBRESULT_meta))
				print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
			$DBRESULT_meta->fetchInto($meta);
			$svc_id["service_description"] = $meta["meta_name"];
		}	
		
		if ($split){
			$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index."' AND `hidden` = '0' ORDER BY `metric_name`");
			if (PEAR::isError($DBRESULT2))
				print "Mysql Error : ".$DBRESULT2->getDebugInfo();
			for ($counter = 1;$DBRESULT2->fetchInto($metrics_ret); $counter++){
				if (isset($metrics_active[$metrics_ret["metric_id"]]) && $metrics_active[$metrics_ret["metric_id"]])
					$metrics_list[$metrics_ret["metric_id"]] = $counter;
			}
		}


//	}

	$tab_period['Daily']= (time() - (60 * 60 * 24));
	$tab_period['Weekly']= (time() - 60 * 60 * 24 * 7);
	$tab_period['Monthly']= (time() - 60 * 60 * 24 * 31);
	$tab_period['Yearly']= (time() - 60 * 60 * 24 * 365);


echo "<svc>";
echo "<sid>".$sid."</sid>";
echo "<id>".$id."</id>";
echo "<index>".$index."</index>";
echo "<opid>".$openid."</opid>";
echo "<split>".$split."</split>";


foreach($tab_period as $name => $start){
	echo "<period>";
	echo "<name>".$name."</name>";
	echo "<start>".$start."</start>";
	echo "<end>".time()."</end>";
	echo "</period>";	
}
echo "</svc>";





	}





















if($type == "SS"){
	$tab_class 		= array("1" => "list_one", "0" => "list_two");
	
		
	$graphTs = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT graph_id,name FROM giv_graphs_template ORDER BY name");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while($DBRESULT->fetchInto($graphT))
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$DBRESULT->free();
	
	
	
	if (isset($_GET["period"]))
		$period =  $_GET["period"];
	if (isset($_POST["period"]))
		$period =  $_POST["period"];
	
/*	
	$periods = array(	""=>"",
						"10800"=>$lang["giv_sr_p3h"],
						"21600"=>$lang["giv_sr_p6h"],
						"43200"=>$lang["giv_sr_p12h"],
						"86400"=>$lang["giv_sr_p24h"],
						"172800"=>$lang["giv_sr_p2d"],
						"302400"=>$lang["giv_sr_p4d"],
						"604800"=>$lang["giv_sr_p7d"],
						"1209600"=>$lang["giv_sr_p14d"],
						"2419200"=>$lang["giv_sr_p28d"],
						"2592000"=>$lang["giv_sr_p30d"],
						"2678400"=>$lang["giv_sr_p31d"],
						"5184000"=>$lang["giv_sr_p2m"],
						"10368000"=>$lang["giv_sr_p4m"],
						"15552000"=>$lang["giv_sr_p6m"],
						"31104000"=>$lang["giv_sr_p1y"]);
*/
	

	# Verify if template exists
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	if (!$DBRESULT->numRows())
;//		print "<div class='msg' align='center'>".$lang["no_graphtpl"]."</div>";
	
	$label = NULL;
		
	$elem = array();

	$host_id = getMyHostIDService($id);
	$DBRESULT2 =& $pearDBO->query("SELECT id, service_id, service_description, host_name, special FROM index_data WHERE `trashed` = '0' AND host_id = '".$host_id."' AND service_id = '".$id."'");
	if (PEAR::isError($DBRESULT2))
		print "Mysql Error : ".$DBRESULT2->getDebugInfo();
	$DBRESULT2->fetchInto($svc_id);
	$DBRESULT2->free();

	
	
//	if (!$isRestreint || ($isRestreint && isset($lcaHostByName["LcaHost"][$svc_id["host_name"]]))){
		$DBRESULT2 =& $pearDBO->query("SELECT id, service_description  FROM index_data WHERE `trashed` = '0' AND host_name = '".$svc_id["host_name"]."' ORDER BY service_description");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$other_services = array();
		while ($DBRESULT2->fetchInto($selected_service)){
			if (preg_match("/meta_([0-9]*)/", $selected_service["service_description"], $matches)){
				$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
				if (PEAR::isError($DBRESULT_meta))
					print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
				$DBRESULT_meta->fetchInto($meta);
				$selected_service["service_description"] = $meta["meta_name"];
			}	
			$selected_service["service_description"] = str_replace("#S#", "/", $selected_service["service_description"]);
			$selected_service["service_description"] = str_replace("#BS#", "\\", $selected_service["service_description"]);
			$other_services[$selected_service["id"]] = $selected_service["service_description"];
		}
		$DBRESULT2->free();
		
		$service_id = $svc_id["service_id"];
		$index_id = $svc_id["id"];
		
		if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
			$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
			if (PEAR::isError($DBRESULT_meta))
				print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
			$DBRESULT_meta->fetchInto($meta);
			$svc_id["service_description"] = $meta["meta_name"];
		}	
		
		$svc_id["service_description"] = str_replace("#S#", "/", str_replace("#BS#", "\\", $svc_id["service_description"]));
		
		$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$svc_id["id"]."' ORDER BY `metric_name`");
		if (PEAR::isError($DBRESULT2))
			print "Mysql Error : ".$DBRESULT2->getDebugInfo();
		$counter = 0;
		while ($DBRESULT2->fetchInto($metrics_ret)){			
			$metrics[$metrics_ret["metric_id"]]["metric_name"] = str_replace("#S#", "/", $metrics_ret["metric_name"]);
			$metrics[$metrics_ret["metric_id"]]["metric_name"] = str_replace("#BS#", "\\", $metrics[$metrics_ret["metric_id"]]["metric_name"]);
			$metrics[$metrics_ret["metric_id"]]["metric_id"] = $metrics_ret["metric_id"];
			$metrics[$metrics_ret["metric_id"]]["class"] = $tab_class[$counter % 2];
			$counter++;
		}
	
		if (isset($period) && $period){
			$start = time() - ($period + 30);
			$end = time() + 1;
		} else if (!isset($_GET["period"])){
			$start = $_GET["start"];
			$end = $_GET["end"];
		} else {
			$start = $_GET["start"];
			$end = $_GET["end"];	
		}
		
		
		# verify if metrics in parameter is for this index
		$metrics_active =& $_GET["metric"];
		$pass = 0;
		if (isset($metrics_active))
			foreach ($metrics_active as $key => $value)
				if (isset($metrics[$key]))
					$pass = 1;
		# 
		
		if (isset($_GET["metric"]) && $pass){
			$DBRESULT =& $pearDB->query("DELETE FROM `ods_view_details` WHERE index_id = '".$id."'");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			foreach ($metrics_active as $key => $metric){
				if (isset($metrics_active[$metric["metric_id"]])){
					$DBRESULT =& $pearDB->query("INSERT INTO `ods_view_details` (`metric_id`, `contact_id`, `all_user`, `index_id`) VALUES ('".$key."', '".$contact_id."', '0', '".$index_id."');");
					if (PEAR::isError($DBRESULT))
						print "Mysql Error : ".$DBRESULT->getDebugInfo();
				}
			}
		} else {
			$DBRESULT =& $pearDB->query("SELECT metric_id FROM `ods_view_details` WHERE index_id = '".$index_id."' AND `contact_id` = '".$contact_id."'");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getDebugInfo();
			$metrics_active = array();
			if ($DBRESULT->numRows())
				while ($DBRESULT->fetchInto($metric))
					$metrics_active[$metric["metric_id"]] = 1;		
			else
				foreach ($metrics as $key => $value)
					$metrics_active[$key] = 1;	
		}


/*
	$period['Daily']= (time() - 60 * 60 * 24);
	$period['Weekly']= (time() - 60 * 60 * 24 * 7);
	$period['Monthly']= (time() - 60 * 60 * 24 * 31);
	$period['Yearly']= (time() - 60 * 60 * 24 * 365);
*/

echo "<svc_zoom>";
echo "<sid>".$sid."</sid>";
echo "<id>".$id."</id>";
echo "<opid>".$openid."</opid>";
echo "<start>".$start."</start>";
echo "<end>".$end."</end>";
echo "<index>".$index_id."</index>";

echo "<metrics>";
$flag = 0;
foreach($metrics as $id => $metric){
	if($flag)
		echo "&amp;";
	$flag = 1;
	echo "metric[".$id."]=1";
}
echo "</metrics>";

/*
foreach($metrics as $id => $metric){
	echo "<metric>";
		echo "<class>".$metric["class"]."</class>";
		echo "<metric_name>".$metric["metric_name"]."</metric_name>";
		echo "<metric_id>".$id."</metric_id>";
	echo "</metric>";	
}
*/


echo "</svc_zoom>";


}

?>
