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
			$DBRESULT =& $pearDB->query("SELECT * FROM timeperiod WHERE tp_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["tp_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "tp_name" ? ($tp_name = $value2 = $value2."_".$i) : null;
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
	
	function insertTimeperiod($ret = array())	{
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