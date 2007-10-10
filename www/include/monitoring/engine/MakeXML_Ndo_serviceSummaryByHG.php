<?
/**
Oreon is developped with GPL Licence 2.0 :
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

	/* security check 1/2*/
	if($oreonPath == '@INSTALL_DIR_OREON@')
		get_error('please set your oreonPath');
	/* security end 1/2 */

	include_once($oreonPath . "www/oreon.conf.php");
	include_once($oreonPath . "www/DBconnect.php");

	/* security check 2/2*/
	if(isset($_GET["sid"]) && !check_injection($_GET["sid"])){

		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($res->fetchInto($session)){
			;
		}else
			get_error('bad session id');
	}
	else
		get_error('need session identifiant !');
	/* security end 2/2 */

	/* requisit */
	if(isset($_GET["num"]) && !check_injection($_GET["num"])){
		$num = htmlentities($_GET["num"]);
	}else
		get_error('num unknown');
	if(isset($_GET["limit"]) && !check_injection($_GET["limit"])){
		$limit = htmlentities($_GET["limit"]);
	}else
		get_error('limit unknown');


	/* options */
	if(isset($_GET["search"]) && !check_injection($_GET["search"])){
		$search = htmlentities($_GET["search"]);
	}else
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


	include_once($oreonPath . "www/DBndoConnect.php");
	$DBRESULT_OPT =& $pearDB->query("SELECT ndo_base_prefix,color_ok,color_warning,color_critical,color_unknown,color_pending,color_up,color_down,color_unreachable FROM general_opt");
	if (PEAR::isError($DBRESULT_OPT))
		print "DB Error : ".$DBRESULT_OPT->getDebugInfo()."<br>";
	$DBRESULT_OPT->fetchInto($general_opt);

	function get_services_status($host_name, $status){
		global $pearDBndo;
		global $general_opt;
		global $o;
	
		$rq = "SELECT count( nss.service_object_id ) AS nb".
		" FROM " .$general_opt["ndo_base_prefix"]."_servicestatus nss".
		" WHERE nss.current_state = '".$status."'";
		
		if($o == "svcSumHG_pb")
			$rq .= " AND nss.current_state != 0";
		if($o == "svcSumHG_ack_0")
			$rq .= " AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0";
	
		if($o == "svcSumHG_ack_1")
			$rq .= " AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0";
	
		$rq .= " AND nss.service_object_id".
		" IN (".		
		" SELECT nno.object_id".
		" FROM " .$general_opt["ndo_base_prefix"]."_objects nno".
		" WHERE nno.objecttype_id =2".
		" AND nno.name1 = '".$host_name."'".
		" )";
					
		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";	
		$DBRESULT->fetchInto($tab);

		return($tab["nb"]);
	}


	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();

	
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


	$rq1 = "SELECT hg.alias, no.name1 as host_name, hgm.hostgroup_id, hgm.host_object_id, hs.current_state".
			" FROM " .$general_opt["ndo_base_prefix"]."_hostgroups hg," .$general_opt["ndo_base_prefix"]."_hostgroup_members hgm, " .$general_opt["ndo_base_prefix"]."_hoststatus hs, " .$general_opt["ndo_base_prefix"]."_objects no".
			" WHERE hs.host_object_id = hgm.host_object_id".
			" AND no.object_id = hgm.host_object_id" .
			" AND hgm.hostgroup_id = hg.hostgroup_id".
			" AND no.name1 not like 'OSL_Module'";

	if($o == "svcSumHG_pb")
		$rq1 .= " AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$general_opt["ndo_base_prefix"]."_objects nno," .$general_opt["ndo_base_prefix"]."_servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0" .
				")";

	if($o == "svcSumHG_ack_0")
		$rq1 .= " AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$general_opt["ndo_base_prefix"]."_objects nno," .$general_opt["ndo_base_prefix"]."_servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" .
				")";

	if($o == "svcSumHG_ack_1")
		$rq1 .= " AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$general_opt["ndo_base_prefix"]."_objects nno," .$general_opt["ndo_base_prefix"]."_servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0" .
				")";
	if($search != ""){
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
	}



	$rq_pagination = $rq1;
	/* Get Pagination Rows */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	if (PEAR::isError($DBRESULT_PAGINATION))
		print "DB Error : ".$DBRESULT_PAGINATION->getDebugInfo()."<br>";	
	$numRows = $DBRESULT_PAGINATION->numRows();
	/* End Pagination Rows */


	$rq1 .= " ORDER BY hg.alias";
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	$buffer .= '<reponse>';
	$buffer .= '<i>';
	$buffer .= '<numrows>'.$numRows.'</numrows>';
	$buffer .= '<num>'.$num.'</num>';
	$buffer .= '<limit>'.$limit.'</limit>';
	$buffer .= '<p>'.$p.'</p>';

	if($o == "svcOVHG")
		$buffer .= '<s>1</s>';
	else
		$buffer .= '<s>0</s>';
	
	$buffer .= '</i>';
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br>";	
	$class = "list_one";
	$ct = 0;
	$flag = 0;


	$tab_final = array();
	while($DBRESULT_NDO1->fetchInto($ndo))
	{
		if($o != "svcSum_pb" && $o != "svcSum_ack_1"  && $o !=  "svcSum_ack_0")
			$tab_final[$ndo["host_name"]]["nb_service_k"] = 0 + get_services_status($ndo["host_name"], 0);
		else
			$tab_final[$ndo["host_name"]]["nb_service_k"] = 0;
		$tab_final[$ndo["host_name"]]["nb_service_w"] = 0 + get_services_status($ndo["host_name"], 1);
		$tab_final[$ndo["host_name"]]["nb_service_c"] = 0 + get_services_status($ndo["host_name"], 2);
		$tab_final[$ndo["host_name"]]["nb_service_u"] = 0 + get_services_status($ndo["host_name"], 3);
		$tab_final[$ndo["host_name"]]["nb_service_p"] = 0 + get_services_status($ndo["host_name"], 4);
		$tab_final[$ndo["host_name"]]["cs"] = $ndo["current_state"];
		$tab_final[$ndo["host_name"]]["hg_name"] = $ndo["alias"];
	}

	$hg = "";
	foreach($tab_final as $host_name => $tab)
	{
		if($class == "list_one")
			$class = "list_two";
		else
			$class = "list_one";


		if($hg != $tab["hg_name"]){

			if($hg != "")
				$buffer .= '</hg>';

			$hg = $tab["hg_name"];
			$buffer .= '<hg>';
			$buffer .= '<hgn><![CDATA['. $tab["hg_name"]  .']]></hgn>';
		}
		$buffer .= '<l class="'.$class.'">';

		$buffer .= '<sk><![CDATA['. $tab["nb_service_k"]  . ']]></sk>';
		$buffer .= '<skc><![CDATA['. $tab_color_service[0]  . ']]></skc>';
		$buffer .= '<sw><![CDATA['. $tab["nb_service_w"]  . ']]></sw>';
		$buffer .= '<swc><![CDATA['. $tab_color_service[1]  . ']]></swc>';
		$buffer .= '<sc><![CDATA['. $tab["nb_service_c"]  . ']]></sc>';
		$buffer .= '<scc><![CDATA['. $tab_color_service[2]  . ']]></scc>';
		$buffer .= '<su><![CDATA['. $tab["nb_service_u"]  . ']]></su>';
		$buffer .= '<suc><![CDATA['. $tab_color_service[3]  . ']]></suc>';
		$buffer .= '<sp><![CDATA['. $tab["nb_service_p"]  . ']]></sp>';
		$buffer .= '<spc><![CDATA['. $tab_color_service[4]  . ']]></spc>';


		$buffer .= '<o>'. $ct++ . '</o>';
		$buffer .= '<hn><![CDATA['. $host_name  . ']]></hn>';
		$buffer .= '<hs><![CDATA['. $tab_status_host[$tab["cs"]]  . ']]></hs>';
		$buffer .= '<hc><![CDATA['. $tab_color_host[$tab["cs"]]  . ']]></hc>';
		$buffer .= '</l>';
	}
	$buffer .= '</hg>';
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
