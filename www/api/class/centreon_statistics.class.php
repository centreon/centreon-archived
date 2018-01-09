<?php
/*
 * Copyright 2005-2018 Centreon
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
 * this program; if not, see <htcommand://www.gnu.org/licenses>.
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
 * For more information : command@centreon.com
 *
 */

require_once dirname(__FILE__) . "/webService.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonUUID.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonGMT.class.php";

class CentreonStatistics extends CentreonWebService
{
    /**
     * get Centreon UUID
     *
     * @return array
     */
    public function getCentreonUUID()
    {
        $centreonUUID = new CentreonUUID($this->pearDB);
        return array(
            'CentreonUUID' => $centreonUUID->getUUID()
        );
    }

    /**
     * get Centreon information
     *
     * @return array
     */
    public function getPlatformInfo()
    {
        $query = "SELECT COUNT(host_id) as nbHost, " .
            "(SELECT COUNT(service_id) FROM service " .
            "WHERE service_activate = 1 AND service_register = 1) as nbService, " .
            "(SELECT COUNT(id) FROM nagios_server WHERE ns_activate = 1) as nbPoller " .
            "FROM host WHERE host_activate = 1 AND host_register = 1";
        $dbResult = $this->pearDB->query($query);
        $data = $dbResult->fetchRow();

        return $data;
    }

    /**
     * get version of Centreon Web
     *
     * @return array
     */
    public function getVersion()
    {
        $query = 'SELECT i.value as "centreon-web" FROM informations i ' .
                'WHERE i.key = "version"';
        $dbResult = $this->pearDB->query($query);
        $data = $dbResult->fetchRow();

        return $data;
    }

    /**
     * get Centreon timezone
     *
     * @return array
     */
    public function getPlatformTimezone()
    {
        $oTimezone = new CentreonGMT($this->pearDB);
        $sDefaultTimezone = $oTimezone->getCentreonTimezone();

        if (empty($sDefaultTimezone)) {
            $sDefaultTimezone = date_default_timezone_get();
        }

        return array(
            'timezone' => $sDefaultTimezone
        );
    }




}
