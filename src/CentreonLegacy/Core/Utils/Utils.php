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

use Psr\Container\ContainerInterface;

class Utils
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $services;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->services = $services;
    }

    /**
     * Require configuration file
     *
     * @param string $configurationFile
     * @param string $type
     * @return array
     */
    public function requireConfiguration($configurationFile, $type = 'install')
    {
        $configuration = array();

        if ($type == 'install') {
            $module_conf = array();
            require $configurationFile;
            $configuration = $module_conf;
        } elseif ($type == 'upgrade') {
            $upgrade_conf = array();
            require $configurationFile;
            $configuration = $upgrade_conf;
        }

        return $configuration;
    }

    /**
     *
     * @param string $fileName
     * @param array $customMacros
     * @throws \Exception
     */
    public function executeSqlFile($fileName, $customMacros = array(), $monitoring = false)
    {
        $dbName = 'configuration_db';
        if ($monitoring) {
            $dbName = 'realtime_db';
        }
        if (!file_exists($fileName)) {
            throw new \Exception('Cannot execute sql file "' . $fileName . '" : File does not exist.');
        }

        $file = fopen($fileName, "r");
        $str = '';
        while (!feof($file)) {
            $line = fgets($file);
            if (!preg_match('/^(--|#)/', $line)) {
                $pos = strrpos($line, ";");
                $str .= $line;
                if ($pos !== false) {
                    $str = rtrim($this->replaceMacros($str, $customMacros));
                    $this->services->get($dbName)->query($str);
                    $str = '';
                }
            }
        }
        fclose($file);
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

        // init parameters to be compatible with old module upgrade scripts
        $pearDB = $this->services->get('configuration_db');
        $pearDBStorage = $this->services->get('realtime_db');
        $centreon_path = $this->services->get('configuration')->get('centreon_path');

        require_once $fileName;
    }

    /**
     *
     * @param string $content
     * @param array $customMacros
     * @return string
     */
    public function replaceMacros($content, $customMacros = array())
    {
        $macros = array(
            'DB_CENTREON' => $this->services->get('configuration')->get('db'),
            'DB_CENTSTORAGE' => $this->services->get('configuration')->get('dbcstg')
        );

        if (count($customMacros)) {
            $macros = array_merge($macros, $customMacros);
        }

        foreach ($macros as $name => $value) {
            $content = str_replace('@' . $name . '@', $value, $content);
        }

        return $content;
    }

    public function xmlIntoArray($path)
    {
        $xml = simplexml_load_file($path);
        return $this->objectIntoArray($xml);
    }

    /**
     *
     * @param array $arrObjData
     * @param array $skippedKeys
     * @return string
     */
    public function objectIntoArray($arrObjData, $skippedKeys = array())
    {
        $arrData = array();

        if (is_object($arrObjData)) {
            $arrObjData = get_object_vars($arrObjData);
        }

        if (is_array($arrObjData)) {
            foreach ($arrObjData as $index => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = self::objectIntoArray($value, $skippedKeys);
                }
                if (in_array($index, $skippedKeys)) {
                    continue;
                }
                $arrData[$index] = $value;
            }
        }
        if (!count($arrData)) {
            $arrData = "";
        }
        return $arrData;
    }

    /**
     * @param $endPath
     * @return bool|string
     */
    public function buildPath($endPath)
    {
        return realpath(__DIR__ . '/../../../../www/' . $endPath);
    }

    /**
     * @param $password
     * @param string $algo
     * @return string
     */
    public function encodePass($password, $algo = 'md5')
    {
        $encodePassword = '';
        switch ($algo) {
            case 'md5':
                $encodePassword .= 'md5__' . md5($password);
                break;
            case 'sha1':
                $encodePassword .= 'sha1__' . sha1($password);
                break;
            case 'argon2i':
                $encodePassword = password_hash($password, PASSWORD_BCRYPT);
                break;
            default:
                $encodePassword .= 'md5__' . md5($password);
                break;
        }
        return $encodePassword;
    }

    /**
     * @param $pattern
     * @return null
     */
    public function detectPassPattern($pattern)
    {
        $patternData = explode('__', $pattern);
        if (isset($patternData[1])) {
            return $patternData[0];
        } else {
            return null;
        }
    }
}
