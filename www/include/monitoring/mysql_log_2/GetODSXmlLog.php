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
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
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


	$id = substr($openid, 3, strlen($openid));
	$type = substr($openid, 0, 2);


	if(isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = htmlentities($_GET["sid"]);
	}else
		$sid = "-1";

	if(isset($_GET["num"]) && !check_injection($_GET["num"])){
		$num = htmlentities($_GET["num"]);
	}else
		$num = "0";

	if(isset($_GET["limit"]) && !check_injection($_GET["limit"])){
		$limit = htmlentities($_GET["limit"]);
	}else
		$limit = "30";


	if(isset($_GET["host"]) && !check_injection($_GET["host"])){
		$host = htmlentities($_GET["host"]);
	}else
		$host = "1";

	if(isset($_GET["service"]) && !check_injection($_GET["service"])){
		$service = htmlentities($_GET["service"]);
	}else
		$service = "1";



	if(isset($_GET["up"]) && !check_injection($_GET["up"])){
		$up = htmlentities($_GET["up"]);
	}else
		$up = "1";
	if(isset($_GET["down"]) && !check_injection($_GET["down"])){
		$down = htmlentities($_GET["down"]);
	}else
		$down = "1";

	if(isset($_GET["unreachable"]) && !check_injection($_GET["unreachable"])){
		$unreachable = htmlentities($_GET["unreachable"]);
	}else
		$unreachable = "1";

	if(isset($_GET["ok"]) && !check_injection($_GET["ok"])){
		$ok = htmlentities($_GET["ok"]);
	}else
		$ok = "1";
	if(isset($_GET["warning"]) && !check_injection($_GET["warning"])){
		$warning = htmlentities($_GET["warning"]);
	}else
		$warning = "1";
	if(isset($_GET["critical"]) && !check_injection($_GET["critical"])){
		$critical = htmlentities($_GET["critical"]);
	}else
		$critical = "1";
	if(isset($_GET["unknown"]) && !check_injection($_GET["unknown"])){
		$unknown = htmlentities($_GET["unknown"]);
	}else
		$unknown = "1";




	if(isset($_GET["notification"]) && !check_injection($_GET["notification"])){
		$notification = htmlentities($_GET["notification"]);
	}else
		$notification = "0";
		
	if(isset($_GET["alert"]) && !check_injection($_GET["alert"])){
		$alert = htmlentities($_GET["alert"]);
	}else
		$alert = "0";
		
	if(isset($_GET["error"]) && !check_injection($_GET["error"])){
		$error = htmlentities($_GET["error"]);
	}else
		$error = "0";


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



//$oreon->user->user_id
$contact_id = '2';

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
	if($auto_period > 0)
		$period = $auto_period;

	if (!isset($start) && !isset($end)){
		$start = time() - ($period);
		$end = time();
	}			


	$DBRESULT_OPT =& $pearDB->query("SELECT color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
	if (PEAR::isError($DBRESULT_OPT))
		print "DB Error : ".$DBRESULT_OPT->getDebugInfo()."<br>";
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
 * Set Get vaiable
 */
$msg_type_set = array ();
if($alert && $service){
	array_push ($msg_type_set, "'0'");
}
if($alert && $host){
	array_push ($msg_type_set, "'1'");
}
if($notification && $service){
	array_push ($msg_type_set, "'2'");
}
if($notification && $host){
	array_push ($msg_type_set, "'3'");
}
if($error)
array_push ($msg_type_set, "'4'");

$msg_req='';
if( count($msg_type_set) > 0 )
	$msg_req .= ' AND msg_type IN (' . implode(",",$msg_type_set). ') ';

$msg_status_set = array ();


if($error){
	array_push ($msg_status_set, "'NULL'");
}

if($up && $host){
	array_push ($msg_status_set, "'UP'");
}
if($down && $host){
	array_push ($msg_status_set, "'DOWN'");
}
if($unreachable && $host){
	array_push ($msg_status_set, "'UNREACHABLE'");
}

if($ok && $service){
	array_push ($msg_status_set, "'ok'");
}
if($warning && $service){
	array_push ($msg_status_set, "'warning'");
}
if($critical && $service){
	array_push ($msg_status_set, "'critical'");
}
if($unknown && $service){
	array_push ($msg_status_set, "'unknown'");
}

if( count($msg_status_set) > 0 )
{
	$msg_req .= ' AND (status IN (' . implode(",",$msg_status_set). ') ';

	if($error || $notification)
		$msg_req .= 'OR status is null';

	$msg_req .=')';
}


if($type == "HG"){
	$hosts = getMyHostGroupHosts($id);
	$tab_host_name= array();
	foreach($hosts as $h_id)
	{
		$host_name = getMyHostName($h_id);
		array_push ($tab_host_name, "'".$host_name."'");
	}
	$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $sort_str1 $msg_req AND (host_name in(".implode(",",$tab_host_name).") ";
	if($error || $notification)
		$req .= ' OR host_name is null';
	$req .= ")";
}/* end HG */
else if($type == "HH"){
	$host_name = getMyHostName($id);

	$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $sort_str1 $msg_req AND (host_name like '".$host_name."' ";

	if($error || $notification)
		$req .= ' OR host_name is null';
	$req .= ")";
}/* end HH */
else if($type == "HS"){
	$service_description = getMyServiceName($id);
	$host_id = getMyHostIDService($id);
	$host_name = getMyHostName($host_id);

	$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $sort_str1 $msg_req AND (host_name like '".$host_name."'";
	if($error || $notification)
		$req .= ' OR host_name is null';
	$req .= ")";

	$req .= " AND (service_description like '".$service_description."' ";
	if($error || $notification)
		$req .= ' OR service_description is null';
	$req .= ") ";


}/* end HH */
else{
	$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $sort_str1 $msg_req ";
}

/*
 * Print infos..
 */
echo "<infos>";
echo "<sid>".$sid."</sid>";
echo "<id>".$id."</id>";
echo "<opid>".$openid."</opid>";
echo "<start>".$start."</start>";
echo "<end>".$end."</end>";
echo "<notification>".$notification."</notification>";
echo "<alert>".$alert."</alert>";
echo "<error>".$error."</error>";
echo "<service>".$service."</service>";
echo "<host>".$host."</host>";
echo "<up>".$up."</up>";
echo "<down>".$down."</down>";
echo "<unreachable>".$unreachable."</unreachable>";
echo "<ok>".$ok."</ok>";
echo "<warning>".$warning."</warning>";
echo "<critical>".$critical."</critical>";
echo "<unknown>".$unknown."</unknown>";
echo "</infos>";



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
			if($log["msg_type"] == 0)
				$color = $tab_color_service[$log["status"]];
			if($log["msg_type"] == 1)
				$color = $tab_color_host[$log["status"]];

			echo '<status color="'.$color.'">'.$log["status"].'</status>';
			echo "<service_description>".$log["service_description"]."</service_description>";
			echo "<host_name>".$log["host_name"]."</host_name>";
			echo "<class>".$tab_class[$cpts % 2]."</class>";
			echo "<date>".date($lang["date_format"], $log["ctime"])."</date>";
			echo "<time>".date($lang["time_format"], $log["ctime"])."</time>";
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

$lang["service"] = "service";
$lang["host"] = "host";

$lang["down"] = "down";
$lang["up"] = "up";
$lang["unreachable"] = "unreachable";

$lang["ok"] = "ok";
$lang["critical"] = "critical";
$lang["warning"] = "warning";
$lang["unknown"] = "unknown";

$lang["typeAlert"] = "Alert";

echo "<lang>";
echo "<typeAlert>".$lang["typeAlert"]."</typeAlert>";
echo "<notification>".$lang["notification"]."</notification>";
echo "<alert>".$lang["alert"]."</alert>";
echo "<error>".$lang["error"]."</error>";

echo "<service>".$lang["service"]."</service>";
echo "<host>".$lang["host"]."</host>";

echo "<down>".$lang["down"]."</down>";
echo "<up>".$lang["up"]."</up>";
echo "<unreachable>".$lang["unreachable"]."</unreachable>";

echo "<warning>".$lang["warning"]."</warning>";
echo "<ok>".$lang["ok"]."</ok>";
echo "<critical>".$lang["critical"]."</critical>";
echo "<unknown>".$lang["unknown"]."</unknown>";
echo "</lang>";
/*
 * END LANG
 */





/*
 * End XML document root
 */
echo "</root>";


?>