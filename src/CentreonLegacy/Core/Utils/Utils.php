<?php
/**
 * Copyright 2005-2017 Centreon
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

namespace CentreonLegacy\Core\Utils;

class Utils
{
    /**
     *
     * @var type
     */
    protected $dbConf;
    
    /**
     *
     * @var type
     */
    protected $dbMon;

    /**
     *
     * @param type $dbConf
     * @param type $dbMon
     */
    public function __construct($dbConf, $dbMon)
    {
        $this->dbConf = $dbConf;
        $this->dbMon = $dbMon;
    }

    /**
     *
     * @param type $fileName
     * @throws \Exception
     */
    public function executeSqlFile($fileName)
    {
        if (!file_exists($fileName)) {
            throw new \Exception('Cannot execute sql file "' . $fileName . '" : File does not exist.');
        }

        $content = file_get_contents($fileName);
        if (!$content) {
            throw new \Exception('Cannot get file content of "' . $fileName . '".');
        }

        $content = $this->replaceMacros($content);
        $lines = explode($content, "\n");

        foreach ($lines as $line) {
            $line = trim($line);
            if (!preg_match('/^(--|#)/', $line)) {
                $this->dbConf->query($line);
            }
        }
    }

    /**
     *
     * @param type $fileName
     * @throws \Exception
     */
    public function executePhpFile($fileName)
    {
        if (!file_exists($fileName)) {
            throw new \Exception('Cannot execute php file "' . $fileName . '" : File does not exist.');
        }

        require_once $fileName;
    }

    /**
     *
     * @param string $content
     * @return string
     */
    public function replaceMacros($content)
    {
        $macros = array(
            '@DB_CENTREON@' => hostCentreon,
            '@DB_CENTSTORAGE@' => hostCentsorage
        );

        foreach ($macros as $name => $value) {
            $content = str_replace($name, $value, $content);
        }

        return $content;
    }
}
