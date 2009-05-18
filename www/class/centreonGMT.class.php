<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 
class CentreonGMT{

	var $listGTM;
	var $myGMT;
	var $use;
	
	function CentreonGMT(){
		/*
		 * Define Table of GMT line
		 */
		$this->listGTM = array(null => null);
		
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
		$this->use = $this->checkGMTStatus();
	}
	
	function checkGMTStatus() {
		global $pearDB;
		
		$DBRESULT =& $pearDB->query("SELECT * FROM options WHERE `key` = 'enable_gmt'");
		$result =& $DBRESULT->fetchRow();
		return ($result["value"]);
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
		
		if ($this->use) {
			if (isset($date) && isset($gmt)) {
				$date += $gmt * 60 * 60;
				return date($format, $date);
			} else {
				return "";	
			}
		} else {
			return date($format, $date);
		}
	}
	
	function getUTCDate($date, $gmt = NULL) {
		/*
		 * Specify special GMT
		 */
		if (!isset($gmt))
			$gmt = $this->myGMT;
		
		if ($this->use) {
			if (isset($date) && isset($gmt)) {
				$date += -1 * ($gmt * 60 * 60);
				return $date;
			} else {
				return "";	
			}			
		} else {
			return $date;
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