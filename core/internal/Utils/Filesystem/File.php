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

namespace Centreon\Internal\Utils\Filesystem;

/**
 * Utils for filesystem files
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class File
{
    /**
     * 
     * @param string $dirname
     * @param string $extension
     * @return array
     */
    public static function getFiles($dirname, $extension)
    {
        $finalFileList = array();
        $path = realpath($dirname);
        
        if (file_exists($path)) {
        
            $listOfFiles = glob($path . '/*');

            while (count($listOfFiles) > 0) {
                $currentFile = array_shift($listOfFiles);
                if (is_dir($currentFile)) {
                    $listOfFiles = array_merge($listOfFiles, glob($currentFile . '/*'));
                } elseif (pathinfo($currentFile, PATHINFO_EXTENSION) == $extension) {
                    $finalFileList[] = $currentFile;
                }
            }
        }
        return $finalFileList;
    }
}
