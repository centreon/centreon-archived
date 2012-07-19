<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/options/oreon/modules/moduleDependenciesValidator.php $
 * 
 */


// License validator
function parse_zend_license_file($file)
{
    $lines = preg_split('/\n/', file_get_contents($file));
    $infos = array();
    foreach ($lines as $line)
    {
        if (preg_match('/^([^= ]+)\s*=\s*(.+)$/', $line, $match))
            $infos[$match[1]] = $match[2];
    }
    return $infos;
}
    
// Load conf
ini_set('display_errors', '1');
require_once "/etc/centreon/centreon.conf.php";
require_once $centreon_path . '/www/autoloader.php';

// Modules access
$modulesPath = $centreon_path . 'www/modules/';
$modulesDirResource = opendir($modulesPath);

$XmlObj = new CentreonXML(true);
$XmlObj->startElement("validation");
while(false !== ($filename = readdir($modulesDirResource)))
{
    if ($filename != "." && $filename != ".." && $filename != ".SVN" && $filename != ".svn" && $filename != ".CSV")
    {
        $XmlObj->startElement("module");
        $XmlObj->writeAttribute('name', $filename);
        $checklistDir = $modulesPath.$filename . '/checklist/';
        
        if (file_exists($checklistDir))
        {
            require_once $checklistDir.'requirements.php';
            if ($critical || $warning)
            {
                if ($critical)
                    $XmlObj->writeAttribute('status', 'critical');
                elseif ($warning)
                    $XmlObj->writeAttribute('status', 'warning');
                
                foreach($message as $errorMessage)
                {
                    $XmlObj->startElement('message');
                    $XmlObj->writeElement('ErrorMessage', $errorMessage['ErrorMessage']);
                    $XmlObj->writeElement('Solution', $errorMessage['Solution']);
                    $XmlObj->endElement();
                }
            }
            else
            {
                $XmlObj->writeAttribute('status', 'ok');
            }
        }
        else
        {
            $XmlObj->writeAttribute('status', 'notfound');
        }
        $XmlObj->endElement();
    }
}

$XmlObj->endElement();
echo $XmlObj->output();



?>
