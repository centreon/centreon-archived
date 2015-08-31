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

	if (!isset($oreon)) 
		exit();

	require_once('SOAP/Client.php');
	require_once('functions.php');
	
	$path = dirname(__FILE__);
	
	/* Récupération des informations */
	$confPatch = getConfigPatch();
	$urlSoapServer = $confPatch['patch_url_service'];
	$installedVersion = getCurrentVersion();
	$branch = getBranch($installedVersion);
	
	/* Initialisation du client SOAP */
	$soapClient = new SOAP_Client($urlSoapServer);
	
	/* Récupération du dernier patch de sécurité pour la branche installé */
	$params = array('project' => 'oreon', 'branch' => $branch, 'clientVersion' => $installedVersion);
	$secu = $soapClient->call('getSecurity', $params);
	if (PEAR::isError($secu)) {
		$msgErr = _("Cannot get last version");
		$secu = '';
	}
	if ($confPatch['patch_type_secu'] == 'N') {
		$secu = '';
	}
	
	/* Récupération de la dernière version dsponible */
	$params = array('project' => 'oreon', 'clientVersion' => $installedVersion);
	$listVersion = $soapClient->call('getListVersion', $params);
	if (PEAR::isError($listVersion)) {
		$msgErr = _("Cannot get last version");
	} else {
		$newVersionInfo = checkNewVersion($listVersion, $confPatch);
	}
	
	if (isset($newVersionInfo) && $newVersionInfo == '') {
		$hasUpdate = false;
	} else {
		$hasUpdate = true;
	}
	
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	if (isset($msgErr))
		$tpl->assign('msgErr', $msgErr);
	else {
		$tpl->assign("hasUpdate", $hasUpdate);
		$tpl->assign("security", $secu);
		$tpl->assign("lastVersion", $newVersionInfo);
		$tpl->assign("lastVersionType", getVersionType($newVersionInfo));
		$tpl->assign("updateSecu", _("Security patch available"));
		$tpl->assign("update", _("Update patch available"));
		$tpl->assign("uptodate", _("Centreon is updated."));
		
		$tpl->assign("oreon_web_path", $oreon->optGen["oreon_web_path"]);
	}
	$tpl->display("checkVersion.ihtml");
?>