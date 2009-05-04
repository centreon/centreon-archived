<?php
/*
 * Copyright 2005-2009 MERETHIS
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