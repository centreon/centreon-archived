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
	
	require_once ('DB.php');

	/*
	 *  Connect to Oreon DB
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

	 */ 

	/*
	 * Verify if start and end date
	 */	

	if (!isset($_GET["start"])){
		$start = time() - (60*60*48);
	}
	else
		$start = $_GET["start"];
	
	if (!isset($_GET["end"])){
		$end = time();
	}
	else
		$end = $_GET["end"];



	/*
	 * Verify if session is active
	 */	
	
	
				/*
		 * Create command line
		 */
		 
		$command_line = " graph - --start=".$start." --end=".$end;

		/*
		 * get all template infos
		 */
		 
		$command_line .= " --interlaced --imgformat PNG --width=500 --height=120 --title='Latency' --vertical-label='Latency' --slope-mode  ";
		$command_line .= "--rigid --alt-autoscale-max ";
				
		/*
		 * Init DS template For each curv
		 */
		
		$colors = array("1"=>"#19EE11", "2"=>"#82CFD8", "3"=>"#F8C706");
		$metrics = array("Used" => 1, "High" => 2, "Total" => 3);
		
		$cpt = 1;
		foreach ($metrics as $key => $value){
			$command_line .= " DEF:v".$cpt."=/var/lib/ods/perfmon-".$_GET["ns_id"]."/nagios_active_service_latency.rrd:".$key.":AVERAGE ";
			$cpt++;
		}
		$command_line .= " COMMENT:\" \\l\" ";
		
		
		# Create Legende
		$cpt = 1;

		foreach ($metrics as $key => $tm){
			
			$command_line .= " AREA:v".($cpt).$colors[$cpt]."3c ";
			$command_line .= " LINE1:v".($cpt);
			$command_line .= $colors[$cpt].":\"";
			$command_line .= $key."\"";
			$cpt++;
		}

		$command_line = "/usr/bin/rrdtool ".$command_line." 2>&1";
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
	
?>