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
    public function __construct()
    {
        parent::__construct();
        $this->authorizedOptions = array('broker'                       => array('ndo', 'broker'),
                                         'centstorage'                  => array('0', '1'),
                                         'enable_perfdata_sync'         => array('0', '1'),
                                         'enable_logs_sync'             => array('0', '1'),
                                         'gmt'                          => ISNUM,
                                         'mailer_path_bin'              => ISSTRING,
                                         'snmptt_unknowntrap_log_file'  => ISSTRING,
                                         'snmpttconvertmib_path_bin'    => ISSTRING,
                                         'perl_library_path'            => ISSTRING,
                                         'rrdtool_path_bin'             => ISSTRING,
                                         'debug_path'                   => ISSTRING,
                                         'debug_auth'                   => array('0', '1'),
                                         'debug_nagios_import'          => array('0', '1'),
                                         'debug_rrdtool'                => array('0', '1'),
                                         'debug_ldap_import'            => array('0', '1'),
                                         'enable_autologin'             => array('0', '1'),
                                         'interval_length'              => ISNUM,
                                         'enable_gmt'                   => array('0', '1'),
                                         'nagios_path_img'              => ISSTRING,
                                         'broker_correlator_script'     => ISSTRING,
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
     * Display all editable options
     *
     * @param string $parameters
     */
    public function show($filter = null)
    {
        $sql = "SELECT `key`, `value` FROM `options` ORDER BY `key`";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetchAll();
        echo "parameter".$this->delim."value\n";
        foreach ($res as $row) {
            if (isset($this->authorizedOptions[$row['key']])) {
                echo $row['key'].$this->delim.$row['value']."\n";
            }
        }
    }

    /**
     * Add method is disabled
     *
     * @return void
     */
    public function add()
    {
        $this->unsupportedMethod(__FUNCTION__);
    }

    /**
     * Del method is disabled
     *
     * @return void
     */
    public function del()
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
        if (is_array($this->authorizedOptions[$key]) && !in_array($value, $this->authorizedOptions[$key])) {
            throw new CentreonClapiException(self::VALUENOTALLOWED);
        } elseif ($this->authorizedOptions[$key] == ISNUM && !is_numeric($value)) {
            throw new CentreonClapiException(self::VALUENOTALLOWED);
        }
        $this->db->query("UPDATE `options` SET `value` = ? WHERE `key` = ?", array($value, $key));
    }
}
