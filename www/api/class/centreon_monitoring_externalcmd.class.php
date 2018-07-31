<?php
/*
 * Copyright 2005-2015 Centreon
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

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

class CentreonMonitoringExternalcmd extends CentreonConfigurationObjects
{
    /**
     *
     * @var type
     */
    protected $pearDBMonitoring;
    protected $centcore_file;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->centcore_file = _CENTREON_VARLIB_ . '/centcore.cmd';
    }

    /**
     * @return array
     * @throws RestBadRequestException
     * @throws RestException
     */
    public function postSend()
    {
        if (isset($this->arguments['commands']) && is_array($this->arguments['commands'])) {
            /* Get poller Listing */
            $query = 'SELECT id ' .
                'FROM nagios_server ' .
                'WHERE ns_activate = "1"';

            $dbResult = $this->pearDB->query($query);
            $pollers = array();
            while ($row = $dbResult->fetchRow()) {
                $pollers[$row['id']] = 1;
            }

            if (count($this->arguments['commands'])) {
                if ($fh = @fopen($this->centcore_file, 'a+')) {
                    foreach ($this->arguments['commands'] as $command) {
                        if (isset($pollers[$command['poller_id']])) {
                            fwrite($fh,
                                "EXTERNALCMD:" . $command["poller_id"] . ":[" .
                                $command['timestamp'] . "] " . $command['command'] . "\n"
                            );
                        } else {
                            throw new RestException('Cannot open Centcore file');
                        }
                    }
                    fclose($fh);
                }
            }
            return (array('success' => true));
        } else {
            throw new RestBadRequestException('Bad arguments - Cannot find command list');
        }
    }
}
