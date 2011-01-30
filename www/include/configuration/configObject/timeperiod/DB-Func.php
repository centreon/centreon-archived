<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	function includeExcludeTimeperiods($tpId, $includeTab = array(), $excludeTab = array()) {
		global $pearDB;

		/*
		 * Insert inclusions
		 */
		if (isset($includeTab) && is_array($includeTab)) {
		    $str = "";
		    foreach($includeTab as $tpIncludeId) {
		        if ($str != "") {
		            $str.= ", ";
		        }
		        $str .= "('".$tpId."', '".$tpIncludeId."')";
		    }
		    if (strlen($str)) {
		        $query = "INSERT INTO timeperiod_include_relations (timeperiod_id, timeperiod_include_id ) VALUES ".$str;
		        $pearDB->query($query);
		    }
		}

		/*
		 * Insert exclusions
		 */
		if (isset($excludeTab) && is_array($excludeTab)) {
		    $str = "";
		    foreach($excludeTab as $tpExcludeId) {
		        if ($str != "") {
		            $str.= ", ";
		        }
		        $str .= "('".$tpId."', '".$tpExcludeId."')";
		    }
		    if (strlen($str)) {
		        $query = "INSERT INTO timeperiod_exclude_relations (timeperiod_id, timeperiod_exclude_id ) VALUES ".$str;
		        $pearDB->query($query);
		    }
		}
	}

	function testTPExistence ($name = NULL)	{
		global $pearDB, $form, $oreon;

		$id = NULL;
		if (isset($form)) {
			$id = $form->getSubmitValue('tp_id');
		}

		$DBRESULT = $pearDB->query("SELECT tp_name, tp_id FROM timeperiod WHERE tp_name = '".htmlentities($oreon->checkIllegalChar($name), ENT_QUOTES, "UTF-8")."'");
		$tp = $DBRESULT->fetchRow();
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
			$DBRESULT2 = $pearDB->query("SELECT tp_name FROM `timeperiod` WHERE `tp_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			$DBRESULT = $pearDB->query("DELETE FROM timeperiod WHERE tp_id = '".$key."'");
			$oreon->CentreonLogAction->insertLog("timeperiod", $key, $row['tp_name'], "d");
		}
	}

	function multipleTimeperiodInDB ($timeperiods = array(), $nbrDup = array())	{
		global $oreon;

		foreach($timeperiods as $key=>$value)	{
			global $pearDB;

			$fields = array();
			$DBRESULT = $pearDB->query("SELECT * FROM timeperiod WHERE tp_id = '".$key."' LIMIT 1");

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
				foreach ($row as $key2 => $value2) {
					if ($key2 == "tp_name") {
					    $value2 .= "_" . $i;
					}
				    $key2 == "tp_name" ? ($tp_name = $value2) : $tp_name = null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
					if ($key2 != "tp_id") {
						$fields[$key2] = $value2;
					}
					if (isset($tp_name)) {
					    $fields["tp_name"] = $tp_name;
					}
				}
				if (isset($tp_name) && testTPExistence($tp_name)) {
					$DBRESULT = $pearDB->query($val ? $rq = "INSERT INTO timeperiod VALUES (".$val.")" : $rq = null);

					/*
		 			* Get Max ID
		 			*/
					$DBRESULT = $pearDB->query("SELECT MAX(tp_id) FROM `timeperiod`");
					$tp_id = $DBRESULT->fetchRow();

					$query = "INSERT INTO timeperiod_exceptions (timeperiod_id, days, timerange)
							SELECT ".$tp_id['MAX(tp_id)'].", days, timerange FROM timeperiod_exceptions WHERE timeperiod_id = '".$key."'";
					$pearDB->query($query);

					$query = "INSERT INTO timeperiod_include_relations (timeperiod_id, timeperiod_include_id)
							SELECT ".$tp_id['MAX(tp_id)'].", timeperiod_include_id FROM timeperiod_include_relations WHERE timeperiod_id = '".$key."'";
					$pearDB->query($query);

					$query = "INSERT INTO timeperiod_exclude_relations (timeperiod_id, timeperiod_exclude_id)
							SELECT ".$tp_id['MAX(tp_id)'].", timeperiod_exclude_id FROM timeperiod_exclude_relations WHERE timeperiod_id = '".$key."'";
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
		global $form, $pearDB, $oreon;

		if (!$tp_id) {
			return;
		}
		$ret = array();
		$ret = $form->getSubmitValues();

		$ret["tp_name"] = $oreon->checkIllegalChar($ret["tp_name"]);

		$rq = "UPDATE timeperiod ";
		$rq .= "SET tp_name = '".htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8")."', " .
				"tp_alias = '".htmlentities($ret["tp_alias"], ENT_QUOTES, "UTF-8")."', " .
				"tp_sunday = '".htmlentities($ret["tp_sunday"], ENT_QUOTES, "UTF-8")."', " .
				"tp_monday = '".htmlentities($ret["tp_monday"], ENT_QUOTES, "UTF-8")."', " .
				"tp_tuesday = '".htmlentities($ret["tp_tuesday"], ENT_QUOTES, "UTF-8")."', " .
				"tp_wednesday = '".htmlentities($ret["tp_wednesday"], ENT_QUOTES, "UTF-8")."', " .
				"tp_thursday = '".htmlentities($ret["tp_thursday"], ENT_QUOTES, "UTF-8")."', " .
				"tp_friday = '".htmlentities($ret["tp_friday"], ENT_QUOTES, "UTF-8")."', " .
				"tp_saturday = '".htmlentities($ret["tp_saturday"], ENT_QUOTES, "UTF-8")."' " .
				"WHERE tp_id = '".$tp_id."'";
		$DBRESULT = $pearDB->query($rq);

		$pearDB->query("DELETE FROM timeperiod_include_relations WHERE timeperiod_id = '".$tp_id."'");
		$pearDB->query("DELETE FROM timeperiod_exclude_relations WHERE timeperiod_id = '".$tp_id."'");

		if (!isset($ret['tp_include'])) {
			$ret['tp_include'] = array();
		}
		if (!isset($ret['tp_exclude'])) {
			$ret['tp_exclude'] = array();
		}

		includeExcludeTimeperiods($tp_id, $ret['tp_include'], $ret['tp_exclude']);

	    if (isset($_POST['nbOfExceptions'])) {
			$my_tab = $_POST;
	        $already_stored = array();
			$res = $pearDB->query("DELETE FROM `timeperiod_exceptions` WHERE `timeperiod_id`='".$tp_id."'");
	 		for ($i=0; $i <= $my_tab['nbOfExceptions']; $i++) {
	 			$exInput = "exceptionInput_" . $i;
	 			$exValue = "exceptionTimerange_" . $i;
	 			if (isset($my_tab[$exInput]) && !isset($already_stored[strtolower($my_tab[$exInput])]) && $my_tab[$exInput]) {
		 			$rq = "INSERT INTO timeperiod_exceptions (`timeperiod_id`, `days`, `timerange`) VALUES ('". $tp_id ."', '". htmlentities($my_tab[$exInput], ENT_QUOTES, "UTF-8") ."', '". htmlentities($my_tab[$exValue], ENT_QUOTES, "UTF-8") ."')";
			 		$DBRESULT = $pearDB->query($rq);
					$fields[$my_tab[$exInput]] = $my_tab[$exValue];
					$already_stored[strtolower($my_tab[$exInput])] = 1;
	 			}
	 		}
		}

		$fields["tp_name"] = htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8");
		$fields["tp_alias"] = htmlentities($ret["tp_alias"], ENT_QUOTES, "UTF-8");
		$fields["tp_sunday"] = htmlentities($ret["tp_sunday"], ENT_QUOTES, "UTF-8");
		$fields["tp_monday"] = htmlentities($ret["tp_monday"], ENT_QUOTES, "UTF-8");
		$fields["tp_tuesday"] = htmlentities($ret["tp_tuesday"], ENT_QUOTES, "UTF-8");
		$fields["tp_wednesday"] = htmlentities($ret["tp_wednesday"], ENT_QUOTES, "UTF-8");
		$fields["tp_thursday"] = htmlentities($ret["tp_thursday"], ENT_QUOTES, "UTF-8");
		$fields["tp_friday"] = htmlentities($ret["tp_friday"], ENT_QUOTES, "UTF-8");
		$fields["tp_saturday"] = htmlentities($ret["tp_saturday"], ENT_QUOTES, "UTF-8");
		$oreon->CentreonLogAction->insertLog("timeperiod", $tp_id, htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8"), "c", $fields);
	}

	function insertTimeperiodInDB ($ret = array())	{
		$tp_id = insertTimeperiod($ret);
		return ($tp_id);
	}

	function insertTimeperiod($ret = array(), $exceptions = null)	{
		global $form, $pearDB, $oreon;

		if (!count($ret)) {
			$ret = $form->getSubmitValues();
		}

		$ret["tp_name"] = $oreon->checkIllegalChar($ret["tp_name"]);

		$rq = "INSERT INTO timeperiod ";
		$rq .= "(tp_name, tp_alias, tp_sunday, tp_monday, tp_tuesday, tp_wednesday, tp_thursday, tp_friday, tp_saturday) ";
		$rq .= "VALUES (";
		isset($ret["tp_name"]) && $ret["tp_name"] != NULL ? $rq .= "'".htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["tp_alias"]) && $ret["tp_alias"] != NULL ? $rq .= "'".htmlentities($ret["tp_alias"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["tp_sunday"]) && $ret["tp_sunday"] != NULL ? $rq .= "'".htmlentities($ret["tp_sunday"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["tp_monday"]) && $ret["tp_monday"] != NULL ? $rq .= "'".htmlentities($ret["tp_monday"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["tp_tuesday"]) && $ret["tp_tuesday"] != NULL ? $rq .= "'".htmlentities($ret["tp_tuesday"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["tp_wednesday"]) && $ret["tp_wednesday"] != NULL ? $rq .= "'".htmlentities($ret["tp_wednesday"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["tp_thursday"]) && $ret["tp_thursday"] != NULL ? $rq .= "'".htmlentities($ret["tp_thursday"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["tp_friday"]) && $ret["tp_friday"] != NULL ? $rq .= "'".htmlentities($ret["tp_friday"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["tp_saturday"]) && $ret["tp_saturday"] != NULL ? $rq .= "'".htmlentities($ret["tp_saturday"], ENT_QUOTES, "UTF-8")."'": $rq .= "NULL";
		$rq .= ")";
		$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(tp_id) FROM timeperiod");
		$tp_id = $DBRESULT->fetchRow();

		if (!isset($ret['tp_include'])) {
			$ret['tp_include'] = array();
		}
		if (!isset($ret['tp_exclude'])) {
			$ret['tp_exclude'] = array();
		}

		includeExcludeTimeperiods($tp_id['MAX(tp_id)'], $ret['tp_include'], $ret['tp_exclude']);

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
		 			$rq = "INSERT INTO timeperiod_exceptions (`timeperiod_id`, `days`, `timerange`) VALUES ('". $tp_id['MAX(tp_id)'] ."', '". htmlentities($my_tab[$exInput], ENT_QUOTES, "UTF-8") ."', '". htmlentities($my_tab[$exValue], ENT_QUOTES, "UTF-8") ."')";
			 		$DBRESULT = $pearDB->query($rq);
					$fields[$my_tab[$exInput]] = $my_tab[$exValue];
					$already_stored[strtolower($my_tab[$exInput])] = 1;
	 			}
	 		}
		}

		$fields["tp_name"] = htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8");
		$fields["tp_alias"] = htmlentities($ret["tp_alias"], ENT_QUOTES, "UTF-8");
		$fields["tp_sunday"] = htmlentities($ret["tp_sunday"], ENT_QUOTES, "UTF-8");
		$fields["tp_monday"] = htmlentities($ret["tp_monday"], ENT_QUOTES, "UTF-8");
		$fields["tp_tuesday"] = htmlentities($ret["tp_tuesday"], ENT_QUOTES, "UTF-8");
		$fields["tp_wednesday"] = htmlentities($ret["tp_wednesday"], ENT_QUOTES, "UTF-8");
		$fields["tp_thursday"] = htmlentities($ret["tp_thursday"], ENT_QUOTES, "UTF-8");
		$fields["tp_friday"] = htmlentities($ret["tp_friday"], ENT_QUOTES, "UTF-8");
		$fields["tp_saturday"] = htmlentities($ret["tp_saturday"], ENT_QUOTES, "UTF-8");
		$oreon->CentreonLogAction->insertLog("timeperiod", $tp_id["MAX(tp_id)"], htmlentities($ret["tp_name"], ENT_QUOTES, "UTF-8"), "a", $fields);

		return ($tp_id["MAX(tp_id)"]);
	}

	function checkHours($hourString) {
		if ($hourString == "") {
			return true;
		} else {
			if (strstr($hourString, ",")) {
				$tab1 = split(",", $hourString);
				for ($i = 0 ; isset($tab1[$i]) ; $i++) {
					if (preg_match("/([0-9]*):([0-9]*)-([0-9]*):([0-9]*)/", $tab1[$i], $str)) {
						if ($str[1] > 24 || $str[3] > 24)
							return false;
						if ($str[2] > 59 || $str[4] > 59)
							return false;
						if (($str[3] * 60 * 60 + $str[4] * 60) > 86400 || ($str[1] * 60 * 60 + $str[2] * 60) > 86400)
							return false;
					} else {
						return false;
					}
				}
				return true;
			} else {
				if (preg_match("/([0-9]*):([0-9]*)-([0-9]*):([0-9]*)/", $hourString, $str)) {
					if ($str[1] > 24 || $str[3] > 24)
						return false;
					if ($str[2] > 59 || $str[4] > 59)
						return false;
					if (($str[3] * 60 * 60 + $str[4] * 60) > 86400 || ($str[1] * 60 * 60 + $str[2] * 60) > 86400)
						return false;
					return true;
				} else {
					return false;
				}
			}
		}
	}
?>