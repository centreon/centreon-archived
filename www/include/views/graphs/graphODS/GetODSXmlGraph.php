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

	function check_injection($g){
		if ( eregi("(<|>|UNION|ALL|OR|AND|ORDER|SELECT|WHERE)", $g)) {
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


/*
 * Start document root
 */
 echo "<root>";



# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
$debugXML = 0;
$buffer = '';

$oreonPath = '../../../../../';



/*
 * pearDB init
 */ 
require_once 'DB.php';

include_once($oreonPath . "etc/centreon.conf.php");
include_once($oreonPath . "www/DBconnect.php");
include_once($oreonPath . "www/DBOdsConnect.php");


/*
 * PHP functions
 */
include_once($oreonPath . "www/include/common/common-Func-ACL.php");
include_once($oreonPath . "www/include/common/common-Func.php");

/*
 * Lang file
 */
	if(isset($_GET["lang"]) && !check_injection($_GET["lang"])){
		$lang_ = htmlentities($_GET["lang"]);
	}else
		$lang_ = "-1";

	is_file ("../lang/".$lang_.".php") ? include_once ("../lang/".$lang_.".php") : include_once ("../../lang/en.php");


//$lang = array();
/*
$lang["start"] = "Du ";
$lang["end"] = "au ";
$lang["period"] = "Periode ";
$lang["optionAdvanced"] = "Options";
*/

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


/*
	$lcaHostByID = getLcaHostByID($pearDB);
	$lcaHostByName = getLcaHostByName($pearDB);
	$LcaHostStr = getLcaHostStr($lcaHostByID["LcaHost"]);
*/





	if(isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = htmlentities($_GET["sid"]);
	}else
		$sid = "-1";

	if(isset($_GET["template_id"]) && !check_injection($_GET["template_id"])){
		$template_id = htmlentities($_GET["template_id"]);
	}else
		$template_id = "1";

	if(isset($_GET["split"]) && !check_injection($_GET["split"])){
		$split = htmlentities($_GET["split"]);
	}else
		$split = "0";


	if(isset($_GET["StartDate"]) && !check_injection($_GET["StartDate"])){
		$StartDate = htmlentities($_GET["StartDate"]);
	}else
		$StartDate = "";
	if(isset($_GET["EndDate"]) && !check_injection($_GET["EndDate"])){
		$EndDate = htmlentities($_GET["EndDate"]);
	}else
		$EndDate = "";

	if(isset($_GET["StartTime"]) && !check_injection($_GET["StartTime"])){
		$StartTime = htmlentities($_GET["StartTime"]);
	}else
		$StartTime = "";
	if(isset($_GET["EndTime"]) && !check_injection($_GET["EndTime"])){
		$EndTime = htmlentities($_GET["EndTime"]);
	}else
		$EndTime = "";

	if(isset($_GET["period"]) && !check_injection($_GET["period"])){
		$auto_period = htmlentities($_GET["period"]);
	}else
		$auto_period = "-1";

	if(isset($_GET["multi"]) && !check_injection($_GET["multi"])){
		$multi = htmlentities($_GET["multi"]);
	}else
		$multi = "-1";

	if(isset($_GET["id"])){
		$openid = htmlentities($_GET["id"]);
	}else
		$openid = "-1";


//$oreon->user->user_id
$contact_id = '2';

	if ($StartDate !=  "" && $StartTime != ""){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $StartDate, $matchesD);
		preg_match("/^([0-9]*):([0-9]*)/", $StartTime, $matchesT);
		$start = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], 0) ;
	}
	if ($EndDate !=  "" && $EndTime != ""){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $EndDate, $matchesD);
		preg_match("/^([0-9]*):([0-9]*)/", $EndTime, $matchesT);
		$end = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], 0) ;
	}
	
	$period = 86400;
	if($auto_period > 0){
		$period = $auto_period;

		$start = time() - ($period);
		$end = time();
	}			



/*
 * set graph template list
 */
	$graphTs = array( NULL => NULL );
	$DBRESULT =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
	while($DBRESULT->fetchInto($graphT))
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$DBRESULT->free();



	$i = 0;
	$tab_id = array();


	if($multi == 0){
		$tab_tmp = split("_",$openid);
		$id = $tab_tmp[1];
		$type = $tab_tmp[0];
		array_push($tab_id,$type."_".$id);
	}
	else{
		echo "<opid>".$openid."</opid>";
		$tab_tmp = split(",",$openid);
	
		foreach($tab_tmp as $openid)
		{
			$tab_tmp = split("_",$openid);
			$id = $tab_tmp[1];
			$type = $tab_tmp[0];
		
			if($type == 'HG')
			{
				$hosts = getMyHostGroupHosts($id);
				foreach($hosts as $host)
				{
					if(host_has_one_or_more_GraphService($host)){
						$services = getMyHostServices($host);
						foreach($services as $svc_id => $svc_name)
						{
							if(service_has_graph($host,$svc_id))
							{
								$oid = "HS_".$svc_id;
								array_push($tab_id,$oid);	
							}
						}
					}
				}
			}
			else if ($type == 'HH')
			{
				$services = getMyHostServices($id);
				foreach($services as $svc_id => $svc_name)
				{
					if(service_has_graph($id,$svc_id))
					{
						$oid = "HS_".$svc_id;
						array_push($tab_id,$oid);	
					}
				}
				
			}
			else
			{
				$hosts = getMyServiceHosts($id);
				if($hosts)
				{
					$host_id = array_pop($hosts);					
					if( service_has_graph($host_id,$id) )
					{
						array_push($tab_id,$openid);
					}
				}
			}
		}
	}


/*
 * clean double in tab_id
 */
$tab_tmp = $tab_id;
$tab_id = array();
foreach($tab_tmp as $openid)
{
	$tab_opid = split("_",$openid);
	$id = $tab_opid[1];
	$type = $tab_opid[0];
	if(!in_array($type."_".$id, $tab_id))
		array_push($tab_id,$type."_".$id);
}


foreach($tab_id as $openid)
{
	$tab_tmp = split("_",$openid);
	$id = $tab_tmp[1];
	$type = $tab_tmp[0];

	if($multi && $type == 'HS')
		$type = "SS";
	else if($multi)
		$type = "NO";
	else
		;


	/*
	 * for one svc -> daily,weekly,monthly,yearly..
	 */
	if($type == "HS"){
		$msg_error 		= 0;
		$tab_class 		= array("1" => "list_one", "0" => "list_two");
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
	
	$name = $svc_id["service_description"];
	
	//	if($template_id == 1)
			$template_id = getDefaultGraph($svc_id["service_id"], 1);
		$DBRESULT2 =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
		$DBRESULT2->fetchInto($GraphTemplate);
		
	
	//	if (($GraphTemplate["split_component"] == 1 && !isset($_GET["split"])) || (isset($_GET["split"]) && $_GET["split"]["split"] == 1)){
		if ($GraphTemplate["split_component"] == 1 ) {
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
		echo "<name>".$name."</name>";
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
	
		if($split)
		foreach($metrics as $metric_id => $metric)
		{
			echo "<metric>";
			echo "<metric_id>".$metric_id."</metric_id>";	
			echo "</metric>";
		}
		echo "</period>";	
	}
	echo "</svc>";
}
	
	
	
	
	
	
		
	
	/*
	 * For service zoom or multi selected
	 */
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
	
	
			if($template_id ==  1 && isset($svc_id["service_id"]))
				$template_id = getDefaultGraph($svc_id["service_id"], 1);
/*
			if($template_id ==  0)
				$template_id = 1;
*/				

			$DBRESULT2 =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
			$DBRESULT2->fetchInto($GraphTemplate);
			
		
			if (($GraphTemplate["split_component"] == 1 && !isset($_GET["split"])) || (isset($_GET["split"]) && $_GET["split"]["split"] == 1)){
				$split = 1;
			}
	
	
			
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

	
		if($multi)
			echo "<multi_svc>";
		
		else
			echo "<svc_zoom>";

		echo "<sid>".$sid."</sid>";
		echo "<id>".$id."</id>";
		echo "<opid>".$openid."</opid>";
		echo "<start>".$start."</start>";
		echo "<end>".$end."</end>";
		echo "<index>".$index_id."</index>";
		echo "<split>".$split."</split>";
		echo "<tpl>".$template_id."</tpl>";
		echo "<multi>".$multi."</multi>";

		if(!$multi)
		{
			if($split == 0){
				echo "<metricsTab>";
				$flag = 0;
				foreach($metrics as $id => $metric){
			
					if(isset($_GET["metric"]) && $_GET["metric"][$id] == 1)
					{
						if($flag)
							echo "&amp;";
						$flag = 1;
						echo "metric[".$id."]=1";
					}
				}
				echo "</metricsTab>";
			}
			else
			{
				echo "<metricsTab>..</metricsTab>";
			}
			
			foreach($metrics as $id => $metric){
				echo "<metrics>";
				echo "<metric_id>" . $id ."</metric_id>";
		
				if(isset($_GET["metric"]) && $_GET["metric"][$id] == 0)
					echo "<select>0</select>";
				else
					echo "<select>1</select>";
		
				echo "<metric_name>" . $metric["metric_name"] ."</metric_name>";
				echo "</metrics>";
			}
	
			foreach($graphTs as $id => $tpl){
				if($tpl && $id){
					echo "<tpl>";
						echo "<tpl_name>".$tpl."</tpl_name>";
						echo "<tpl_id>".$id."</tpl_id>";
					echo "</tpl>";	
				}
			}
			echo "</svc_zoom>";
		}
		else
		{
			foreach($metrics as $id => $metric){
				echo "<metrics>";
				echo "<metric_id>" . $id ."</metric_id>";
		
				echo "<select>1</select>";
		
				echo "<metric_name>" . $metric["metric_name"] ."</metric_name>";
				echo "</metrics>";
			}


			echo "</multi_svc>";
		}
	
	}
	
	else
	{
	// no output
	}
$metrics = array();	
} 
/*
 * End foreach
 */


/*
 * LANG
 */

echo "<lang>";
echo "<giv_gg_tpl>".$lang["giv_gg_tpl"]."</giv_gg_tpl>";
echo "<advanced>".$lang["optionAdvanced"]."</advanced>";
echo "<giv_split_component>".$lang['giv_split_component']."</giv_split_component>";
echo "</lang>";

/*
 * end root
 */

/*
 * if you want debug img..
 */
 $debug = 0;
 echo "<debug>".$debug."</debug>";
 echo "</root>";

?>
