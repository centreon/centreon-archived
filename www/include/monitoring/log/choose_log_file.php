<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick
Facon, Romain Le Merlus, Julien Mathis

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the
quality,
safety, contents, performance, merchantability, non-infringement or
suitability for
any particular or intended purpose of the Software found on the OREON web
site.
In no event will OREON be liable for any direct, indirect, punitive,
special,
incidental or consequential damages however they may arise and even if OREON
has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	$lcaHostByName = getLcaHostByName($pearDB);
	
	$tableFile2 = array();
	if ($handle  = @opendir($oreon->Nagioscfg["log_archive_path"]))	{
		while ($file = @readdir($handle))
			if (is_file($oreon->Nagioscfg["log_archive_path"]."/$file"))	{
				preg_match("/nagios\-([0-9]*)\-([0-9]*)\-([0-9]*)\-([0-9]*).log/", $file, $matches);
				$time = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3]) - 1;
				$tableFile2[$file] =  "  " . date($lang["date_format"], $time) . " ";
			}
		@closedir($handle);
	}
	krsort($tableFile2);
	
	$tableFile3 = array($oreon->Nagioscfg["log_file"] => " -- " . $lang["m_mon_today"] . " -- ");
	$tableFile1 = array_merge($tableFile3, $tableFile2);

	$host = array();
	
	$host[""] = "";
	$DBRESULT =& $pearDB->query("SELECT host_name FROM host where host_activate = '1' and host_register = '1' ORDER BY host_name");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	while ($DBRESULT->fetchInto($h))
		if (IsHostReadable($lcaHostByName, $h['host_name']))
			$host[$h["host_name"]] = $h["host_name"];

	$debug = 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	
	#
	## Form begin
	#
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', $lang["dtm_addS"]);
	
	#
	## Indicator basic information
	#
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
    
    $selHost =& $form->addElement('select', 'file', $lang["nag_logFile"], $tableFile1, array("onChange" =>"this.form.submit();"));
	$selHost =& $form->addElement('select', 'host', $lang["h"], $host, array("onChange" =>"this.form.submit();"));
	isset($_POST["host"]) ?	$form->setDefaults(array('file' => $_POST["host"])) : $form->setDefaults(array('file' => $oreon->Nagioscfg["log_file"]));
	
	$log = NULL;	
	$tab_log = array();	
	
?>