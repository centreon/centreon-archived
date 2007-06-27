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

	function escape_command($command) {
		return ereg_replace("(\\\$|`)", "", $command);
	}

	require_once 'DB.php';
	require_once ("../../../../../class/Session.class.php");
	require_once ("../../../../../class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];

	/* Connect to Oreon DB */

	include("../../../../../oreon.conf.php");
	is_file ("../../../../../lang/".$oreon->user->get_lang().".php") ? include_once ("../../../../../lang/".$oreon->user->get_lang().".php") : include_once ("../../../../../lang/en.php");
	require_once "../../../../common/common-Func.php";

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
	if (PEAR::isError($pearDB))
	    die("Unable to connect : " . $pearDB->getMessage());

	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);

	$session =& $pearDB->query("SELECT * FROM `session` WHERE session_id = '".$_GET["session_id"]."'");
	if (!$session->numRows()){
		exit;
	} else {
		$session->free();
		include_once("../../../../../DBOdsConnect.php");
		
		$DBRESULT =& $pearDBO->query("SELECT RRDdatabase_path FROM config LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		$DBRESULT->fetchInto($config);
		$RRDdatabase_path = $config["RRDdatabase_path"];
		$DBRESULT->free();
		unset($config);
		
		$DBRESULT =& $pearDBO->query("SELECT * FROM index_data WHERE id = '".$_GET["index"]."' LIMIT 1");
		$DBRESULT->fetchInto($index_data_ODS);
		if (!isset($_GET["template_id"])|| !$_GET["template_id"]){
			$host_id = getMyHostID($index_data_ODS["host_name"]);
			$svc_id = getMyServiceID($index_data_ODS["service_description"], $host_id);	
			$template_id = getDefaultGraph($svc_id, 1);
		} else
			$template_id = $_GET["template_id"];
		$DBRESULT->free();	
		$command_line = " graph - --start=".$_GET["start"]. " --end=".$_GET["end"];
		
		# get all template infos
		$DBRESULT =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
		$DBRESULT->fetchInto($GraphTemplate);
		
		$index_data_ODS["service_description"] = str_replace("#S#", "/", $index_data_ODS["service_description"]);
		$index_data_ODS["service_description"] = str_replace("#BS#", "\\", $index_data_ODS["service_description"]);
		
		$command_line .= " --interlaced --imgformat PNG --width=".$GraphTemplate["width"]." --height=".$GraphTemplate["height"]." --title='".$index_data_ODS["service_description"]." graph on ".$index_data_ODS["host_name"]."' --vertical-label='".$GraphTemplate["vertical_label"]."' ";
		if ($oreon->optGen["rrdtool_version"] == "1.2")
			$command_line .= " --slope-mode ";

		if (isset($GraphTemplate["lower_limit"]) && $GraphTemplate["lower_limit"] != NULL)
			$command_line .= "--lower-limit ".$GraphTemplate["lower_limit"]." ";
		if (isset($GraphTemplate["upper_limit"]) && $GraphTemplate["upper_limit"] != NULL)
			$command_line .= "--upper-limit ".$GraphTemplate["upper_limit"]." ";
		if ((isset($GraphTemplate["lower_limit"]) && $GraphTemplate["lower_limit"] != NULL) || (isset($GraphTemplate["upper_limit"]) && $GraphTemplate["upper_limit"] != NULL))
			$command_line .= "--rigid ";
		
		$metrics = array();
		$DBRESULT =& $pearDBO->query("SELECT metric_id, metric_name, unit_name FROM metrics WHERE index_id = '".$_GET["index"]."' ORDER BY metric_id");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		$pass = 1;
		while ($DBRESULT->fetchInto($metric)){
			if (isset($_GET["metric"]) && isset($_GET["metric"][$metric["metric_id"]]))
				$pass = 0;
		}
		$DBRESULT->free();
				
		$metrics = array();
		$DBRESULT =& $pearDBO->query("SELECT metric_id, metric_name, unit_name FROM metrics WHERE index_id = '".$_GET["index"]."' ORDER BY metric_id");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getDebugInfo();
		$cpt = 0;
		$metrics = array();		
		while ($DBRESULT->fetchInto($metric)){
			$metric["metric_name"] = str_replace("#S#", "\/", $metric["metric_name"]);
			$metric["metric_name"] = str_replace("#BS#", "\\", $metric["metric_name"]);
			if (!isset($_GET["metric"]) || (isset($_GET["metric"]) && isset($_GET["metric"][$metric["metric_id"]])) || isset($_GET["index_id"]) || $pass){
				$metrics[$metric["metric_id"]]["metric_id"] = $metric["metric_id"];
				$metrics[$metric["metric_id"]]["metric"] = str_replace("/", "", $metric["metric_name"]);
				$metrics[$metric["metric_id"]]["unit"] = $metric["unit_name"];
				$ds = getDefaultDS($template_id, $cpt, 1);						
				$metrics[$metric["metric_id"]]["ds_id"] = $ds;
				$res_ds =& $pearDB->query("SELECT * FROM giv_components_template WHERE compo_id = '".$ds."'");
				$res_ds->fetchInto($ds_data);
				foreach ($ds_data as $key => $ds_d){
					if ($key == "ds_transparency")
						$metrics[$metric["metric_id"]][$key] = dechex(255-($ds_d*255)/100);
					else
						$metrics[$metric["metric_id"]][$key] = $ds_d;
				}
				if (preg_match('/DS/', $ds_data["ds_name"], $matches))
					$metrics[$metric["metric_id"]]["legend"] = $metric["metric_name"];
                else
                	$metrics[$metric["metric_id"]]["legend"] = $ds_data["ds_name"];
				if (strcmp($metric["unit_name"], ""))
					$metrics[$metric["metric_id"]]["legend"] .= " (".$metric["unit_name"].") ";
				$metrics[$metric["metric_id"]]["legend_len"] = strlen($metrics[$metric["metric_id"]]["legend"]);
			}
			$cpt++;
		}
		$DBRESULT->free();
		$cpt = 0;
		$longer = 0;
		foreach ($metrics as $key => $tm){
			if (isset($tm["ds_invert"]) && $tm["ds_invert"])
				$command_line .= " DEF:va".$cpt."=".$RRDdatabase_path.$key.".rrd:".$metrics[$key]["metric"].":AVERAGE CDEF:v".$cpt."=va".$cpt.",-1,* ";
			else
				$command_line .= " DEF:v".$cpt."=".$RRDdatabase_path.$key.".rrd:".$metrics[$key]["metric"].":AVERAGE ";
			if ($tm["legend_len"] > $longer)
				$longer = $tm["legend_len"];
			$cpt++;
		}

		# Create Legende
		$i = 0;
		$cpt = 1;
		foreach ($metrics as $key => $tm){
			if ($metrics[$key]["ds_filled"])
				$command_line .= " AREA:v".($cpt-1)."".$tm["ds_color_area"].$tm["ds_transparency"]." ";
			$command_line .= " LINE".$tm["ds_tickness"].":v".($cpt-1);
			$command_line .= $tm["ds_color_line"].":\"";
			$command_line .= $metrics[$key]["legend"];
			
			for ($i = $metrics[$key]["legend_len"]; $i != $longer + 1; $i++)
				$command_line .= " ";
			$command_line .= "\"";
			if ($tm["ds_average"]){
				$command_line .= " GPRINT:v".($cpt-1).":AVERAGE:\"Average\:%8.2lf%s";
				$tm["ds_min"] || $tm["ds_max"] || $tm["ds_last"] ? $command_line .= "\"" : $command_line .= "\\l\" ";
			}
			if ($tm["ds_min"]){
				$command_line .= " GPRINT:v".($cpt-1).":MIN:\"Min\:%8.2lf%s";
				$tm["ds_max"] || $tm["ds_last"] ? $command_line .= "\"" : $command_line .= "\\l\" ";
			}
			if ($tm["ds_max"]){
				$command_line .= " GPRINT:v".($cpt-1).":MAX:\"Max\:%8.2lf%s";
				$tm["ds_last"] ? $command_line .= "\"" : $command_line .= "\\l\" ";
			}
			if ($tm["ds_last"])
				$command_line .= " GPRINT:v".($cpt-1).":LAST:\"Last\:%8.2lf%s\\l\"";
			$cpt++;
		}

		$command_line = $oreon->optGen["rrdtool_path_bin"].$command_line." 2>&1";
		$command_line = escape_command("$command_line");
		if ( $oreon->optGen["debug_rrdtool"] == "1" )
			error_log("[" . date("d/m/Y H:s") ."] RDDTOOL : $command_line \n", 3, $oreon->optGen["debug_path"]."rrdtool.log");

		//print $command_line;
		$fp = popen($command_line  , 'r');
		if (isset($fp) && $fp ) {
			$str ='';
			while (!feof ($fp)) {
		  		$buffer = fgets($fp, 4096);
		 		$str = $str . $buffer ;
			}
			print $str;
		}
	}
?>