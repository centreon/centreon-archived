<?php

/*
 * Copyright 2005-2014 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonConfiguration\Internal\Poller\Template;

use CentreonConfiguration\Internal\Poller\LiteTemplate;
use Centreon\Internal\Module\Informations;

/**
 * Description of Manager
 *
 * @author lionel
 */
class Manager
{
    /**
     * 
     * @param string $filePath
     * @return boolean
     */
    public static function liteFileParser($filePath)
    {
        $liteTemplate = array(
            'engine' => false,
            'broker' => false
        );
        $templateContent = json_decode(file_get_contents($filePath), true);
        
        if (isset($templateContent['name'])) {
            $liteTemplate['name'] = $templateContent['name'];
        } else {
            $liteTemplate['name'] = basename($filePath, '.json');
        }
        
        if (isset($templateContent['content']['engine'])) {
            $liteTemplate['engine'] = true;
        }
        
        if (isset($templateContent['content']['broker'])) {
            $liteTemplate['broker'] = true;
        }
        
        return $liteTemplate;
    }
    
    /**
     * 
     * @return array
     */
    public static function buildTemplatesList()
    {
        $rawTemplatesFileList = array();
        $moduleList = Informations::getModuleList();
        foreach ($moduleList as $module) {
            $modulePath = Informations::getModulePath($module);
            $pollerTemplatesFilePath = $modulePath . '/pollers/*.json';
            $rawTemplatesFileList = array_merge($rawTemplatesFileList, glob($pollerTemplatesFilePath));
        }
        
        $templatesList = array();
        foreach ($rawTemplatesFileList as $templateFile) {
            $liteTemplate = self::liteFileParser($templateFile);

            if (!isset($templatesList[$liteTemplate['name']])) {
                $myLiteTemplate = new LiteTemplate($liteTemplate['name']);
                if ($liteTemplate['engine']) {
                    $myLiteTemplate->setEnginePath($templateFile);
                }
                if ($liteTemplate['broker']) {
                    $myLiteTemplate->setBrokerPath($templateFile);
                }
                $templatesList[$liteTemplate['name']] = $myLiteTemplate;
            } else {
                $enginePath = $templatesList[$liteTemplate['name']]->getEnginePath();
                if ($liteTemplate['engine'] && empty($enginePath)) {
                    $templatesList[$liteTemplate['name']]->setEnginePath($templateFile);
                }
                $brokerPath = $templatesList[$liteTemplate['name']]->getBrokerPath();
                if ($liteTemplate['broker']) {
                    $templatesList[$liteTemplate['name']]->setBrokerPath($templateFile);
                }
            } 
            unset($liteTemplate);
        }

        return $templatesList;
    }
}
