<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Status Map » is developped by Merethis company for Lafarge Group, 
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

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

	function testExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('map_id');
		$res =& $pearDB->query("SELECT map_name, map_id FROM view_map WHERE map_name = '".htmlentities($name, ENT_QUOTES)."'");
		$map =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $map["map_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $map["map_id"] != $id)	
			return false;
		else
			return true;
	}

	function deleteMapInDB ($maps = array())	{
		global $pearDB;
		foreach($maps as $key=>$value)	{
			$res =& $pearDB->query("SELECT map_path FROM view_map WHERE map_id = '".$key."' LIMIT 1");
			$row = $res->fetchRow();
			if (is_file($row["map_path"]))
				unlink($row["map_path"]);
			$pearDB->query("DELETE FROM view_map WHERE map_id = '".$key."'");
		}
	}
	
	function updateMapInDB ($map_id = NULL, $file = NULL)	{
		if (!$map_id) return;
		updateMap($map_id, $file);
	}
	
	function updateMap($map_id, $file = NULL)	{
		if (!$map_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret["map_path"] = NULL;
		$ret = $form->getSubmitValues();
		if ($file->isUploadedFile())	{
			$res =& $pearDB->query("SELECT map_path FROM view_map WHERE map_id = '".$map_id."' LIMIT 1");
			$row = $res->fetchRow();
			if (is_file($row["map_path"]))
				unlink($row["map_path"]);
			$file->moveUploadedFile("./ext/osm/maps/");
			$fDataz =& $file->getValue();
			$ret["map_path"] = "./ext/osm/maps/".$fDataz["name"];
		}
		$rq = "UPDATE view_map ";
		$rq .= "SET map_name = '".htmlentities($ret["map_name"], ENT_QUOTES)."', ";
		$rq	.= "map_description = '".htmlentities($ret["map_description"], ENT_QUOTES)."', ";
		$ret["map_path"] ? $rq	.= "map_path = '".$ret["map_path"]."', " : NULL;
		$rq	.= "map_comment = '".htmlentities($ret["map_comment"], ENT_QUOTES)."' ";
		$rq	.= "WHERE map_id = '".$map_id."'";
		$pearDB->query($rq);
	}
	
	function insertMapInDB ($file = NULL)	{
		$map_id = insertMap($file);
		return ($map_id);
	}
	
	function insertMap($file = NULL)	{
		global $form;
		global $pearDB;
		$ret = array();
		$ret["map_path"] = NULL;
		$ret = $form->getSubmitValues();
		if ($file)	{
			$file->moveUploadedFile("./ext/osm/maps/");
			$fDataz =& $file->getValue();
			$ret["map_path"] = "./ext/osm/maps/".$fDataz["name"];
		}
		$rq = "INSERT INTO view_map ";
		$rq .= "(map_name, map_description, map_path, map_comment) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["map_name"], ENT_QUOTES)."', '".htmlentities($ret["map_description"], ENT_QUOTES)."', '".$ret["map_path"]."', '".htmlentities($ret["map_comment"], ENT_QUOTES)."')";
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(map_id) FROM view_map");
		$map_id = $res->fetchRow();
		return ($map_id["MAX(map_id)"]);
	}
?>