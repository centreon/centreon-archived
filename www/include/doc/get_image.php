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
	
	$img = filter_var($_GET["img"], FILTER_SANITIZE_SPECIAL_CHARS);
	$img = filter_var($img, INPUT_GET);

	$lang = filter_var($_GET["lang"], FILTER_SANITIZE_SPECIAL_CHARS);
	$lang = filter_var($lang, INPUT_GET);


	$tab_images = split("/", $img);
	foreach ($tab_images as $value)
		$image = $value;
	
	header("Content-Type: image/png");
	
	if (file_exists("../../../doc/".$lang."/images/".$image)){
		$img = fopen("../../../doc/".$lang."/images/".$image, "r");	
		if (isset($img) && $img)
			while ($line = fgets($img))
				print $line;
	}
?>