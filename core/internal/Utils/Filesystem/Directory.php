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

namespace Centreon\Internal\Utils\Filesystem;

use Centreon\Internal\Exception;

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
}
