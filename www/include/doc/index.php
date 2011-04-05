<?php
/*
 * Copyright 2005-2011 MERETHIS
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

	if (strstr($page, "http:"))
		header("Location: $page");

	$tab_pages = preg_split("/", $page);
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

	if ($doc = fopen("../doc/".$oreon->user->get_version()."/".$lang."/".$page, "r"))	
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