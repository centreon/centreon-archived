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
 
	if (!isset ($oreon))
		exit ();
	
	isset($_GET["cg_id"]) ? $cG = $_GET["cg_id"] : $cG = NULL;
	isset($_POST["cg_id"]) ? $cP = $_POST["cg_id"] : $cP = NULL;
	$cG ? $cg_id = $cG : $cg_id = $cP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	/*
	 * Pear library
	 */
	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/configuration/configObject/contactgroup/";
	
	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];
	
        $acl = $oreon->user->access;
        $allowedContacts = $acl->getContactAclConf(array('fields'  => array('contact_id', 
                                                                            'contact_name'),
                                                         'keys'    => array('contact_id'),
                                                         'get_row' => 'contact_name',
														 'order'   => 'contact_name'));
		$allowedAclGroups = $acl->getAccessGroups();
        $contactstring = "";
        if (count($allowedContacts)) {
            $first = true;
            foreach ($allowedContacts as $key => $val) {
                if ($first) {
                    $first = false;
                } else {
                    $contactstring .= ",";
                }
                $contactstring .= "'".$key."'";
            }
        } else {
            $contactstring = "''";
        }
        
	switch ($o)	{
		case "a" : 
			/*
			 * Add a contactgroup
			 */
			require_once($path."formContactGroup.php"); 
			break; 
		case "w" : 
			/*
			 * Watch a contactgroup
			 */
			require_once($path."formContactGroup.php"); 
			break;
		case "c" : 
			/*
			 * Modify a contactgroup
			 */
			require_once($path."formContactGroup.php"); 
			break;
		case "s" : 
			/*
			 * Activate a contactgroup
			 */
			enableContactGroupInDB($cg_id); 
			require_once($path."listContactGroup.php"); 
			break;
		case "u" : 
			/*
			 * Desactivate a contactgroup
			 */
			disableContactGroupInDB($cg_id); 
			require_once($path."listContactGroup.php"); 
			break;
		case "m" : 
			/*
			 * Duplicate n contact group
			 */
			multipleContactGroupInDB(isset($select) ? $select : array(), $dupNbr); 
			require_once($path."listContactGroup.php"); 
			break;
		case "d" : 
			/*
			 * 
			 */
			deleteContactGroupInDB(isset($select) ? $select : array()); 
			require_once($path."listContactGroup.php"); 
			break;
		default : 
			/*
			 * Delete n contact group
			 */
			require_once($path."listContactGroup.php"); 
			break;
	}
?>
