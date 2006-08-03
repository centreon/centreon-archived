<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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
	require_once ("../../../../class/Session.class.php");
	require_once ("../../../../class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];

	/* Connect to Oreon DB */
	
	include("../../../../oreon.conf.php");
	is_file ("../../../../lang/".$oreon->user->get_lang().".php") ? include_once ("../../../../lang/".$oreon->user->get_lang().".php") : include_once ("../../../../lang/en.php");	
	require_once "../../../common/common-Func.php";
	
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

		if (strcmp($_GET["host_name"], "Meta_Module")){
			$host_id = getMyHostID($_GET["host_name"]);
			$service_id = getMyServiceID($_GET["service_description"], $host_id);
		}
		if (!isset($_GET["template_id"])){
			if (isset($service_id))
				$template_id = getDefaultGraph($service_id, 1);
			else {
				$tab = split("_", $_GET["service_description"]);
				$template_id = getDefaultMetaGraph($tab[1], 1);
			}	
		} else 
			$template_id = $_GET["template_id"];
		
		include_once("../../../../DBPerfparseConnect.php");
		
		$command_line = " graph - --start=".$_GET["start"]. " --end=".$_GET["end"];
		
		# get all template infos
		$res =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
		$res->fetchInto($GraphTemplate);
		if (isset($_GET["service_description"]) && strstr($_GET["service_description"], "meta_")){
			$tab_name = spliti("_", $_GET["service_description"]);
			$res_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE meta_id = '".$tab_name[1]."'");
			$res_meta->fetchInto($meta_data);
			$command_line .= " --interlaced --width=".$GraphTemplate["width"]." --height=".$GraphTemplate["height"]." --title='Graph Meta Service ".$meta_data["meta_name"]."' --vertical-label='".$GraphTemplate["vertical_label"]."' ";
		} else
			$command_line .= " --interlaced --width=".$GraphTemplate["width"]." --height=".$GraphTemplate["height"]." --title='Graph ".$_GET["service_description"]." on Host ".$_GET["host_name"]."' --vertical-label='".$GraphTemplate["vertical_label"]."' ";
			
		# Init Graph Template Value
		if (isset($GraphTemplate["bg_grid_color"]) && $GraphTemplate["bg_grid_color"])
			$command_line .= "--color CANVAS".$GraphTemplate["bg_grid_color"]." ";
		if (isset($GraphTemplate["bg_color"]) && $GraphTemplate["bg_color"])
			$command_line .= "--color BACK".$GraphTemplate["bg_color"]." ";
		if (isset($GraphTemplate["police_color"]) && $GraphTemplate["police_color"])
			$command_line .= "--color FONT".$GraphTemplate["police_color"]." ";
		if (isset($GraphTemplate["grid_main_color"]) && $GraphTemplate["grid_main_color"])
			$command_line .= "--color MGRID".$GraphTemplate["grid_main_color"]." ";
		if (isset($GraphTemplate["grid_sec_color"]) && $GraphTemplate["grid_sec_color"])
			$command_line .= "--color GRID".$GraphTemplate["grid_sec_color"]." ";
		if (isset($GraphTemplate["contour_cub_color"]) && $GraphTemplate["contour_cub_color"])
			$command_line .= "--color FRAME".$GraphTemplate["contour_cub_color"]." ";
		if (isset($GraphTemplate["col_arrow"]) && $GraphTemplate["col_arrow"])
			$command_line .= "--color ARROW".$GraphTemplate["col_arrow"]." ";
		if (isset($GraphTemplate["col_top"]) && $GraphTemplate["col_top"])
			$command_line .= "--color SHADEA".$GraphTemplate["col_top"]." ";
		if (isset($GraphTemplate["col_bot"]) && $GraphTemplate["col_bot"])
			$command_line .= "--color SHADEB".$GraphTemplate["col_bot"]." ";	
		
		if (isset($GraphTemplate["lower_limit"]) && $GraphTemplate["lower_limit"] != NULL)
			$command_line .= "--lower-limit ".$GraphTemplate["lower_limit"]." ";
		if (isset($GraphTemplate["upper_limit"]) && $GraphTemplate["upper_limit"] != NULL)
			$command_line .= "--upper-limit ".$GraphTemplate["upper_limit"]." ";
		
		if ((isset($GraphTemplate["lower_limit"]) && $GraphTemplate["lower_limit"] != NULL) || (isset($GraphTemplate["upper_limit"]) && $GraphTemplate["upper_limit"] != NULL))
			$command_line .= "--rigid ";
		
		
		# Init DS template For each curv
		$ppMetrics = array();
		$res =& $pearDBpp->query("SELECT DISTINCT metric_id, metric, unit FROM perfdata_service_metric WHERE host_name = '".$_GET["host_name"]."' AND service_description = '".$_GET["service_description"]."'");
		$cpt = 0;
		while($res->fetchInto($ppMetric))	{
			$ppMetrics[$ppMetric["metric_id"]]["metric"] = $ppMetric["metric"];
			$ppMetrics[$ppMetric["metric_id"]]["unit"] = $ppMetric["unit"];
			$ds = getDefaultDS($template_id, $cpt, 1);
			$ppMetrics[$ppMetric["metric_id"]]["ds_id"] = $ds;

			$res_ds =& $pearDB->query("SELECT * FROM giv_components_template WHERE compo_id = '".$ds."'");
			$res_ds->fetchInto($ds_data);
			foreach ($ds_data as $key => $ds_d)
				$ppMetrics[$ppMetric["metric_id"]][$key] = $ds_d;
			
			$ppMetrics[$ppMetric["metric_id"]]["legend"] = $ds_data["name"];
			if (strcmp($ppMetric["unit"], ""))
				$ppMetrics[$ppMetric["metric_id"]]["legend"] .= " (".$ppMetric["unit"].") ";
			$ppMetrics[$ppMetric["metric_id"]]["legend_len"] = strlen($ppMetrics[$ppMetric["metric_id"]]["legend"]);
			$cpt++;
		}
		$res->free();	

		$cpt = 0;
		$longer = 0;
		foreach ($ppMetrics as $tm){
			if (isset($tm["ds_invert"]) && $tm["ds_invert"]){
				$command_line .= " DEF:va".$cpt."=".$oreon->optGen["oreon_path"]."filesGeneration/graphs/simpleRenderer/rrdDB/".str_replace(" ", "-",$_GET["host_name"])."_".str_replace(" ", "-",$_GET["service_description"]).".rrd:".$tm["metric"].":LAST ";
				$command_line .= " CDEF:v".$cpt."=va".$cpt.",-1,* ";
			} else 
				$command_line .= " DEF:v".$cpt."=".$oreon->optGen["oreon_path"]."filesGeneration/graphs/simpleRenderer/rrdDB/".str_replace(" ", "-",$_GET["host_name"])."_".str_replace(" ", "-",$_GET["service_description"]).".rrd:".$tm["metric"].":LAST ";
			if ($tm["legend_len"] > $longer)
				$longer = $tm["legend_len"];
			$cpt++;
		}	
		
		# Add Comments
		$rrd_time  = addslashes(date("d\/m\/Y G:i", $_GET["start"])) ;
		$rrd_time = str_replace(":", "\:", $rrd_time);
		$rrd_time2 = addslashes(date("d\/m\/Y G:i", $_GET["end"])) ;
		$rrd_time2 = str_replace(":", "\:", $rrd_time2);
		$command_line .= " COMMENT:\" \\c\" COMMENT:\" From  $rrd_time to $rrd_time2 \\c\" COMMENT:\" \\c\" ";
		
		# Create Legende
		$cpt = 1;
		foreach ($ppMetrics as $key => $tm){
			if ($ppMetrics[$key]["ds_filled"])
				$command_line .= " AREA:v".($cpt-1)."".$tm["ds_color_area"].$tm["ds_transparency"]." ";	
			$command_line .= " LINE".$tm["ds_tickness"].":v".($cpt-1);
			$command_line .= $tm["ds_color_line"].":\"";
			$command_line .= $ppMetrics[$key]["legend"];
			for ($i = $ppMetrics[$key]["legend_len"]; $i != $longer + 1; $i++)
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