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
 * For information : contact@centreon.com
 */
 
class CentreonGMT{

	var $listGTM;
	var $myGMT;
	var $use;
	
	function CentreonGMT(){
		/*
		 * Define Table of GMT line
		 */
		$this->listGTM = array(null=>null);
		
		$this->listGTM['-12'] = -12;
		$this->listGTM['-11'] = -11;
		$this->listGTM['-10'] = -10;
		$this->listGTM['-9'] = -9;
		$this->listGTM['-8'] = -8;
		$this->listGTM['-7'] = -7;
		$this->listGTM['-6'] = -6;
		$this->listGTM['-5'] = -5;
		$this->listGTM['-4'] = -4;
		$this->listGTM['-3'] = -3;
		$this->listGTM['-2'] = -2;
		$this->listGTM['-1'] = -1;
		$this->listGTM['0'] = 0;
		$this->listGTM['1'] = 1;
		$this->listGTM['2'] = 2;
		$this->listGTM['3'] = 3;
		$this->listGTM['4'] = 4;
		$this->listGTM['5'] = 5;
		$this->listGTM['6'] = 6;
		$this->listGTM['7'] = 7;
		$this->listGTM['8'] = 8;
		$this->listGTM['9'] = 9;
		$this->listGTM['10'] = 10;
		$this->listGTM['11'] = 11;
		$this->listGTM['12'] = 12;
		
		/*
		 * Flag activ / inactiv
		 */
		$this->use = 1;
	}
	
	function used(){
		return $this->use;
	}
	
	function setMyGMT($value){
		if (!isset($value))
			$this->myGMT = $value;	
	}
	
	function getGMTList() {
		return $this->listGTM;
	}
	
	function getMyGMT(){
		return $this->myGMT;
	}

	function getMyGMTForRRD(){
		$gmt = (-1 * $this->myGMT);
		if ($gmt > 0)
			$gmt = "+$gmt";
		return $gmt;
	}
	
	function getDate($format, $date, $gmt = NULL) {
		/*
		 * Specify special GMT
		 */
		if (!isset($gmt))
			$gmt = $this->myGMT;
			
		if (isset($date) && isset($gmt)) {
			$date += $gmt * 60 * 60;
			return date($format, $date);
		} else {
			return "";	
		}
	}
	
	function getUTCDate($date, $gmt = NULL) {
		/*
		 * Specify special GMT
		 */
		if (!isset($gmt))
			$gmt = $this->myGMT;
			
		if (isset($date) && isset($gmt)) {
			$date += -1 * ($gmt * 60 * 60);
			return $date;
		} else {
			return "";	
		}
	}
	
	function getDelaySecondsForRRD($gmt) {
		$str = "";
		if ($gmt) {
			if ($gmt > 0)
				$str .= "+";
		} else {
			return "";	
		}
	}
	
	function getMyGMTFromSession($sid = NULL){
		global $pearDB;
		
		if (!isset($sid))
			return 0;
		
		$DBRESULT =& $pearDB->query("SELECT `contact_location` FROM `contact`, `session` " .
									"WHERE `session`.`user_id` = `contact`.`contact_id` " .
									"AND `session_id` = '$sid' LIMIT 1");
		if (PEAR::isError($DBRESULT)) {		
			$this->myGMT = 0;
		}
		$info =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		$this->myGMT = $info["contact_location"];
	}
	
}