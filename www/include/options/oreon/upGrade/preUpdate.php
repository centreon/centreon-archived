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
		$outputString .= sprintf(_("%s is downloaded.<br/>"), $file->filename);
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