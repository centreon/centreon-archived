<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

	$path = "/srv/oreon/";
	
	require_once ('DB.php');
	require_once ($path."www/class/Session.class.php");
	require_once ($path."www/class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];

	include($path."etc/centreon.conf.php");
	require_once $path."www/include/common/common-Func.php";

	/*
	 *  Connect to Oreon DB
	 */ 
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


	/*
	 * Verify if start and end date
	 */	

	if (!isset($_GET["start"]))
		$start = time() - (60*60*48);
	else
		$start = $_GET["start"];
	
	if (!isset($_GET["end"]))
		$end = time();
	else
		$end = $_GET["end"];

	$len = $end - $start;

	/*
	 * Verify if session is active
	 */	

	$session =& $pearDB->query("SELECT * FROM `session` WHERE session_id = '".$_GET["session_id"]."'");
	if (!$session->numRows()){

		$image = imagecreate(250,100);
		$fond = imagecolorallocate($image,0xEF,0xF2,0xFB);
		
		header("Content-Type: image/gif");
		imagegif($image);

		exit;
	} else {
		/*
		 * Get Values
		 */
		$session_value = $session->fetchRow();
		$session->free();

		/*
		 * Connect to ods
		 */
		 
		include_once($path."www/DBOdsConnect.php");

		$RRDdatabase_path = "/var/lib/ods/svc_status/";
	
		/*
		 * Get index information to have acces to graph
		 */
		
		if (!isset($_GET["host_name"]) && !isset($_GET["service_description"])){
			$DBRESULT =& $pearDBO->query("SELECT * FROM index_data WHERE `id` = '".$_GET["index"]."' LIMIT 1");
		} else {
			$DBRESULT =& $pearDBO->query("SELECT * FROM index_data WHERE host_name = '".$_GET["host_name"]."' AND `service_description` = '".$_GET["service_description"]."' LIMIT 1");
		}
		$index_data_ODS = $DBRESULT->fetchRow();
		if (!isset($_GET["template_id"])|| !$_GET["template_id"]){
			$host_id = getMyHostID($index_data_ODS["host_name"]);
			$svc_id = getMyServiceID($index_data_ODS["service_description"], $host_id);
			$template_id = getDefaultGraph($svc_id, 1);
			$index = $index_data_ODS["id"];
		} else
			$template_id = $_GET["template_id"];
		$DBRESULT->free();	

		/*
		 * Create command line
		 */
		 
		$command_line = " graph - --start=".$start." --end=".$end;

		/*
		 * get all template infos
		 */
		 
		$DBRESULT =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$template_id."' LIMIT 1");
		$DBRESULT->fetchInto($GraphTemplate);
		
		if (preg_match("/meta_([0-9]*)/", $index_data_ODS["service_description"], $matches)){
			$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
			if (PEAR::isError($DBRESULT_meta))
				print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
			$DBRESULT_meta->fetchInto($meta);
			$index_data_ODS["service_description"] = $meta["meta_name"];
		}
		
		$index_data_ODS["service_description"] = str_replace("#S#", "/", $index_data_ODS["service_description"]);
		$index_data_ODS["service_description"] = str_replace("#BS#", "\\", $index_data_ODS["service_description"]);
		
		$base = "";
		if (isset($GraphTemplate["base"]) && $GraphTemplate["base"])
			$base = "-b ".$GraphTemplate["base"];
		
		if (!isset($GraphTemplate["width"]) || $GraphTemplate["width"] == "")
			$GraphTemplate["width"] = 600;
		if (!isset($GraphTemplate["height"]) || $GraphTemplate["height"] == "")
			$GraphTemplate["height"] = 200;
		if (!isset($GraphTemplate["vertical_label"]) || $GraphTemplate["vertical_label"] == "")
			$GraphTemplate["vertical_label"] = "sds";		
		
		
		$command_line .= " --interlaced $base --imgformat PNG --width=500 --height=120 ";
		$command_line .= "--title='".$index_data_ODS["service_description"]." graph on ".$index_data_ODS["host_name"]."' --vertical-label='Status' ";

		//if ($oreon->optGen["rrdtool_version"] == "1.2")
		//	$command_line .= " --slope-mode ";
				
		/*
		 * Init Graph Template Value
		 */
		if (isset($GraphTemplate["bg_grid_color"]) && $GraphTemplate["bg_grid_color"])
			$command_line .= "--color CANVAS".$GraphTemplate["bg_grid_color"]." ";

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
			
		$command_line .= "--upper-limit 105 ";
		$command_line .= "--lower-limit 0 --rigid ";			
		$command_line .= " DEF:v1=".$RRDdatabase_path.$index.".rrd:status:AVERAGE:start=\"-8 days\":end=\"start + 8 days\" ";
		/*
		$command_line .= " DEF:pred=".$RRDdatabase_path.$index.".rrd:status:HWPREDICT ";
		$command_line .= " DEF:dev=".$RRDdatabase_path.$index.".rrd:status:DEVPREDICT ";
		$command_line .= " DEF:fail=".$RRDdatabase_path.$index.".rrd:status:FAILURES ";

		$command_line .= " TICK:fail#ffffa0:1.0:\"Failures Average bits out\"";
		*/
		$command_line .= " CDEF:vname=v1,3600,TREND ";
		$command_line .= "  CDEF:crit=v1,75,LT,100,0,IF ";
		$command_line .= "  CDEF:warn=v1,74,GT,100,0,IF ";
		$command_line .= "  CDEF:ok=v1,100,EQ,100,0,IF ";
		$command_line .= "  CDEF:unk=v1,UN,100,0,IF ";

		//$command_line .= " AREA:v1#AAFF33";
		//$command_line .= " VDEF:max=v1,AVERAGE ";
		
		$command_line .= " AREA:crit#F91E05 ";
		$command_line .= " AREA:warn#F8C706 ";
		$command_line .= " AREA:ok#19EE11 ";
		$command_line .= " AREA:unk#FFFFFF ";
		$command_line .= " COMMENT:\" \\l\" ";
		$command_line .= " LINE1:ok#19EE11:\"Ok\" ";
		$command_line .= " LINE1:warn#F8C706:\"Warning\" ";
		$command_line .= " LINE1:crit#F91E05:\"Critical\" ";
		$command_line .= " LINE1:unk#FFFFFF:\"Unknown\\l\" ";
		$command_line .= " LINE1:vname#000000:\"tendance\" ";
		//$command_line .= " HRULE:max#FF0000:\"tendance\" ";
		
		$command_line .= " GPRINT:v1:LAST:\"Last\:%7.2lf%s\"";
		$command_line .= " GPRINT:v1:LAST:\"Last\:%7.2lf%s\"";
		$command_line .= " GPRINT:v1:MAX:\"Max\:%7.2lf%s\"";
		$command_line .= " GPRINT:v1:AVERAGE:\"Average\:%7.2lf%s\\l\"";
		
		$command_line = $oreon->optGen["rrdtool_path_bin"].$command_line." 2>&1";
		$command_line = escape_command("$command_line");
		if ($oreon->optGen["debug_rrdtool"] == "1")
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