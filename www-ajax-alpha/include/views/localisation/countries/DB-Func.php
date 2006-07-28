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
			$id = $form->getSubmitValue('country_id');
		$res =& $pearDB->query("SELECT country_name, country_id FROM view_country WHERE country_name = '".htmlentities($name, ENT_QUOTES)."'");
		$country =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $country["country_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $country["country_id"] != $id)	
			return false;
		else
			return true;
	}

	function deleteCountryInDB ($countries = array())	{
		global $pearDB;
		foreach($countries as $key=>$value)
			$pearDB->query("DELETE FROM view_country WHERE country_id = '".$key."'");
	}
	
	function multipleCountryInDB ($countries = array(), $nbrDup = array())	{
		foreach($countries as $key=>$value)	{
			global $pearDB;
			$res =& $pearDB->query("SELECT * FROM view_country WHERE country_id = '".$key."' LIMIT 1");
			$row = $res->fetchRow();
			$row["country_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "country_name" ? ($country_name = clone($value2 = $value2."_".$i)) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($country_name))
					$pearDB->query($val ? $rq = "INSERT INTO view_country VALUES (".$val.")" : $rq = null);
			}
		}
	}
	
	function updateCountryInDB ($country_id = NULL)	{
		if (!$country_id) return;
		updateCountry($country_id);
	}
	
	function updateCountry($country_id)	{
		if (!$country_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE view_country ";
		$rq .= "SET country_name = '".htmlentities($ret["country_name"], ENT_QUOTES)."', " .
				"country_alias = '".htmlentities($ret["country_alias"], ENT_QUOTES)."' " .
				"WHERE country_id = '".$country_id."'";
		$pearDB->query($rq);
	}
	
	function insertCountryInDB ()	{
		$country_id = insertCountry();
		return ($country_id);
	}
	
	function insertCountry()	{
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "INSERT INTO view_country ";
		$rq .= "(country_name, country_alias) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["country_name"], ENT_QUOTES)."', '".htmlentities($ret["country_alias"], ENT_QUOTES)."')";
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(country_id) FROM view_country");
		$country_id = $res->fetchRow();
		return ($country_id["MAX(country_id)"]);
	}
?>