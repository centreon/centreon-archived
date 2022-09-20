<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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
     * @param string $fileName
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
            $value = $value ?? '';
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
    public function encodePass($password, $algo = 'md5'): string
    {
        /*
         * Users passwords must be verified as md5 encrypted
         * before they can be encrypted as bcrypt.
         */
        if ($algo === 'md5') {
            return 'md5__' . md5($password);
        }

        return password_hash($password, PASSWORD_BCRYPT);
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
