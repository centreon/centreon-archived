<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@oreon-project.org
 */


	function my_getTimeTamps($dateSTR)	{
		list($m,$d,$y) = split('/',$dateSTR);
		return (mktime(0,0,0,$m,$d,$y));
	}
	
	function my_getStartDay($date){
		$d = date("d",$date);
		$y = date("Y",$date);
		$m = date("m",$date);
		return (mktime(0,0,0,$m,$d,$y));
	}
	
	function my_getEndDay($date){
		$d = date("d",$date);
		$y = date("Y",$date);
		$m = date("m",$date);
		return (mktime(0,0,0,$m,$d+1,$y));
	}
	
	function my_getNextStartDay($date){
		$d = date("d",$date);
		$y = date("Y",$date);
		$m = date("m",$date);
		return (mktime(0,0,0,$m,$d+1,$y));
	}
	
	function my_getNextEndDay($date){
		$d = date("d",$date);
		$y = date("Y",$date);
		$m = date("m",$date);
		return (mktime(0,0,0,$m,$d+1,$y));
	}
	
	function trim_value(&$value){
	   $value = trim($value);
	}
	
	function getLogData($time_event, $host, $service, $status, $output, $type){
		$tab_img = array("UP" => './img/icones/12x12/recovery.gif',
						"DOWN" => './img/icones/12x12/alert.gif',
						"UNREACHABLE" => './img/icones/12x12/queue.gif',
						"NONE" => './img/icones/12x12/info.gif',
						);

		$tab["time"] = date(_("Y/m/d G:i"), $time_event);
		$tab["timeb"] = $time_event;
		$tab["host"] = $host;
		$tab["service"] = $service;
		$tab["status"] = $status;
		$tab["output"] = $output;
		$tab["type"] = $type;
		$tab["img"] = $tab_img[$status];
		return $tab ;
	}
?>