<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	require_once ("@CENTREON_ETC@/centreon.conf.php");		
	require_once ("../../$classdir/centreonSession.class.php");
	require_once ("../../$classdir/centreon.class.php");
	require_once ("../../$classdir/centreonDB.class.php");
	
	$pearDB = new CentreonDB();
	CentreonSession::start();
	$centreon= $_SESSION["centreon"];
	
	$session = $pearDB->query("SELECT * FROM `session` WHERE `session_id` = '".session_id()."'");
	if (!$session->numRows())
		exit;
	
	$logos_path = "../../img/media/";

	if (isset($_GET["id"]) && $_GET["id"] && is_numeric($_GET["id"])) {
	    $result = $pearDB->query("SELECT dir_name, img_path FROM view_img_dir, view_img, view_img_dir_relation vidr WHERE view_img_dir.dir_id = vidr.dir_dir_parent_id AND vidr.img_img_id = img_id AND img_id = '".$pearDB->escape($_GET["id"])."'");
	    while ($img = $result->fetchRow() ) {
			$imgpath = $logos_path . $img["dir_name"] ."/". $img["img_path"];
	        if (!is_file($imgpath)) {
		        $imgpath = $centreon_path . 'www/img/media/' . $img["dir_name"] ."/". $img["img_path"];
		    }
			if (is_file($imgpath)) {
			    $fd = fopen($imgpath, "r");
			    $buffer = NULL;
			    while (!feof($fd)) {
				    $buffer .= fgets($fd, 4096);
			    }
			    fclose ($fd);
			    print $buffer;
			    break;
			}
			else {
				print "File not found";
			}
	    }	
	}
?>