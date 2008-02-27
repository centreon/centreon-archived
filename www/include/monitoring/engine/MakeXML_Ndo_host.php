<?php
/**
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

	# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
	$debugXML = 0;
	$buffer = '';
	$oreonPath = '/srv/oreon/';

	/* security check 1/2*/
	if($oreonPath == '@INSTALL_DIR_OREON@')
		get_error('please set your oreonPath');
	/* security end 1/2 */

	include_once($oreonPath . "etc/centreon.conf.php");
	include_once($oreonPath . "www/DBconnect.php");
	include_once($oreonPath . "www/DBndoConnect.php");
	include_once($oreonPath . "www/include/common/common-Func-ACL.php");
	include_once($oreonPath . "www/include/common/common-Func.php");
	
	$ndo_base_prefix = getNDOPrefix();
	

	/* security check 2/2*/
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){

		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$res->fetchInto($session))
			get_error('bad session id');
	} else
		get_error('need session identifiant !');
	/* security end 2/2 */

	/* requisit */
	if (isset($_GET["instance"]) && !check_injection($_GET["instance"]))
		$instance = htmlentities($_GET["instance"]);
	else
		$instance = "ALL";
		
	if (isset($_GET["num"]) && !check_injection($_GET["num"]))
		$num = htmlentities($_GET["num"]);
	else
		get_error('num unknown');
	
	if (isset($_GET["limit"]) && !check_injection($_GET["limit"]))
		$limit = htmlentities($_GET["limit"]);
	else
		get_error('limit unknown');


	/* options */
	if (isset($_GET["search"]) && !check_injection($_GET["search"])){
		$search = htmlentities($_GET["search"]);
	} else
		$search = "";

	if(isset($_GET["sort_type"]) && !check_injection($_GET["sort_type"])){
		$sort_type = htmlentities($_GET["sort_type"]);
	}else
		$sort_type = "host_name";

	if(isset($_GET["order"]) && !check_injection($_GET["order"])){
		$order = htmlentities($_GET["order"]);
	}else
		$oreder = "ASC";

	if(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])){
		$date_time_format_status = htmlentities($_GET["date_time_format_status"]);
	}else
		$date_time_format_status = "d/m/Y H:i:s";

	if(isset($_GET["o"]) && !check_injection($_GET["o"])){
		$o = htmlentities($_GET["o"]);
	}else
		$o = "h";
	if(isset($_GET["p"]) && !check_injection($_GET["p"])){
		$p = htmlentities($_GET["p"]);
	}else
		$p = "2";



	/* security end*/

	# class init
	class Duration
	{
		function toString ($duration, $periods = null)
	    {
	        if (!is_array($duration)) {
	            $duration = Duration::int2array($duration, $periods);
	        }
	        return Duration::array2string($duration);
	    }
	    function int2array ($seconds, $periods = null)
	    {
	        // Define time periods
	        if (!is_array($periods)) {
	            $periods = array (
	                    'y'	=> 31556926,
	                    'M' => 2629743,
	                    'w' => 604800,
	                    'd' => 86400,
	                    'h' => 3600,
	                    'm' => 60,
	                    's' => 1
	                    );
	        }
	        // Loop
	        $seconds = (int) $seconds;
	        foreach ($periods as $period => $value) {
	            $count = floor($seconds / $value);
	            if ($count == 0) {
	                continue;
	            }
	            $values[$period] = $count;
	            $seconds = $seconds % $value;
	        }
	        // Return
	        if (empty($values)) {
	            $values = null;
	        }
	        return $values;
	    }

	    function array2string ($duration)
	    {
	        if (!is_array($duration)) {
	            return false;
	        }
	        foreach ($duration as $key => $value) {
	            $segment = $value . '' . $key;
	            $array[] = $segment;
	        }
	        $str = implode(' ', $array);
	        return $str;
	    }
	}

	/* LCA */
	// check is admin
	$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$sid."'");
	$res1->fetchInto($user);
	$user_id = $user["user_id"];
	$res2 =& $pearDB->query("SELECT contact_admin FROM contact WHERE contact_id = '".$user_id."'");
	$res2->fetchInto($admin);
	$is_admin = 0;
	$is_admin = $admin["contact_admin"];

	// if is admin -> lca
	if(!$is_admin){
		$_POST["sid"] = $sid;
		$lca =  getLCAHostByName($pearDB);
		$lcaSTR = getLCAHostStr($lca["LcaHost"]);
	}


	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();

	$DBRESULT_OPT =& $pearDB->query("SELECT color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
//	$DBRESULT_OPT =& $pearDB->query("SELECT color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
	if (PEAR::isError($DBRESULT_OPT))
		print "DB Error : ".$DBRESULT_OPT->getDebugInfo()."<br />";
	$DBRESULT_OPT->fetchInto($general_opt);

	$tab_color_service = array();
	$tab_color_service[0] = $general_opt["color_ok"];
	$tab_color_service[1] = $general_opt["color_warning"];
	$tab_color_service[2] = $general_opt["color_critical"];
	$tab_color_service[3] = $general_opt["color_unknown"];
	$tab_color_service[4] = $general_opt["color_pending"];

	$tab_color_host = array();
	$tab_color_host[0] = $general_opt["color_up"];
	$tab_color_host[1] = $general_opt["color_down"];
	$tab_color_host[2] = $general_opt["color_unreachable"];

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");


	/* Get Host status */
	$rq1 = "SELECT nhs.current_state," .
			" nhs.problem_has_been_acknowledged, " .
			" nhs.passive_checks_enabled," .
			" nhs.active_checks_enabled," .
			" nhs.notifications_enabled," .
			" unix_timestamp(nhs.last_state_change) as last_state_change," .
			" nhs.output," .
			" unix_timestamp(nhs.last_check) as last_check," .
			" nh.address," .
			" no.name1 as host_name," .
			" nh.action_url," .
			" nh.notes_url," .
			" nh.icon_image," .
			" nh.icon_image_alt" .
			" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no, ".$ndo_base_prefix."hosts nh" .
			" WHERE no.object_id = nhs.host_object_id and nh.host_object_id = no.object_id " .
			" AND no.name1 not like 'OSL_Module'".
			" AND no.is_active = 1 AND no.objecttype_id = 1 AND nh.config_type = 1";

		if(!$is_admin)
			$rq1 .= " AND no.name1 IN (".$lcaSTR." )";


	if($search != ""){
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
	}

	if($o == "hpb")
		$rq1 .= " AND nhs.current_state != 0 ";

	if($instance != "ALL")
		$rq1 .= " AND no.instance_id = ".$instance;


	switch($sort_type){
			case 'host_name' : $rq1 .= " order by no.name1 ". $order;  break;
			case 'current_state' : $rq1 .= " order by nhs.current_state ". $order.",no.name1 ";  break;
			case 'last_state_change' : $rq1 .= " order by nhs.last_state_change ". $order.",no.name1 ";  break;
			case 'last_check' : $rq1 .= " order by nhs.last_check ". $order.",no.name1 ";  break;
			case 'current_attempt' : $rq1 .= " order by nhs.current_check_attempt ". $order.",no.name1 ";  break;
			case 'ip' : $rq1 .= " order by nh.address ". $order.",no.name1 ";  break;
			case 'plugin_output' : $rq1 .= " order by nhs.output ". $order.",no.name1 ";  break;
			default : $rq1 .= " order by no.name1 ";  break;
	}

	$rq_pagination = $rq1;

	/* Get Pagination Rows */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	if (PEAR::isError($DBRESULT_PAGINATION))
		print "DB Error : ".$DBRESULT_PAGINATION->getDebugInfo()."<br />";
	$numRows = $DBRESULT_PAGINATION->numRows();
	/* End Pagination Rows */


	$rq1 .= " LIMIT ".($num * $limit).",".$limit;


	$buffer .= '<reponse>';
	$buffer .= '<i>';
	$buffer .= '<numrows>'.$numRows.'</numrows>';
	$buffer .= '<num>'.$num.'</num>';
	$buffer .= '<limit>'.$limit.'</limit>';
	$buffer .= '<p>'.$p.'</p>';
	$buffer .= '</i>';
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	$class = "list_one";
	$ct = 0;
	$flag = 0;
	while($DBRESULT_NDO1->fetchInto($ndo))
	{
		$color_host = $tab_color_host[$ndo["current_state"]]; //"#FF0000";
		$passive = 0;
		$active = 1;
		$last_check = " ";
		$duration = " ";
		if($ndo["last_state_change"] > 0)
			$duration = Duration::toString(time() - $ndo["last_state_change"]);
		if($class == "list_one")
			$class = "list_two";
		else
			$class = "list_one";
		$host_status[$ndo["host_name"]] = $ndo;
		$buffer .= '<l class="'.$class.'">';
		$buffer .= '<o>'. $ct++ . '</o>';
		$buffer .= '<hc><![CDATA['. $color_host . ']]></hc>';
		$buffer .= '<f><![CDATA['. $flag . ']]></f>';
		$buffer .= '<hn><![CDATA['. $ndo["host_name"]  . ']]></hn>';
		$buffer .= '<a><![CDATA['. $ndo["address"]  . ']]></a>';
		$buffer .= '<ou><![CDATA['. $ndo["output"]  . ']]></ou>';
		$buffer .= '<lc>'. date($date_time_format_status, $ndo["last_check"])  . '</lc>';
		$buffer .= '<cs>'. $tab_status_host[$ndo["current_state"]] . '</cs>';
        $buffer .= '<pha>'. $ndo["problem_has_been_acknowledged"] .'</pha>';
        $buffer .= '<pce>'.$ndo["passive_checks_enabled"] .'</pce>';
        $buffer .= '<ace>'.$ndo["active_checks_enabled"] .'</ace>';
        $buffer .= '<lsc>'.$duration.'</lsc>';
        $buffer .= '<ha>'.$ndo["problem_has_been_acknowledged"]  .'</ha>';///
        $buffer .= '<hae>'.$ndo["active_checks_enabled"] .'</hae>';///
        $buffer .= '<hpe>'.$ndo["passive_checks_enabled"]  .'</hpe>';///
		$buffer .= '<ne>'. $ndo["notifications_enabled"] . '</ne>';
		$buffer .= '</l>';
	}
	/* end */

	if(!$ct){
		$buffer .= '<infos>';
		$buffer .= 'none';
		$buffer .= '</infos>';
	}

	$buffer .= '</reponse>';
	header('Content-Type: text/xml');
	echo $buffer;

?>
