<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * For more information : command@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once _CENTREON_PATH_ . "/lib/Centreon/Object/Timezone/Timezone.php";
require_once _CENTREON_PATH_ . "/lib/Centreon/Object/Object.php";

/**
 * Centreon Settings
 *
 * @author Sylvestre Ho
 */
class CentreonSettings extends CentreonObject
{
    const ISSTRING = 0;
    const ISNUM = 1;
    const KEYNOTALLOWED = "This parameter cannot be modified";
    const VALUENOTALLOWED = "This parameter value is not valid";
    protected $authorizedOptions;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);

        $this->authorizedOptions = array(
            'broker' => array('values' => array('ndo', 'broker')),
            'centstorage' => array('values' => array('0', '1')),
            'gmt' => array(
                'format' => self::ISSTRING,
                'getterFormatMethod' => 'getTimezonenameFromId',
                'setterFormatMethod' => 'getTimezoneIdFromName'
            ),
            'mailer_path_bin' => array('format' => self::ISSTRING),
            'snmptt_unknowntrap_log_file' => array('format' => self::ISSTRING),
            'snmpttconvertmib_path_bin' => array('format' => self::ISSTRING),
            'perl_library_path' => array('format' => self::ISSTRING),
            'rrdtool_path_bin' => array('format' => self::ISSTRING),
            'debug_path' => array('format' => self::ISSTRING),
            'debug_auth' => array('values' => array('0', '1')),
            'debug_nagios_import' => array('values' => array('0', '1')),
            'debug_rrdtool' => array('values' => array('0', '1')),
            'debug_ldap_import' => array('values' => array('0', '1')),
            'enable_autologin' => array('values' => array('0', '1')),
            'interval_length' => array('format' => self::ISNUM),
            'enable_gmt' => array('values' => array('0', '1')),
            'nagios_path_img' => array('format' => self::ISSTRING),
        );
    }

    /**
     * Display unsupported method
     *
     * @param string $method
     * @return void
     */
    protected function unsupportedMethod($method)
    {
        echo sprintf("The %s method is not supported on this object\n", $method);
    }

    /**
     * @param null $params
     * @param array $filters
     */
    public function show($params = null, $filters = array())
    {
        $sql = "SELECT `key`, `value` FROM `options` ORDER BY `key`";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetchAll();
        echo "parameter" . $this->delim . "value\n";
        foreach ($res as $row) {
            if (isset($this->authorizedOptions[$row['key']])) {
                if (isset($this->authorizedOptions[$row['key']]['getterFormatMethod'])) {
                    $method = $this->authorizedOptions[$row['key']]['getterFormatMethod'];
                    $row['value'] = $this->$method($row['value']);
                }
                echo $row['key'] . $this->delim . $row['value'] . "\n";
            }
        }
    }

    /**
     * @param null $parameters
     * @return int|mixed|void
     */
    public function add($parameters = null)
    {
        $this->unsupportedMethod(__FUNCTION__);
    }

    /**
     * @param null $objectName
     */
    public function del($objectName = null)
    {
        $this->unsupportedMethod(__FUNCTION__);
    }

    /**
     * Set parameters
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function setparam($parameters = null)
    {
        if (is_null($parameters)) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        list($key, $value) = $params;
        if (!isset($this->authorizedOptions[$key])) {
            throw new CentreonClapiException(self::KEYNOTALLOWED);
        }

        if (isset($this->authorizedOptions[$key]['format'])) {
            if ($this->authorizedOptions[$key]['format'] == self::ISNUM && !is_numeric($value)) {
                throw new CentreonClapiException(self::VALUENOTALLOWED);
            } elseif (is_array($this->authorizedOptions[$key]['format']) == self::ISSTRING && !is_string($value)) {
                throw new CentreonClapiException(self::VALUENOTALLOWED);
            }
        }

        if (isset($this->authorizedOptions[$key]['values']) &&
            !in_array($value, $this->authorizedOptions[$key]['values'])) {
            throw new CentreonClapiException(self::VALUENOTALLOWED);
        }

        if (isset($this->authorizedOptions[$key]['setterFormatMethod'])) {
            $method = $this->authorizedOptions[$key]['setterFormatMethod'];
            $value = $this->$method($value);
        }

        $this->db->query("UPDATE `options` SET `value` = ? WHERE `key` = ?", array($value, $key));
    }

    /**
     * @param $value
     * @return mixed
     * @throws CentreonClapiException
     */
    private function getTimezoneIdFromName($value)
    {
        $timezone = new \Centreon_Object_Timezone($this->dependencyInjector);
        $timezoneId = $timezone->getIdByParameter('timezone_name', $value);
        if (!isset($timezoneId[0])) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND);
        }
        return $timezoneId[0];
    }

    /**
     * @param $value
     * @return mixed
     * @throws CentreonClapiException
     */
    private function getTimezonenameFromId($value)
    {
        $timezone = new \Centreon_Object_Timezone($this->dependencyInjector);
        $timezoneName = $timezone->getParameters($value, array('timezone_name'));
        if (!isset($timezoneName['timezone_name'])) {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND);
        }
        return $timezoneName['timezone_name'];
    }
}
