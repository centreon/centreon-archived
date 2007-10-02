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

	function get_services($host_name){
		global $pearDBndo;
		global $general_opt;
		global $o;

		$rq = "SELECT no.name1, no.name2 as service_name, nss.current_state" .
				" FROM `" .$general_opt["ndo_base_prefix"]."_servicestatus` nss, `" .$general_opt["ndo_base_prefix"]."_objects` no" .
				" WHERE no.object_id = nss.service_object_id" ;

		if($o == "svcgridHG_pb" || $o == "svcOVHG_pb")
			$rq .= " AND nss.current_state != 0" ;

		if($o == "svcgridHG_ack_0" || $o == "svcOVHG_ack_0")
			$rq .= " AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" ;

		if($o == "svcgridHG_ack_1" || $o == "svcOVHG_ack_1")
			$rq .= " AND nss.problem_has_been_acknowledged = 1" ;


		$rq .= " AND no.object_id" .
				" IN (" .
				
				" SELECT nno.object_id" .
				" FROM ndo_objects nno" .
				" WHERE nno.objecttype_id =2" .
				" AND nno.name1 = '".$host_name."'" .
				" )";
					
		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";	
		$tab = array();
		while($DBRESULT->fetchInto($svc)){

			$tab[$svc["service_name"]] = $svc["current_state"];
		}
		return($tab);
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


	$rq1 = "SELECT hg.alias, no.name1 as host_name, hgm.servicegroup_id, hgm.service_object_id, ss.current_state".
			" FROM " .$general_opt["ndo_base_prefix"]."_servicegroups hg," .$general_opt["ndo_base_prefix"]."_servicegroup_members hgm, " .$general_opt["ndo_base_prefix"]."_servicestatus ss, " .$general_opt["ndo_base_prefix"]."_objects no".
			" WHERE ss.service_object_id = hgm.service_object_id".
			" AND no.object_id = hgm.service_object_id" .
			" AND hgm.servicegroup_id = hg.servicegroup_id";

	if($o == "svcgridHG_pb" || $o == "svcOVHG_pb")
		$rq1 .= " AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$general_opt["ndo_base_prefix"]."_objects nno," .$general_opt["ndo_base_prefix"]."_servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0" .
				")";

	if($o == "svcgridHG_ack_0" || $o == "svcOVHG_ack_0")
		$rq1 .= " AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$general_opt["ndo_base_prefix"]."_objects nno," .$general_opt["ndo_base_prefix"]."_servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" .
				")";

	if($o == "svcgridHG_ack_1" || $o == "svcOVHG_ack_1")
		$rq1 .= " AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$general_opt["ndo_base_prefix"]."_objects nno," .$general_opt["ndo_base_prefix"]."_servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1" .
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
		$tab_svc = get_services($ndo["host_name"]);
		$tab_final[$ndo["host_name"]]["tab_svc"] = $tab_svc;
		$tab_final[$ndo["host_name"]]["cs"] = $ndo["current_state"];
		$tab_final[$ndo["host_name"]]["hg_name"] = $ndo["alias"];
	}

	$hg = "";
	foreach($tab_final as $host_name => $tab)
	{
		if($hg != $tab["hg_name"]){

			if($hg != "")
				$buffer .= '</hg>';

			$hg = $tab["hg_name"];
			$buffer .= '<hg>';
			$buffer .= '<hgn>'. $tab["hg_name"]  .'</hgn>';
		}
		$buffer .= '<l class="'.$class.'">';
		foreach ($tab["tab_svc"] as $svc => $state) {
			$buffer .= '<svc>';
			$buffer .= '<sn>'. $svc . '</sn>';
			$buffer .= '<sc>'. $tab_color_service[$state] . '</sc>';
			$buffer .= '</svc>';
		}
		$buffer .= '<o>'. $ct++ . '</o>';
		$buffer .= '<hn>'. $host_name  . '</hn>';
		$buffer .= '<hs>'. $tab_status_host[$tab["cs"]]  . '</hs>';
		$buffer .= '<hc>'. $tab_color_host[$tab["cs"]]  . '</hc>';
		$buffer .= '</l>';
	}
	if($hg != "")
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
