<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	function testExistence ($email = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('rtde_id');
		$res =& $pearDB->query("SELECT email, rtde_id FROM reporting_diff_email WHERE email = '".htmlentities($email, ENT_QUOTES)."'");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		$email =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $email["rtde_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $email["rtde_id"] != $id)
			return false;
		else
			return true;
	}
	
	function enableMailInDB ($rtde_id = null)	{
		if (!$rtde_id) return;
		global $pearDB;
		$pearDB->query("UPDATE reporting_diff_email SET activate = '1' WHERE rtde_id = '".$rtde_id."'");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	}
	
	function disableMailInDB ($rtde_id = null)	{
		if (!$rtde_id) return;
		global $pearDB;
		$pearDB->query("UPDATE reporting_diff_email SET activate = '0' WHERE rtde_id = '".$rtde_id."'");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	}
	
	function deleteMailInDB ($contacts = array())	{
		global $pearDB;
		foreach($contacts as $key=>$value)	{
			$pearDB->query("DELETE FROM reporting_diff_email WHERE rtde_id = '".$key."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$pearDB->query("DELETE FROM reporting_email_list_relation WHERE rtde_id = '".$key."'");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
	
	function updateMailInDB ($rtde_id = NULL)	{
		if (!$rtde_id) return;
		updateMail($rtde_id);
		updateMailDiffList($rtde_id);
	}
	
	function mailOk($mail = NULL)	{
		if (!$mail) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		if (testExistence($mail))	{
			$rq = "INSERT INTO `reporting_diff_email` ( " .
					"`rtde_id` , `email`  , `format` , `comment` , `activate` )" .
					"VALUES ( ";
			$rq .= "NULL, '".$mail."', '".$ret["format"]["format"]."', ";
			isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES)."', ": $rq .= "NULL, ";
			isset($ret["activate"]["activate"]) && $ret["activate"]["activate"] != NULL ? $rq .= "'".$ret["activate"]["activate"]."' ": $rq .= "NULL ";
			$rq .= ")";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$res =& $pearDB->query("SELECT MAX(rtde_id) FROM reporting_diff_email");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$rtde_id = $res->fetchRow();
			updateMailDiffList($rtde_id["MAX(rtde_id)"]);
			return($rtde_id["MAX(rtde_id)"]);
		}
	}
	
	function insertMailInDB()	{
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		if (isset($ret["email"]) && $ret["email"])	{
			$str = NULL;
			for ($i = 0; $i < strlen($ret["email"]); $i++)	{
				switch ($ret["email"][$i])	{
					case " " : testExistence($str) ? mailOk($str) : NULL; $str = NULL; break;
					case "," : testExistence($str) ? mailOk($str) : NULL; $str = NULL; break;
					case ";" : testExistence($str) ? mailOk($str) : NULL; $str = NULL; break;
					case "\t" : testExistence($str) ? mailOk($str) : NULL; $str = NULL; break;
					case "\n" : testExistence($str) ? mailOk($str) : NULL; $str = NULL; break;
					default : $str .= $ret["email"][$i]; break;
				}
			}
			return(mailOk($str));			
		}
	}
	
	function updateMail($rtde_id = null)	{
		if (!$rtde_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE reporting_diff_email SET ";
		$rq .= "email = ";
		isset($ret["email"]) && $ret["email"] != NULL ? $rq .= "'".htmlentities($ret["email"], ENT_QUOTES)."', ": $rq .= "NULL, ";
        $rq .= "format = ";
        isset($ret["format"]["format"]) && $ret["format"]["format"] != NULL ? $rq .= "'".$ret["format"]["format"]."', ": $rq .= "NULL, ";
		$rq .= "comment = ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "activate = ";
		isset($ret["activate"]["activate"]) && $ret["activate"]["activate"] != NULL ? $rq .= "'".$ret["activate"]["activate"]."' ": $rq .= "NULL ";
		$rq .= "WHERE rtde_id = '".$rtde_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	}
	
	function updateMailDiffList($rtde_id = null)	{
		if (!$rtde_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM reporting_email_list_relation ";
		$rq .= "WHERE rtde_id = '".$rtde_id."'";
		$pearDB->query($rq);
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		$ret = array();
		$ret = $form->getSubmitValue("contact_lists");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO reporting_email_list_relation ";
			$rq .= "(rtdl_id, rtde_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$ret[$i]."', '".$rtde_id."')";
			$pearDB->query($rq);
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
		}
	}
?>