<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */


// License validator
function parse_zend_license_file($file)
{
    $lines = preg_split('/\n/', file_get_contents($file));
    $infos = array();
    foreach ($lines as $line) {
        if (preg_match('/^([^= ]+)\s*=\s*(.+)$/', $line, $match)) {
            $infos[$match[1]] = $match[2];
        }
    }
    return $infos;
}
    
/* Load conf */
require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . '/www/autoloader.php';

/* Modules access */
$modulesPath = _CENTREON_PATH_ . 'www/modules/';
$modulesDirResource = opendir($modulesPath);

$XmlObj = new CentreonXML(true);
$XmlObj->startElement("validation");
$message = array();

while (false !== ($filename = readdir($modulesDirResource))) {
    if ($filename != "." && $filename != ".." && $filename != ".SVN" && $filename != ".svn" && $filename != ".CSV") {
        $XmlObj->startElement("module");
        $XmlObj->writeAttribute('name', $filename);
        $checklistDir = $modulesPath.$filename . '/checklist/';
        $warning = false;
        $critical = false;
        
        if (file_exists($checklistDir.'requirements.php')) {
            require_once $checklistDir.'requirements.php';
            if ($critical || $warning) {
                if ($critical) {
                    $XmlObj->writeAttribute('status', 'critical');
                } elseif ($warning) {
                    $XmlObj->writeAttribute('status', 'warning');
                }

                foreach ($message as $errorMessage) {
                    $XmlObj->startElement('message');
                    $XmlObj->writeElement('ErrorMessage', $errorMessage['ErrorMessage']);
                    $XmlObj->writeElement('Solution', $errorMessage['Solution']);
                    $XmlObj->endElement();
                }
            } else {
                $XmlObj->writeAttribute('status', 'ok');
                if (isset($customAction) && is_array($customAction)) {
                    $XmlObj->writeAttribute('customAction', $customAction['action']);
                    $XmlObj->writeAttribute('customActionName', $customAction['name']);
                }
            }
        } else {
            $XmlObj->writeAttribute('status', 'notfound');
        }
        $XmlObj->endElement();
    }
}

$XmlObj->endElement();

echo $XmlObj->output();
