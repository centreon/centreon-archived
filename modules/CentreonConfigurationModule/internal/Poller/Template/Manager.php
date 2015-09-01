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
