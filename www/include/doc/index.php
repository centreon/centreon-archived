<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS. OREON makes no representation
and gives no warranty whatsoever, whether express or implied, and without limitation, 
with regard to the quality, safety, contents, performance, merchantability, non-infringement
or suitability for any particular or intended purpose of the Software found on the OREON web
site. In no event will OREON be liable for any direct, indirect, punitive, special, incidental
or consequential damages however they may arise and even if OREON has been previously advised 
of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit(); 
		
	unset($tpl);
	unset($path);

	$path = "./include/doc/";
		
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");	
	
	$flag_begin = 0;
	$flag_end = 0;
	print "<div style='padding=20px'>";
	if (!file_exists("../doc/".$oreon->user->get_lang()."/"))
		$lang = "en_US";
	else 
		$lang = $oreon->user->get_lang();

	$doc = fopen("../doc/".$lang."/".$_GET["page"], "r");	
	while ($line = fgets($doc)){
		if ($flag_begin && !$flag_end){
			$line = preg_replace("/href\=\"/", "href=\"./oreon.php?p=$p&doc=1&page=", $line);
			$line = preg_replace("/page\=\#/", "page=".$_GET["page"]."#", $line);
			$line = preg_replace("/\<ul\>/", "<ul style=\"padding-left:40px;\">", $line);
			$line = preg_replace("/\<li\>/", "<li style=\"padding-left:30px;\">", $line);
			$line = preg_replace("/\<strong\>/", "<strong style=\"padding-left:20px;\">", $line);
			$line = preg_replace("/\<p\>/", "<p style=\"text-align:justify;padding-left:20px;padding-right:10px;padding-top:5px;padding-bottom:10px;\">", $line);
			$line = preg_replace("/\<img src\=\"images\//", "<img src=\"./include/doc/get_image.php?lang=".$oreon->user->get_lang()."&img=", $line);
			$line = preg_replace("/\<table border\=\"0\"/", "<table border=\"1\"", $line);
			print $line;
		}
		if (preg_match("/\<body[.]*/", $line))
			$flag_begin = 1;
		if (preg_match("/\<\/body[.]*/", $line))
			$flag_end = 1;
	}
	print "</div>";
?>

