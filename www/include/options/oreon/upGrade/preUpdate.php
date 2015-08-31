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
	
	$path = dirname(__FILE__);
	
	/* Initialisation de la template */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	require_once('SOAP/Client.php');
	require_once('functions.php');
	
	if (!isset($_GET['version'])) {
		$msgErr = _("No version defined.");
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
		$msgErr = _("Can't get list of files.");
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
		$msgErr = _("Can't open configuration file : /etc/oreon.conf");
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
			$msgErr = _("Can't get file'");
			break;
		}
		$outputString .= sprintf(_("%s is downloaded.")."<br/>", $file->filename);
		if ($file->type == 'a') {
			$readme .= sprintf(_("In order to complete your upgrade (%s), unzip the downloaded file, and follow the instructions in README\n"), $file->filename);
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
		$msgErr = _("Can't open patch");
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
		$readme .= sprintf(_("launch %s with root permissions.\n"), $shellName);
		$fd = fopen($pathPatch . '/' . $shellName, 'w');
		writeShellHeaders($fd, $oreon->optGen['oreon_path'], $pathPatch);
		foreach ($batchPatch as $patch) {
			replaceVarInPatch($pathPatch . '/' . $patch->filename, $sysvar);
			writeShellPatch($fd, $patch, $conf_centreon['user'], $conf_centreon['password'], $conf_centreon['db']);
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