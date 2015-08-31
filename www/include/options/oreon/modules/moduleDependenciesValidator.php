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
require_once "@CENTREON_ETC@/centreon.conf.php";
require_once $centreon_path . '/www/autoloader.php';

// Modules access
$modulesPath = $centreon_path . 'www/modules/';
$modulesDirResource = opendir($modulesPath);

$XmlObj = new CentreonXML(true);
$XmlObj->startElement("validation");
$message = array();

while(false !== ($filename = readdir($modulesDirResource)))
{
    if ($filename != "." && $filename != ".." && $filename != ".SVN" && $filename != ".svn" && $filename != ".CSV")
    {
        $XmlObj->startElement("module");
        $XmlObj->writeAttribute('name', $filename);
        $checklistDir = $modulesPath.$filename . '/checklist/';
        $warning = false;
        $critical = false;
        
        if (file_exists($checklistDir.'requirements.php'))
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
                if (isset($customAction) && is_array($customAction))
                {
                    $XmlObj->writeAttribute('customAction', $customAction['action']);
                    $XmlObj->writeAttribute('customActionName', $customAction['name']);
                }
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
