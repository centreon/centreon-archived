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
 

	if (!isset ($oreon))
		exit ();

	function testTPExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('tp_id');
		$DBRESULT =& $pearDB->query("SELECT tp_name, tp_id FROM timeperiod WHERE tp_name = '".htmlentities($name, ENT_QUOTES)."'");
		$tp =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $tp["tp_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $tp["tp_id"] != $id)	
			return false;
		else
			return true;
	}

	function deleteTimeperiodInDB ($timeperiods = array())	{
		global $pearDB, $oreon;
		foreach($timeperiods as $key=>$value)	{
			$DBRESULT2 =& $pearDB->query("SELECT tp_name FROM `timeperiod` WHERE `tp_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			$DBRESULT =& $pearDB->query("DELETE FROM timeperiod WHERE tp_id = '".$key."'");
			$oreon->CentreonLogAction->insertLog("timeperiod", $key, $row['tp_name'], "d");
		}
	}
	
	function multipleTimeperiodInDB ($timeperiods = array(), $nbrDup = array())	{
		global $oreon;
		
		foreach($timeperiods as $key=>$value)	{
			global $pearDB;
			
			$fields = array();
			$DBRESULT =& $pearDB->query("SELECT * FROM timeperiod WHERE tp_id = '".$key."' LIMIT 1");
			
			$query = "SELECT days, timerange FROM timeperiod_exceptions WHERE timeperiod_id = '".$key."'";
			$res = $pearDB->query($query);
			while ($row = $res->fetchRow()) {
			    foreach ($row as $keyz => $valz) {
			        $fields[$keyz] = $valz;
			    }
			}
			
			$row = $DBRESULT->fetchRow();
			$row["tp_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2) {
					$value2 .= "_" . $i;
				    $key2 == "tp_name" ? ($tp_name = $value2) : $tp_name = null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
					if ($key2 != "tp_id")
						$fields[$key2] = $value2;
					$fields["tp_name"] = $tp_name;
				}
				if (testTPExistence($tp_name))	{	
					$DBRESULT =& $pearDB->query($val ? $rq = "INSERT INTO timeperiod VALUES (".$val.")" : $rq = null);
					
					/*
		 			* Get Max ID
		 			*/
					$DBRESULT =& $pearDB->query("SELECT MAX(tp_id) FROM `timeperiod`");
					$tp_id = $DBRESULT->fetchRow();	
				
					$query = "INSERT INTO timeperiod_exceptions (timeperiod_id, days, timerange) 
							SELECT ".$tp_id['MAX(tp_id)'].", days, timerange FROM timeperiod_exceptions WHERE timeperiod_id = '".$key."'";					
					$pearDB->query($query);
					$oreon->CentreonLogAction->insertLog("timeperiod", $tp_id["MAX(tp_id)"], $tp_name, "a", $fields);
				}
			}
		}
	}
	
	function updateTimeperiodInDB ($tp_id = NULL)	{
		if (!$tp_id) return;
		updateTimeperiod($tp_id);
	}
	
	function updateTimeperiod($tp_id)	{
		if (!$tp_id) return;
		global $form;
		global $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE timeperiod ";
		$rq .= "SET tp_name = '".htmlentities($ret["tp_name"], ENT_QUOTES)."', " .
				"tp_alias = '".htmlentities($ret["tp_alias"], ENT_QUOTES)."', " .
				"tp_sunday = '".htmlentities($ret["tp_sunday"], ENT_QUOTES)."', " .
				"tp_monday = '".htmlentities($ret["tp_monday"], ENT_QUOTES)."', " .
				"tp_tuesday = '".htmlentities($ret["tp_tuesday"], ENT_QUOTES)."', " .
				"tp_wednesday = '".htmlentities($ret["tp_wednesday"], ENT_QUOTES)."', " .
				"tp_thursday = '".htmlentities($ret["tp_thursday"], ENT_QUOTES)."', " .
				"tp_friday = '".htmlentities($ret["tp_friday"], ENT_QUOTES)."', " .
				"tp_saturday = '".htmlentities($ret["tp_saturday"], ENT_QUOTES)."' " .
				"WHERE tp_id = '".$tp_id."'";
		$DBRESULT =& $pearDB->query($rq);
		
	    if (isset($_POST['nbOfExceptions'])) {			
			$my_tab = $_POST;
	        $already_stored = array();
			$res = $pearDB->query("DELETE FROM `timeperiod_exceptions` WHERE `timeperiod_id`='".$tp_id."'");
	 		for ($i=0; $i <= $my_tab['nbOfExceptions']; $i++) { 			
	 			$exInput = "exceptionInput_" . $i;
	 			$exValue = "exceptionTimerange_" . $i;
	 			if (isset($my_tab[$exInput]) && !isset($already_stored[strtolower($my_tab[$exInput])]) && $my_tab[$exInput]) {		 			
		 			$rq = "INSERT INTO timeperiod_exceptions (`timeperiod_id`, `days`, `timerange`) VALUES ('". $tp_id['MAX(tp_id)'] ."', '". htmlentities($my_tab[$exInput], ENT_QUOTES) ."', '". htmlentities($my_tab[$exValue], ENT_QUOTES) ."')";
			 		$DBRESULT =& $pearDB->query($rq);
					$fields[$my_tab[$exInput]] = $my_tab[$exValue];	
					$already_stored[strtolower($my_tab[$exInput])] = 1;
	 			}			
	 		}
		}	
		
		$fields["tp_name"] = htmlentities($ret["tp_name"], ENT_QUOTES);
		$fields["tp_alias"] = htmlentities($ret["tp_alias"], ENT_QUOTES);
		$fields["tp_sunday"] = htmlentities($ret["tp_sunday"], ENT_QUOTES);
		$fields["tp_monday"] = htmlentities($ret["tp_monday"], ENT_QUOTES);
		$fields["tp_tuesday"] = htmlentities($ret["tp_tuesday"], ENT_QUOTES);
		$fields["tp_wednesday"] = htmlentities($ret["tp_wednesday"], ENT_QUOTES);
		$fields["tp_thursday"] = htmlentities($ret["tp_thursday"], ENT_QUOTES);
		$fields["tp_friday"] = htmlentities($ret["tp_friday"], ENT_QUOTES);
		$fields["tp_saturday"] = htmlentities($ret["tp_saturday"], ENT_QUOTES);
		$oreon->CentreonLogAction->insertLog("timeperiod", $tp_id, htmlentities($ret["tp_name"], ENT_QUOTES), "c", $fields);
	}
	
	function insertTimeperiodInDB ($ret = array())	{
		$tp_id = insertTimeperiod($ret);
		return ($tp_id);
	}
	
	function insertTimeperiod($ret = array(), $exceptions = null)	{
		global $form;
		global $pearDB, $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO timeperiod ";
		$rq .= "(tp_name, tp_alias, tp_sunday, tp_monday, tp_tuesday, tp_wednesday, tp_thursday, tp_friday, tp_saturday) ";
		$rq .= "VALUES (";
		isset($ret["tp_name"]) && $ret["tp_name"] != NULL ? $rq .= "'".htmlentities($ret["tp_name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["tp_alias"]) && $ret["tp_alias"] != NULL ? $rq .= "'".htmlentities($ret["tp_alias"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["tp_sunday"]) && $ret["tp_sunday"] != NULL ? $rq .= "'".htmlentities($ret["tp_sunday"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["tp_monday"]) && $ret["tp_monday"] != NULL ? $rq .= "'".htmlentities($ret["tp_monday"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["tp_tuesday"]) && $ret["tp_tuesday"] != NULL ? $rq .= "'".htmlentities($ret["tp_tuesday"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["tp_wednesday"]) && $ret["tp_wednesday"] != NULL ? $rq .= "'".htmlentities($ret["tp_wednesday"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["tp_thursday"]) && $ret["tp_thursday"] != NULL ? $rq .= "'".htmlentities($ret["tp_thursday"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["tp_friday"]) && $ret["tp_friday"] != NULL ? $rq .= "'".htmlentities($ret["tp_friday"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["tp_saturday"]) && $ret["tp_saturday"] != NULL ? $rq .= "'".htmlentities($ret["tp_saturday"], ENT_QUOTES)."'": $rq .= "NULL";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(tp_id) FROM timeperiod");
		$tp_id = $DBRESULT->fetchRow();
				
		/*
		 *  Insert exceptions
		 */
		if (isset($exceptions))
			$my_tab = $exceptions;
		else if (isset($_POST['nbOfExceptions']))
			$my_tab = $_POST;
		if (isset($my_tab['nbOfExceptions'])) {			
			$already_stored = array(); 		
	 		for ($i=0; $i <= $my_tab['nbOfExceptions']; $i++) { 			
	 			$exInput = "exceptionInput_" . $i;
	 			$exValue = "exceptionTimerange_" . $i;
	 			if (isset($my_tab[$exInput]) && !isset($already_stored[strtolower($my_tab[$exInput])]) && $my_tab[$exInput]) {		 			
		 			$rq = "INSERT INTO timeperiod_exceptions (`timeperiod_id`, `days`, `timerange`) VALUES ('". $tp_id['MAX(tp_id)'] ."', '". htmlentities($my_tab[$exInput], ENT_QUOTES) ."', '". htmlentities($my_tab[$exValue], ENT_QUOTES) ."')";
			 		$DBRESULT =& $pearDB->query($rq);
					$fields[$my_tab[$exInput]] = $my_tab[$exValue];	
					$already_stored[strtolower($my_tab[$exInput])] = 1;
	 			}			
	 		}
		}
		
		$fields["tp_name"] = htmlentities($ret["tp_name"], ENT_QUOTES);
		$fields["tp_alias"] = htmlentities($ret["tp_alias"], ENT_QUOTES);
		$fields["tp_sunday"] = htmlentities($ret["tp_sunday"], ENT_QUOTES);
		$fields["tp_monday"] = htmlentities($ret["tp_monday"], ENT_QUOTES);
		$fields["tp_tuesday"] = htmlentities($ret["tp_tuesday"], ENT_QUOTES);
		$fields["tp_wednesday"] = htmlentities($ret["tp_wednesday"], ENT_QUOTES);
		$fields["tp_thursday"] = htmlentities($ret["tp_thursday"], ENT_QUOTES);
		$fields["tp_friday"] = htmlentities($ret["tp_friday"], ENT_QUOTES);
		$fields["tp_saturday"] = htmlentities($ret["tp_saturday"], ENT_QUOTES);
		$oreon->CentreonLogAction->insertLog("timeperiod", $tp_id["MAX(tp_id)"], htmlentities($ret["tp_name"], ENT_QUOTES), "a", $fields);
		
		return ($tp_id["MAX(tp_id)"]);
	}
?>