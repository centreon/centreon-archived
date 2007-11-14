<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
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
		$msgErr = $lang['checkVersion_msgErr01'];
		$secu = '';
	}
	if ($confPatch['patch_type_secu'] == 'N') {
		$secu = '';
	}
	
	/* Récupération de la dernière version dsponible */
	$params = array('project' => 'oreon', 'clientVersion' => $installedVersion);
	$listVersion = $soapClient->call('getListVersion', $params);
	if (PEAR::isError($listVersion)) {
		$msgErr = $lang['checkVersion_msgErr01'];
	} else {
		$newVersionInfo = checkNewVersion($listVersion, $confPatch);
	}
	
	if ($newVersionInfo == '') {
		$hasUpdate = false;
	} else {
		$hasUpdate = true;
	}
	
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	$tpl->assign('msgErr', $msgErr);
	$tpl->assign('hasUpdate', $hasUpdate);
	$tpl->assign('security', $secu);
	$tpl->assign('lastVersion', $newVersionInfo);
	$tpl->assign('lastVersionType', getVersionType($newVersionInfo));
	$tpl->assign('lang', $lang);
	$tpl->assign('oreon_web_path', $oreon->optGen['oreon_web_path']);
	$tpl->display("checkVersion.ihtml");
?>