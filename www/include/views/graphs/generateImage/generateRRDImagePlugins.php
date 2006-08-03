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
		exit;		// Session expired or invalide -> exit 
	} else {		// Session Ok -> create image		
		
		$tab_id = split("\_", $_GET["database"]);
		$res =& $pearDB->query("SELECT service_description FROM service WHERE service_id = '".$tab_id[1]."'");
		$res->fetchInto($r);
		$service_description = $r["service_description"];
		$res =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$tab_id[0]."'");
		$res->fetchInto($r);
		$host_name = $r["host_name"];
		
		if (!$_GET["template_id"])
			$template_id = getDefaultGraph($tab_id[1], 2);
		else 
			$template_id = $_GET["template_id"];
			
		$return = shell_exec($oreon->optGen['rrdtool_path_bin'] . " info " . $oreon->optGen["oreon_rrdbase_path"].$_GET["database"] . " ");
		
		// Recupe le Graph template
		$tab_return = preg_split("/\n/", $return);
		$tab_ds = array();
		foreach ($tab_return as $tr)
			if (preg_match("/^ds\[([a-zA-Z0-9]*)\].*/", $tr, $matches))
				$tab_ds[$matches['1']] = $matches['1'];
		
		// Recupe les noms des legendes
		// Recupe le DS (nombre et name)
	
		$ppMetrics = array();	
		$cpt = 0;
		foreach ($tab_ds as $t){		
			# Get Metric Infos 
			$ppMetrics[$t] = array();
			$ppMetrics[$t]["ds_id"] = getDefaultDS($template_id, $cpt, 2);
			# Get DS Data
			$res_ds =& $pearDB->query("SELECT * FROM giv_components_template WHERE compo_id = '".$ppMetrics[$t]["ds_id"]."'");
			$res_ds->fetchInto($ds_data);
			foreach ($ds_data as $key => $ds_d)
				$ppMetrics[$t][$key] = $ds_d;
			$ppMetrics[$t]["legend"] = $ds_data["name"];
			$ppMetrics[$t]["legend_len"] = strlen($ppMetrics[$t]["legend"]);
			$cpt++;
		}
	
		$command_line = " graph - ";
		if (isset($_GET["start"]) && $_GET["start"])
			$command_line .= " --start=".$_GET["start"]. " --end=".$_GET["end"];
		else {
			if (!$template_id)
				$period = 86400;
			else {	
				$res =& $pearDB->query("SELECT period FROM giv_graphs_template WHERE graph_id = '".$template_id."'");
				$res->fetchInto($graph);
				$period = $graph["period"];
			}
			$_GET["start"] = time() - ($period + 120);
			$_GET["end"] = time();
			$command_line .= " --start=".$_GET["start"]. " --end=".$_GET["end"];
		}
			
		# Get Graph Data
		
		$res =& $pearDB->query("SELECT * FROM `giv_graphs_template` WHERE graph_id = '".$template_id."'");
		$res->fetchInto($GraphTemplate);
		
		if (isset($_GET["nbgraph"]))
	    	if ($_GET["nbgraph"] >= 2){
	        	$GraphTemplate["height"] = $GraphTemplate["height"]/2;
	        	$GraphTemplate["width"] = $GraphTemplate["width"]/2;
			}
		# Create command line for graph properties
		
		$command_line .= " --interlaced --width=".$GraphTemplate["width"]." --height=".$GraphTemplate["height"]." --title='Graph ".$service_description." on ".$host_name." ' --vertical-label='".$GraphTemplate["vertical_label"]."' ";
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
		//else
		//	$command_line .= "--alt-autoscale-max "; 
		if ((isset($GraphTemplate["lower_limit"]) && $GraphTemplate["lower_limit"] != NULL) || (isset($GraphTemplate["upper_limit"]) && $GraphTemplate["upper_limit"] != NULL))
			$command_line .= "--rigid ";
			
		$cpt = 0;
		$longer = 0;
		foreach ($ppMetrics as $key => $tm){
			if (isset($tm["ds_invert"]) && $tm["ds_invert"]){
				$command_line .= " DEF:va".$cpt."=".$oreon->optGen["oreon_rrdbase_path"].$_GET["database"].":".$key.":AVERAGE ";
				$command_line .= " CDEF:v".$cpt."=va".$cpt.",-1,* ";
			} else 
				$command_line .= " DEF:v".$cpt."=".$oreon->optGen["oreon_rrdbase_path"].$_GET["database"].":".$key.":AVERAGE ";
			if ($tm["legend_len"] > $longer)
				$longer = $tm["legend_len"];
			$cpt++;
		}	
		
		if ($GraphTemplate["stacked"]){		
			$cpt = 0;
			$command_line .= " CDEF:total=";
			foreach ($ppMetrics as $key => $tm){
				if ($cpt)
					$command_line .= ",";
				$nameDEF = "v".$cpt;
				$command_line .= "TIME,".$_GET['start'].",GT,$nameDEF,$nameDEF,UN,0,$nameDEF,IF,IF";
				$cpt++;
			}
			for ($cpt2 = 1;$cpt2 != $cpt;$cpt2++)
				$command_line .= ",+";
			$command_line .= " ";
		}
		
		$rrd_time  = addslashes(date("d\/m\/Y G:i", $_GET["start"])) ;
		$rrd_time = str_replace(":", "\:", $rrd_time);
		$rrd_time2 = addslashes(date("d\/m\/Y G:i", $_GET["end"])) ;
		$rrd_time2 = str_replace(":", "\:", $rrd_time2);
		$command_line .= " COMMENT:\" \\c\" COMMENT:\" From  $rrd_time to $rrd_time2 \\c\" COMMENT:\" \\c\" ";
		
		$cpt = 1;
		foreach ($ppMetrics as $key => $tm){
			$space_added = NULL;
			$legend = "\"" . $ppMetrics[$key]["legend"];
			for ($i = $ppMetrics[$key]["legend_len"]; $i != $longer + 1; $i++)
				$legend .= " ";
			$legend .= "\"";
			if ($ppMetrics[$key]["ds_filled"] && $GraphTemplate["stacked"] == "0"){
				$command_line .= " AREA:v".($cpt-1)."".$tm["ds_color_area"].$tm["ds_transparency"];
				$command_line .= " ";
			} else if ($GraphTemplate["stacked"] == "1")
				$command_line .= " STACK:v".($cpt-1).$tm["ds_color_area"].$tm["ds_transparency"].":".html_entity_decode($legend);
			if ($GraphTemplate["stacked"] == "0"){
				$command_line .= " LINE".$tm["ds_tickness"].":v".($cpt-1);
				$command_line .= $tm["ds_color_line"].":".$legend;
			}
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
		
		if (isset($GraphTemplate["stacked"]) && $GraphTemplate["stacked"]){
			$space_added = NULL;
			$command_line .= " LINE1:total#000000:\"Total$space_added\" GPRINT:total:AVERAGE:\"Average\:%8.2lf%s\" GPRINT:total:MIN:\"Min\:%8.2lf%s\" GPRINT:total:MAX:\"Max\:%8.2lf%s\" GPRINT:total:LAST:\"Last\:%8.2lf%s\\l\" ";	
		}
		
		$command_line = $oreon->optGen["rrdtool_path_bin"] . $command_line;// .  " 2>&1";
		$command_line = escape_command("$command_line") ;		
		$fp = popen($command_line  , 'r');
		if (isset($fp) && $fp ){
			for ($str = '';!feof ($fp);) {
		  		$buffer = fgets($fp, 4096);
		 		$str = $str . $buffer ;
			}
			print $str;
		}	
	}
?>