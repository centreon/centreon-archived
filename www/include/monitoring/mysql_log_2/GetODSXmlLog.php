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
	if ( eregi("(<|>|;|UNION|ALL|OR|AND|ORDER|SELECT|WHERE)", $g)) {
		get_error('sql injection detected');
		return 1;
	}
	return 0;
}

function check_session($sid, $pearDB){
	if(isset($sid) && !check_injection($sid)){
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($res->fetchInto($session)){
			return $session["user_id"];
		}else
			get_error('bad session id');		
	}
	else
		get_error('need session identifiant !');
	return 0;
}


function get_user_param($user_id, $pearDB)
{
	$tab_row = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM contact_param where cp_contact_id = '".$user_id."'");
	if (PEAR::isError($DBRESULT)){
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		return null;		
	}
	while( $row = $DBRESULT->fetchRow()){
		$tab_row[$row["cp_key"]] = $row["cp_value"];
	}
	return $tab_row;
}

function set_user_param($user_id, $pearDB, $key, $value)
{
//	$DBRESULT =& $pearDB->query("INSERT into contact_param (cp_key, cp_value, cp_contact_id) VALUES ('".$key."','".$value."','".$user_id."')");
	$DBRESULT =& $pearDB->query("UPDATE contact_param set cp_value ='".$value."' where cp_contact_id like '".$user_id."' AND cp_key like '".$key."' ");
	
	if (PEAR::isError($DBRESULT)) {
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
}





/*
 * XML tag
 */


if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") )
{
	header("Content-type: application/xhtml+xml"); }
else
{
	header("Content-type: text/xml");
}
echo("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n");



/*
 * Start XML document root
 */
 echo "<root>";



# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
$debugXML = 0;
$buffer = '';

$oreonPath = '../../../../';



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

	is_file ("../lang/".$lang_.".php") ? include_once ("../lang/".$lang_.".php") : include_once ("../lang/en.php");
	is_file ("../../../lang/".$lang_.".php") ? include_once ("../../../lang/".$lang_.".php") : include_once ("../../../lang/en.php");

	function getMyHostIDService($svc_id = NULL)	{
		if (!$svc_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host h, host_service_relation hs WHERE h.host_id = hs.host_host_id AND hs.service_service_id = '".$svc_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["host_id"];
		}
		return NULL;
	}




	if(isset($_GET["id"]) && !check_injection($_GET["id"])){
		$openid = htmlentities($_GET["id"]);
	}else
		$openid = "-1";






	if(isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = htmlentities($_GET["sid"]);
	}else
		$sid = "-1";


	$contact_id = check_session($sid,$pearDB);


	if(isset($_GET["num"]) && !check_injection($_GET["num"])){
		$num = htmlentities($_GET["num"]);
	}else
		$num = "0";

	if(isset($_GET["limit"]) && !check_injection($_GET["limit"])){
		$limit = htmlentities($_GET["limit"]);
	}else
		$limit = "30";

	if(isset($_GET["up"]) && !check_injection($_GET["up"])){
		$up = htmlentities($_GET["up"]);
		set_user_param($contact_id, $pearDB, "log_filter_host_up", $up);
	}else
		$up = "true";
	if(isset($_GET["down"]) && !check_injection($_GET["down"])){
		$down = htmlentities($_GET["down"]);
		set_user_param($contact_id, $pearDB, "log_filter_host_down", $down);
	}else
		$down = "true";

	if(isset($_GET["unreachable"]) && !check_injection($_GET["unreachable"])){
		$unreachable = htmlentities($_GET["unreachable"]);
		set_user_param($contact_id, $pearDB, "log_filter_host_unreachable", $unreachable);
	}else
		$unreachable = "true";

	if(isset($_GET["ok"]) && !check_injection($_GET["ok"])){
		$ok = htmlentities($_GET["ok"]);
		set_user_param($contact_id, $pearDB, "log_filter_svc_ok", $ok);
	}else
		$ok = "true";
	if(isset($_GET["warning"]) && !check_injection($_GET["warning"])){
		$warning = htmlentities($_GET["warning"]);
		set_user_param($contact_id, $pearDB, "log_filter_svc_warning", $warning);
	}else
		$warning = "true";
	if(isset($_GET["critical"]) && !check_injection($_GET["critical"])){
		$critical = htmlentities($_GET["critical"]);
		set_user_param($contact_id, $pearDB, "log_filter_svc_critical", $critical);
	}else
		$critical = "true";
	if(isset($_GET["unknown"]) && !check_injection($_GET["unknown"])){
		$unknown = htmlentities($_GET["unknown"]);
		set_user_param($contact_id, $pearDB, "log_filter_svc_unknown", $unknown);
	}else
		$unknown = "true";

	if(isset($_GET["notification"]) && !check_injection($_GET["notification"])){
		$notification = htmlentities($_GET["notification"]);
		set_user_param($contact_id, $pearDB, "log_filter_notif", $notification);
	}else
		$notification = "false";
		
	if(isset($_GET["alert"]) && !check_injection($_GET["alert"])){
		$alert = htmlentities($_GET["alert"]);
		set_user_param($contact_id, $pearDB, "log_filter_alert", $alert);
	}else
		$alert = "true";
		
	if(isset($_GET["error"]) && !check_injection($_GET["error"])){
		$error = htmlentities($_GET["error"]);
		set_user_param($contact_id, $pearDB, "log_filter_error", $error);
	}else
		$error = "false";


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




	if($contact_id){
		$user_params = get_user_param($contact_id, $pearDB);
		
		$alert = $user_params["log_filter_alert"];
		$notification = $user_params["log_filter_notif"];
		$error = $user_params["log_filter_error"];
		$unknown = $user_params["log_filter_svc_unknown"];
		$unreachable = $user_params["log_filter_host_unreachable"];
		$up = $user_params["log_filter_host_up"];
		$ok = $user_params["log_filter_svc_ok"];
		$down = $user_params["log_filter_host_down"];
		$warning = $user_params["log_filter_svc_warning"];
		$critical = $user_params["log_filter_svc_critical"];
	}




	if ($StartDate !=  "" && $StartTime != ""){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $StartDate, $matchesD);
		preg_match("/^([0-9]*):([0-9]*)/", $StartTime, $matchesT);
		$start = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], 1) ;
	}
	if ($EndDate !=  "" && $EndTime != ""){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $EndDate, $matchesD);
		preg_match("/^([0-9]*):([0-9]*)/", $EndTime, $matchesT);
		$end = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], 1) ;
	}


	$period = 86400;
	if($auto_period > 0){
		$period = $auto_period;
		$start = time() - ($period);
		$end = time();
	}



	$DBRESULT_OPT =& $pearDB->query("SELECT color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
	if (PEAR::isError($DBRESULT_OPT))
		print "DB Error : ".$DBRESULT_OPT->getDebugInfo()."<br />";
	$DBRESULT_OPT->fetchInto($general_opt);

	$tab_color_service = array();
	$tab_color_service["OK"] = $general_opt["color_ok"];
	$tab_color_service["WARNING"] = $general_opt["color_warning"];
	$tab_color_service["CRITICAL"] = $general_opt["color_critical"];
	$tab_color_service["UNKNOWN"] = $general_opt["color_unknown"];
	$tab_color_service["PENDING"] = $general_opt["color_pending"];

	$tab_color_host = array();
	$tab_color_host["UP"] = $general_opt["color_up"];
	$tab_color_host["DOWN"] = $general_opt["color_down"];
	$tab_color_host["UNREACHABLE"] = $general_opt["color_unreachable"];

	
	
	$tab_type = array("1" => "HARD", "0" => "SOFT");
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	$tab_status_service = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN");

	$logs = array();	


/*
 * Print infos..
 */
echo "<infos>";
echo "<multi>".$multi."</multi>";
echo "<sid>".$sid."</sid>";
echo "<opid>".$openid."</opid>";
echo "<start>".$start."</start>";
echo "<end>".$end."</end>";
echo "<notification>".$notification."</notification>";
echo "<alert>".$alert."</alert>";
echo "<error>".$error."</error>";
echo "<up>".$up."</up>";
echo "<down>".$down."</down>";
echo "<unreachable>".$unreachable."</unreachable>";
echo "<ok>".$ok."</ok>";
echo "<warning>".$warning."</warning>";
echo "<critical>".$critical."</critical>";
echo "<unknown>".$unknown."</unknown>";
echo "</infos>";


 

$msg_type_set = array ();
if($alert == 'true' ){
	array_push ($msg_type_set, "'0'");
}
if($alert == 'true' ){
	array_push ($msg_type_set, "'1'");
}
if($notification == 'true'){
	array_push ($msg_type_set, "'2'");
}
if($notification== 'true'){
	array_push ($msg_type_set, "'3'");
}
if($error == 'true')
array_push ($msg_type_set, "'4'");

$msg_req='';
if( count($msg_type_set) > 0 )
	$msg_req .= ' AND msg_type IN (' . implode(",",$msg_type_set). ') ';

$msg_status_set = array ();


if($error == 'true'){
	array_push ($msg_status_set, "'NULL'");
}

if($up == 'true' ){
	array_push ($msg_status_set, "'UP'");
}
if($down == 'true' ){
	array_push ($msg_status_set, "'DOWN'");
}
if($unreachable == 'true' ){
	array_push ($msg_status_set, "'UNREACHABLE'");
}

if($ok == 'true' ){
	array_push ($msg_status_set, "'ok'");
}
if($warning == 'true' ){
	array_push ($msg_status_set, "'warning'");
}
if($critical == 'true' ){
	array_push ($msg_status_set, "'critical'");
}
if($unknown == 'true' ){
	array_push ($msg_status_set, "'unknown'");
}

if( count($msg_status_set) > 0 )
{
	$msg_req .= ' AND (status IN (' . implode(",",$msg_status_set). ') ';

	if($error  == 'true' || $notification == 'true')
		$msg_req .= 'OR status is null';

	$msg_req .=')';
}


/*
** If multi checked 
*/
if($multi == 1){
	$tab_id = split(",",$openid);
	$tab_host_name= array();
	$tab_svc = array();

	/*
	 * prepare tab with host and svc
	 */
	foreach($tab_id as $openid)
	{
		$tab_tmp = split("_",$openid);
		$id = $tab_tmp[1];
		$type = $tab_tmp[0];

	
		if($type == "HG"){
			$hosts = getMyHostGroupHosts($id);
			foreach($hosts as $h_id)
			{
				$host_name = getMyHostName($h_id);
				array_push ($tab_host_name, "'".$host_name."'");
			}

		}/* end HG */
		else if($type == "HH"){
			$host_name = getMyHostName($id);
			array_push ($tab_host_name, "'".$host_name."'");		
		}/* end HH */
		else if($type == "HS"){
			$service_description = getMyServiceName($id);
			$host_id = getMyHostIDService($id);
			$host_name = getMyHostName($host_id);
			$tmp["svc_name"] = $service_description;
			$tmp["host_name"] = $host_name;
			array_push($tab_svc, $tmp);
		}/* end HH */
	}


	/*
	 * Making the request
	 */


	$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end'  $msg_req";

	if(count($tab_host_name) > 0){
	$req .= " AND (host_name in(".implode(",",$tab_host_name).") ";


	if($error  == 'true' || $notification == 'true'){
		$req .= ' OR host_name is null';		
	}
	$req .= ")";
		
	}


	if(count($tab_svc) > 0){
		$req .= " AND ( ";
		$flag = 0;
		foreach($tab_svc as $svc){
		
				if($flag)
				 $req .= " OR ";
				$flag = 1;			
				$req .= " ((host_name like '".$svc["host_name"]."'";
				if($error  == 'true' || $notification == 'true')
					$req .= ' OR host_name is null';
				$req .= ")";
			
				$req .= " AND (service_description like '".$svc["svc_name"]."' ";
				$req .= ")) ";
		}
		$req .= " )";
	}	
	
}
else// only click on one element
{
	$id = substr($openid, 3, strlen($openid));
	$type = substr($openid, 0, 2);	


	if($type == "HG"){
		$hosts = getMyHostGroupHosts($id);
		$tab_host_name= array();
		foreach($hosts as $h_id)
		{
			$host_name = getMyHostName($h_id);
			array_push ($tab_host_name, "'".$host_name."'");
		}
		$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end'  $msg_req AND (host_name in(".implode(",",$tab_host_name).") ";
		if($error  == 'true' || $notification == 'true')
			$req .= ' OR host_name is null';
		$req .= ")";
	}/* end HG */
	else if($type == "HH"){
		$host_name = getMyHostName($id);
	
		$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end'  $msg_req AND (host_name like '".$host_name."' ";
	
		if($error  == 'true' || $notification == 'true')
			$req .= ' OR host_name is null';
		$req .= ")";
	}/* end HH */
	else if($type == "HS"){
		$service_description = getMyServiceName($id);
		$host_id = getMyHostIDService($id);
		$host_name = getMyHostName($host_id);
	
		$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end'  $msg_req AND (host_name like '".$host_name."'";

		if($error  == 'true' || $notification == 'true')
			$req .= ' OR host_name is null';
			
		$req .= ")";
	
		$req .= " AND (service_description like '".$service_description."' ";
/*
		if($error  == 'true' || $notification == 'true')
			$req .= ' OR service_description is null';
			*/
		$req .= ") ";
	
	
	}/* end HH */
	else{ /* RR_0*/
		$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end'  $msg_req";
	}
}





	/*
	 * calculate size before limit for pagination 
	 */
	 $lstart = 0;
		$DBRESULT =& $pearDBO->query($req);
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getMessage();
		$rows = $DBRESULT->numrows();
	
		if(($num * $limit) > $rows)
			$num = round($rows / $limit) - 1;
		$lstart = $num * $limit;
	
		if ($lstart <= 0)
			$lstart = 0;
	
	/*
	 * pagination
	 */
		$page_max = ceil($rows / $limit);
		if ($num > $page_max && $rows)
			$num = $page_max - 1;
		
		if($num < 0)
		$num = 0;
	
		$pageArr = array();
		$istart = 0;
		for($i = 5, $istart = $num; $istart > 0 && $i > 0; $i--)
			$istart--;
		for($i2 = 0, $iend = $num; ( $iend <  ($rows / $limit -1)) && ( $i2 < (5 + $i)); $i2++)
			$iend++;
		for ($i = $istart; $i <= $iend; $i++){
			$pageArr[$i] = array("url_page"=>"&num=$i&limit=".$limit, "label_page"=>($i +1),"num"=> $i);
		}
		if($i > 1)
		foreach ($pageArr as $key => $tab) {
			echo "<page>";
			if($tab["num"] == $num)
			echo "<selected>1</selected>";
			else
			echo "<selected>0</selected>";
			
			echo "<num><![CDATA[".$tab["num"]."]]></num>";
			echo "<url_page><![CDATA[".$tab["url_page"]."]]></url_page>";
			echo "<label_page><![CDATA[".$tab["label_page"]."]]></label_page>";
			echo "</page>";
		}
		$num_page = 0;
	
		if($num > 0 && $num < $rows)
			$num_page= $num * $limit;

		$prev = $num - 1;
		$next = $num + 1;
		
		if($num > 0)
			echo "<first show='true'>0</first>";
		else
			echo "<first show='false'>none</first>";

		if($num > 1)
			echo "<prev show='true'>$prev</prev>";
		else
			echo "<prev show='false'>none</prev>";

		if($num < $page_max-1)
			echo "<next show='true'>$next</next>";
		else
			echo "<next show='false'>none</next>";

		$last = $page_max -1;

		if($num < $page_max-1)
			echo "<last show='true'>$last</last>";
		else
			echo "<last show='false'>none</last>";



	/*
	 * End pagination
	 */
	
	
	
	/*
	 * Full Request
	 */
		$req .= " ORDER BY ctime DESC,log_id DESC";
		$req .= " LIMIT $lstart,$limit";
	
		$DBRESULT =& $pearDBO->query($req);
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getMessage();
		for ($cpts = 0;$DBRESULT->fetchInto($log);){
				echo "<line>";
				echo "<msg_type>".$log["msg_type"]."</msg_type>";
	
				if($log["msg_type"] > 1)
				 echo "<retry></retry>";
				else
				 echo "<retry>".$log["retry"]."</retry>";
	
	
				if($log["msg_type"] == 2 || $log["msg_type"] == 3)
					echo "<type>NOTIF</type>";
				else
					echo "<type>".$log["type"]."</type>";
	
				$color = '';
				if($log["msg_type"] == 0 || $log["msg_type"] == 2)
					$color = $tab_color_service[$log["status"]];
				if($log["msg_type"] == 1 || $log["msg_type"] == 3)
					$color = $tab_color_host[$log["status"]];
	
				echo '<status color="'.$color.'">'.$log["status"].'</status>';
				echo "<service_description>".$log["service_description"]."</service_description>";
				echo "<host_name>".$log["host_name"]."</host_name>";
				echo "<class>".$tab_class[$cpts % 2]."</class>";
				echo "<date>".date(_("Y/m/d"), $log["ctime"])."</date>";
				echo "<time>".date(_("H:i:s"), $log["ctime"])."</time>";
				echo "<output><![CDATA[".$log["output"]."]]></output>";
				echo "<contact><![CDATA[".$log["notification_contact"]."]]></contact>";
				echo "<contact_cmd><![CDATA[".$log["notification_cmd"]."]]></contact_cmd>";
				echo "</line>";
			$cpts++;
		}






/*
 * LANG
 */
$lang["notification"] = "notification";
$lang["alert"] = "alert";
$lang["error"] = "error";

$lang["service"] = "Service";
$lang["host"] = "Host";

$lang["down"] = "down";
$lang["up"] = "up";
$lang["unreachable"] = "unreachable";

$lang["ok"] = "ok";
$lang["critical"] = "critical";
$lang["warning"] = "warning";
$lang["unknown"] = "unknown";

$lang["typeAlert"] = "Type";

echo "<lang>";
echo "<typeAlert>"._("Type")."</typeAlert>";
echo "<notification>"._("notification")."</notification>";
echo "<alert>"._("alert")."</alert>";
echo "<error>"._("error")."</error>";

echo "<service>"._("Service")."</service>";
echo "<host>"._("Host")."</host>";

echo "<down>"._("down")."</down>";
echo "<up>"._("up")."</up>";
echo "<unreachable>"._("unreachable")."</unreachable>";

echo "<warning>"._("warning")."</warning>";
echo "<ok>"._("ok")."</ok>";
echo "<critical>"._("critical")."</critical>";
echo "<unknown>"._("unknown")."</unknown>";
echo "</lang>";
/*
 * END LANG
 */


			echo "<req><![CDATA[".$req."]]></req>";



/*
 * End XML document root
 */
echo "</root>";


?>