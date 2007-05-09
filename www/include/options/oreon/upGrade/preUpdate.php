<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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
	
	$path = dirname(__FILE__);
	
	/* Initialisation de la template */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	require_once('SOAP/Client.php');
	require_once('functions.php');
	
	if (!isset($_GET['version'])) {
		$msgErr = $lang['preUpdate_msgErr03'];
		$tpl->assign('msgErr', $msgErr);
		$tpl->display('preUpdate.ihtml');
		exit();
	}
	
	/* Initialisation des variables */
	$confPatch = getConfigPatch();
	$urlSoapServer = $confPatch['patch_url_service'];
	$installedVersion = getCurrentVersion();
	$version = $_GET['version'];
	$installedBranch = getBranch($installedVersion);
	$versionBranch = getBranch($version);
	
	$doStable = false;
	if ($installedBranch != $versionBranch) {
		$doStable = true;
	}
	
	/* Initialisation du client SOAP */
	$soapClient = new SOAP_Client($urlSoapServer);
	
	/* Récupération la liste des fichiers pour patcher */
	$params = array('project' => 'oreon', 'version' => $version, 'clientVersion' => $installedVersion);
	$listFiles = $soapClient->call('getListPatch', $params);
	if (PEAR::isError($listFiles)) {
		$msgErr = $lang['preUpdate_msgErr01'];
		$tpl->assign('msgErr', $msgErr);
		$tpl->display('preUpdate.ihtml');
		exit();
	}
	
	if (ini_get("allow_url_fopen")) {
		$method = 'copy';
	} elseif (function_exists('curl_init')) {
		$method = 'curl';
	} else {
		$method = 'socket';
	}
	
	/* Tableau pour le changement des variables sys */
	$oreon_etc = "/etc/oreon.conf";
	$fd = @fopen($oreon_etc, 'r');
	if (!$fd) {
		$msgErr = $lang['preUpdate_msgErr04'];
		$tpl->assign('msgErr', $msgErr);
		$tpl->display('preUpdate.ihtml');
	}
	$sysvar = array();
	while ($line = fgets($fd)) {
		$line = trim($line);
		if (preg_match('/^([\w_]*)=(.*)$/', $line, $matches)) {
			$sysvar['source'][] = '@' . $matches[1] . '@';
			$sysvar['dest'][] = $matches[2];
		}
	}
	fclose($fd);
	$sysvar['source'][] = '@OREON_PATH@';
	$sysvar['dest'][] = $oreon->optGen['oreon_path'];
	
	
	/* Téléchargement des fichiers */
	$outputString = '';
	$readme = '';
	$hasPatch = false;
	$batchPatch = array();
	$lastVersion = '';
	foreach ($listFiles->files as $file) {
		$urlPatch = $confPatch['patch_url_download'];
		$pathPatch = $confPatch['patch_path_download'];
		if (!getFile($file, $urlPatch, $pathPatch, $method)) {
			$msgErr = $lang['preUpdate_msgErr02'];
			break;
		}
		$outputString .= sprintf($lang['preUpdate_fileDownloaded'], $file->filename);
		if ($file->type == 'a') {
			$readme .= sprintf($lang['preUpdate_installArchive'], $file->filename);
			$lastVersion = $file->version;
		} else {
			$hasPatch = true;
			$batchPatch[] = $file;
			$lastVersion = $file->version;
		}
	}
	
	if ($msgErr != '') {
	       $tpl->assign('msgErr', $msgErr);
	       $tpl->display('preUpdate.ihtml');
	       exit();
	} elseif ($lastVersion == '') {
		$msgErr = $lang['preUpdate_msgErr06'];
		$tpl->assign('msgErr', $msgErr);
		$tpl->display('preUpdate.ihtml');
		exit();
	}
	
	$readmeName = "README." . $installedVersion . "-" . $lastVersion;
	/* Construction du shell pour les patchs */ 
	if ($hasPatch) {
		$shellName = "upgrade_";
		if ($doStable) {
			$shellName .= $versionBranch;
		} else {
			$shellName .= $installedVersion;
		}
		$shellName .= "-" . $lastVersion . ".sh";
		$readme .= sprintf($lang['preUpdate_shellPatch'], $shellName);
		$fd = fopen($pathPatch . '/' . $shellName, 'w');
		writeShellHeaders($fd, $oreon->optGen['oreon_path'], $pathPatch);
		foreach ($batchPatch as $patch) {
			replaceVarInPatch($pathPatch . '/' . $patch->filename, $sysvar);
			writeShellPatch($fd, $patch, $conf_oreon['user'], $conf_oreon['password'], $conf_oreon['db']);
		}
		writeShellFooters($fd);
		fclose($fd);
	}
	
	/* Ecriture du fichier README */
	$fd = fopen($pathPatch . '/' . $readmeName, 'w');
	fwrite($fd, $readme);
	fclose($fd);
	
	/* Affichage */
	$tpl->assign('msgErr', $msgErr);
	$tpl->assign('output', $outputString);
	$tpl->assign('readme', nl2br(htmlentities($readme, ENT_COMPAT, 'UTF-8')));
	$tpl->display('preUpdate.ihtml');
?>