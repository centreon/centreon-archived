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

use Centreon\Internal\Exception;
use Centreon\Internal\Exception\Filesystem\DirectoryNotExistsException;

/**
 * Utils for filesystem directories
 *
 * @author Maximilien Bersoult
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class Directory
{
    /**
     * @var array List of directory where cannot delete files
     */
    private static $securityDir = array(
        '',
        '/etc',
        '/usr',
        '/var',
        '/'
    );

    /**
     * Delete a directory with recursive
     * Check some security before delete
     *
     * @param string $dirname The directory to delete
     * @param boolean $recursive If delete recursive or not
     * @return boolean
     */
    public static function delete($dirname, $recursive = false)
    {
        if (in_array($dirname, self::$securityDir)) {
            return false;
        }
        $dirname = realpath($dirname);
        if (false === $dirname || false === is_dir($dirname)) {
            return false;
        }
        if (false === $recursive) {
           return rmdir($dirname);
        }
        $ok = true;
        $fh = opendir($dirname);
        while ($file = readdir($fh)) {
            if ($file != '.' && $file != '..') {
                $filename = $dirname . '/' . $file;
                if (is_dir($filename)) {
                    if (false === self::delete($filename, $recursive)) {
                        $ok = false;
                    }
                } else {
                    if (false === unlink($filename)) {
                        $ok = false;
                    }
                } 
            }
        } 
        closedir($fh);
        if (false === $ok) {
            return false;
        }
        return rmdir($dirname);
    }

    /**
     * Get the temporary name for a directory
     *
     * @param string $prefix The prefix for the temporary directory
     * @param boolean $create If create the directory
     * @return string The full path of temporary directory
     * @throws \Centreon\Internal\Exception When cannot create the directory
     */
    public static function temporary($prefix = '', $create = false)
    {
        $dirname = sys_get_temp_dir() . '/' . uniqid($prefix);
        if ($create) {
            if (false === mkdir($dirname)) {
                throw new Exception('Error when create temporary directory');
            }
        }
        return $dirname;
    }
    
    /**
     * 
     * @param string $sourceDirectory
     * @param string $destinationDirectory
     */
    public static function copy($sourceDirectory, $destinationDirectory)
    {
        $dir = opendir($sourceDirectory);
        
        if (!file_exists($destinationDirectory)) {
            mkdir($destinationDirectory, 0777, true);
        }
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($sourceDirectory . '/' . $file) ) { 
                    self::copy($sourceDirectory . '/' . $file,$destinationDirectory . '/' . $file); 
                } 
                else { 
                    copy($sourceDirectory . '/' . $file,$destinationDirectory . '/' . $file); 
                }
            } 
        }
        closedir($dir); 
    }
    
    /**
     * 
     * @param string $directory Directory to check
     * @param string $pattern 
     * @return boolean
     * @throws DirectoryNotExistsException
     */
    public static function isEmpty($directory, $pattern = "*")
    {
        if (!file_exists($directory)) {
            throw new DirectoryNotExistsException('Error when create temporary directory', 1104);
        }
        
        $directoryEmpty = false;
        if (count(glob($directory . '/' . $pattern)) == 0) {
             $directoryEmpty = true;
        }
        
        return $directoryEmpty;
    }
}
