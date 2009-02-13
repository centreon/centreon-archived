<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

/**
 * Récupère la configuration pour la gestion des mises à jour
 * 
 * @return array La liste des configurations
 */
function getConfigPatch() {
	global $pearDB;
	$query = "SELECT * FROM `options` WHERE `key` IN ('patch_type_stable', 'patch_type_RC', 'patch_type_patch', 'patch_type_secu', 'patch_type_beta', 'patch_url_service', 'patch_url_download', 'patch_path_download')";
	$DBRESULT =& $pearDB->query($query);
	while ($result =& $DBRESULT->fecthRow()) {
		$row[$result["key"]] = $result["value"];
	}
	$DBRESULT->free();
	return($row);
}

/**
 * Récupère la version courante d'Oreon
 * 
 * @return string Le numéro de version
 */
function getCurrentVersion() {
	global $pearDB;
	$query = "SELECT value FROM informations WHERE `key`='version'";
	$DBRESULT =& $pearDB->query($query);	
	if ($DBRESULT->numRows() != 1) {
		return(false);
	}
	$row =& $DBRESULT->fetchRow();
	$version = $row['value'];
	return($version);
}

/**
 * Récupère la branche à partir d'un version
 * 
 * @param string $version La version
 * @return string La branche
 */
function getBranch($version) {
	preg_match('/^([\d\.]+)/', $version, $matches);
	$branch = preg_replace('/`.$/', '', $matches[1]);
	return($branch); 
}

/**
 * Récupère le type de la version (beta, patch, RC, security, stable)
 * 
 * @param string $version La version
 * @return string Le type de la version
 */
function getVersionType($version) {
	if (!preg_match('/^[\d\.]*[\d][bRCpl]*[\d]*[s]?$/', $version)) {
		return('');
	}
	if (preg_match('/s$/', $version)) {
		return('security');
	} elseif (preg_match('/pl[\d]+$/', $version)) {
		return('patch');
	} elseif (preg_match('/b[\d]+$/', $version)) {
		return('beta');
	} elseif (preg_match('/RC[\d]+$/', $version)) {
		return('RC');
	} elseif (preg_match('/[^\w]\.?[\d]+$/', $version)) {
		return('stable');
	} else {
		return('');
	}
}

/**
 * Récupère la dernière version disponible ou vide
 * 
 * @param string $listVersion La liste des versions disponibles
 * @param string $config La configuration de l'utilisateur
 * @return string La dernière version disponible
 */
function checkNewVersion($listVersion, $config) {
	foreach ($listVersion as $version) {
		$type = getVersionType($version);
		switch ($type) {
			case "beta":
				if ($config['patch_type_beta'] == 'Y') {
					return($version);
				}
				break;
			case "patch":
				if ($config['patch_type_patch'] == 'Y') {
					return($version);
				}
				break;
			case "RC":
				if ($config['patch_type_RC'] == 'Y') {
					return($version);
				}
				break;
			case "security":
				if ($config['patch_type_secu'] == 'Y') {
					return($version);
				}
				break;
			case "stable":
				if ($config['patch_type_stable'] == 'Y') {
					return($version);
				}
				break;
			default:
				break;
		}
	}
	return('');
}

/**
 * Télécharge le fichier et vérifie le MD5
 * 
 * @param stdClass $file Le tableau du fichier à télécharger avec le nom du fichier et le MD5
 * @param string $urlpatch L'url de téléchargement des patchs
 * @param string $pathpatch Le chemin de stackage des patchs
 * @param string $method Méthode pour le téléchargement
 * @return bool Si le fichier est téléchargé et correspond avec le MD5 
 */
function getFile($file, $urlpatch, $pathpatch, $method = 'copy') {
	$source = $urlpatch . '/' . $file->filename;
	$dest = $pathpatch . '/' . $file->filename;
	switch ($method) {
		case 'copy':
			if (!copy($source, $dest)) {
				return(false);
			}
			if (md5_file($dest) != $file->md5sum) {
				return(false);
			}
			break;
		case 'curl':
			$curl = curl_init($source);
			$fd = @fopen($dest, 'w');
			curl_setopt($curl, CURLOPT_FILE, $fd);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			@curl_exec($curl);
			fclose($fd);
			curl_close($curl);
			if (md5_file($dest) != $file->md5sum) {
				return(false);
			}
			break;
		case 'socket':
			preg_match('!http://([^/]+)(/.*)$', $source, $matches);
			$host = $matches[1];
			$pathfile = $matches[2];
			$addr = gethostbyname($host);
			$req = "GET " . $pathfile . " HTTP/1.1\r\n";
			$req .= "Host: " . $host . "\r\n";
			$req .= "Connection: Close\r\n\r\n";
			$fd = fopen($dest, 'w');
			$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($sock < 0) {
				return(false);
			}
			$res = socket_connect($sock, $addr, 80);
			if ($res < 0) {
				return(false);
			}
			socket_write($sock, $req, strlen($req));
			while ($out = socket_read($sock, 2048)) {
				fwrite($fd, $out);
			}
			socket_close($sock);
			fclose($fd);
			if (md5_file($dest) != $file->md5sum) {
				return(false);
			}
			break;
		default:
			return(false);
	}
	return(true);
}

/**
 * Ecrit l'entête du shell bash
 * 
 * @param resource $fd La ressource du fichier shell bash
 * @param string $oreonInstallPath Le chemin d'installation d'oreon
 * @param string $patchPath Le chemin des fichier de patch
 */
function writeShellHeaders($fd, $oreonInstallPath, $patchPath) {
	global $lang;
	fwrite($fd, "#!/bin/bash\n\n");
	fwrite($fd, "PATH_OREON=" . $oreonInstallPath . "\n");
	fwrite($fd, "PATH_PATCH=" . $patchPath . "\n\n");
	fwrite($fd, "PATH_OLD=`pwd`\n\n");
	fwrite($fd, 'cd ${PATH_OREON}' . "\n\n");
	fwrite($fd, 'echo "' . _("Execution start") . '"' . "\n\n");
}

/**
 * Ecrit le pied du shell bash
 * 
 * @param resource $fd La ressource du fichier shell bash
 */
function writeShellFooters($fd) {
	global $lang;
	fwrite($fd, 'cd ${PATH_OLD}' . "\n\n");
	fwrite($fd, 'echo "' . _("Execution end") . '"');
}

/**
 * Ecrit dans le shell bash les informations pour un patch
 * 
 * @param resource $fd La ressource du fichier shell bash
 * @param stdClass $file Le fichier patch
 * @param string $dbuser Le nom de l'utilisateur de la base de données
 * @param string $dbpass Le mot de passe pour la base de données
 * @param string $dbname Le nom de la base de données
 */
function writeShellPatch($fd, $file, $dbuser, $dbpass, $dbname) {
	global $lang;
	$query = 'UPDATE `informations` SET `value`="' . $file->version . '" WHERE `key`="version"';
	fwrite($fd, 'patch -p1 --dry-run < ${PATH_PATCH}/' . $file->filename . ' > /dev/null' . "\n");
	fwrite($fd, 'if [ $? -ne 0 ]; then');
	fwrite($fd, "\n\t" . 'echo "' . sprintf(_("Error when installing patch : %s."), $file->filename) . '"');
	fwrite($fd, "\n\texit 1\nfi\n\n");
	fwrite($fd, 'patch -p1 < ${PATH_PATCH}/' . $file->filename . ' > /dev/null' . "\n");
	fwrite($fd, "mysql -u " . $dbuser . " -p" . $dbpass . " " . $dbname . " -e '". $query . "'\n");
	fwrite($fd, 'echo "' . sprintf(_("%s patch is installed."), $file->version) . '"' . "\n\n");
}

/**
 * Remplace les variables @..@ pour le bon fonctionnnement des patchs
 * 
 * @param string $patchfile Le nom du fichier
 * @param array $sysvar La liste des variables et leurs valeurs de remplacement
 */
function replaceVarInPatch($patchfile, $sysvar) {
	$file = file_get_contents($patchfile, false);
	$file = str_replace($sysvar['source'], $sysvar['dest'], $file);
	$fd = fopen($patchfile, 'w');
	fwrite($fd, $file);
	fclose($fd);
}
?>