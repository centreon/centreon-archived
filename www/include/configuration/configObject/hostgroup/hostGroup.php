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

	isset($_GET["hg_id"]) ? $hG = $_GET["hg_id"] : $hG = NULL;
	isset($_POST["hg_id"]) ? $hP = $_POST["hg_id"] : $hP = NULL;
	$hG ? $hg_id = $hG : $hg_id = $hP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;


	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/configuration/configObject/hostgroup/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

    $acl = $oreon->user->access;
    $dbmon = $oreon->broker->getBroker() == 'broker' ? new CentreonDB('centstorage') : new CentreonDB('ndo');
    $aclDbName = $acl->getNameDBAcl($oreon->broker->getBroker());
    $hgs = $acl->getHostGroupAclConf(null, $oreon->broker->getBroker());

    function mywrap ($el) {
        return "'".$el."'";
    }
    $hgString = implode(',', array_map('mywrap', array_keys($hgs)));
    $hoststring = $acl->getHostsString('ID', $dbmon);

	switch ($o)	{
		case "a" : require_once($path."formHostGroup.php"); break; #Add a Hostgroup
		case "w" : require_once($path."formHostGroup.php"); break; #Watch a Hostgroup
		case "c" : require_once($path."formHostGroup.php"); break; #Modify a Hostgroup
		case "s" : enableHostGroupInDB($hg_id); require_once($path."listHostGroup.php"); break; #Activate a Hostgroup
		case "ms" : enableHostGroupInDB(NULL, isset($select) ? $select : array()); require_once($path."listHostGroup.php"); break;
		case "u" : disableHostGroupInDB($hg_id); require_once($path."listHostGroup.php"); break; #Desactivate a Hostgroup
		case "mu" : disableHostGroupInDB(NULL, isset($select) ? $select : array()); require_once($path."listHostGroup.php"); break;
		case "m" : multipleHostGroupInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listHostGroup.php"); break; #Duplicate n Host grou
		case "d" : deleteHostGroupInDB(isset($select) ? $select : array()); require_once($path."listHostGroup.php"); break; #Delete n Host group
		default : require_once($path."listHostGroup.php"); break;
	}
?>
