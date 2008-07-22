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
 

	if (function_exists("filter_var")) {
		$img = filter_var($_GET["img"], FILTER_SANITIZE_SPECIAL_CHARS);
		$img = filter_var($img, INPUT_GET);

		$lang = filter_var($_GET["lang"], FILTER_SANITIZE_SPECIAL_CHARS);
		$lang = filter_var($lang, INPUT_GET);

		$version = filter_var($_GET["version"], FILTER_SANITIZE_SPECIAL_CHARS);
		$version = filter_var($version, INPUT_GET);
	}
	else {
		$img = filter_var($_GET["img"]);
		$lang = filter_var($_GET["lang"]);
		$version = filter_var($_GET["version"]);
	}

	$tab_images = split("/", $img);
	foreach ($tab_images as $value)
		$image = $value;
	
	header("Content-Type: image/png");
	
	if (file_exists("../../../doc/".$version."/".$lang."/images/".$image)){
		$img = fopen("../../../doc/".$version."/".$lang."/images/".$image, "r");	
		if (isset($img) && $img)
			while ($line = fgets($img))
				print $line;
	}
?>