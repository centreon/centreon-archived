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

	if (!isset($oreon))
		exit(); 

	/*
	 * Filter Args
	 */
		 
	if (function_exists("filter_var")) {
		$page = filter_var($_GET["page"], FILTER_SANITIZE_SPECIAL_CHARS);
	} else if (function_exists("filter_get")) {
		$page = filter_get($_GET["page"]);
	} else {
		$page = $_GET["page"];
	}

	$tab_pages = split("/", $page);
	foreach ($tab_pages as $value)
		$page = $value;

	if (!file_exists("../doc/".$oreon->user->get_version()."/".$oreon->user->get_lang()."/"))
		$lang = "en_US";
	else 
		$lang = $oreon->user->get_lang();
		
	if (preg_match("/png/i", $page)) {
		print "<img src=\"./include/doc/getImage.php?lang=".$oreon->user->get_lang()."&version=".$oreon->user->get_version()."&img=images/".$page."\" />" ;
		exit ;
	}

	unset($tpl);
	unset($path);

	$path = "./include/doc/";
		
	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");	
	
	$flag_begin = 0;
	$flag_end = 0;
	print "<div style='padding=20px'>";

	$doc = fopen("../doc/".$oreon->user->get_version()."/".$lang."/".$page, "r");	
	while ($line = fgets($doc)){
		if ($flag_begin && !$flag_end){
			$line = preg_replace("/href\=\"/", "href=\"./main.php?p=$p&doc=1&page=", $line);
			$line = preg_replace("/page\=\#/", "page=".$_GET["page"]."#", $line);
			$line = preg_replace("/\<ul\>/", "<ul style=\"padding-left:40px;\">", $line);
			$line = preg_replace("/\<li\>/", "<li style=\"padding-left:30px;\">", $line);
			$line = preg_replace("/\<strong\>/", "<strong style=\"padding-left:20px;\">", $line);
			$line = preg_replace("/\<p\>/", "<p style=\"text-align:justify;padding-left:20px;padding-right:10px;padding-top:5px;padding-bottom:10px;\">", $line);
			$line = preg_replace("/\<img src\=\"images\//", "<img src=\"./include/doc/getImage.php?lang=".$oreon->user->get_lang()."&version=".$oreon->user->get_version()."&img=", $line);
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