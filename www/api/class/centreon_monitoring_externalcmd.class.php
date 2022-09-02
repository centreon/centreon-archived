<?php

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
require_once _CENTREON_PATH_ . '/www/class/centreonExternalCommand.class.php';
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
        $this->pearDBMonitoring = new \CentreonDB('centstorage');
        $this->centcore_file = _CENTREON_VARLIB_ . '/centcore.cmd';
    }

    /**
     * @return array
     * @throws RestBadRequestException
     * @throws RestException
     */
    public function postSend()
    {
        global $centreon;

        if (
            isset($this->arguments['commands'])
            && is_array($this->arguments['commands'])
            && count($this->arguments['commands'])
        ) {
            /* Get poller Listing */
            $query = 'SELECT id ' .
                'FROM nagios_server ' .
                'WHERE ns_activate = "1"';

            $dbResult = $this->pearDB->query($query);
            $pollers = array();

            while ($row = $dbResult->fetch(PDO::FETCH_ASSOC)) {
                $pollers[$row['id']] = 1;
            }

            $externalCommand = new CentreonExternalCommand();
            $availableCommands = array();

            /**
             * We need to make the concordance between the data saved in the database
             * and the action provided by the user.
             */
            foreach ($externalCommand->getExternalCommandList() as $key => $cmd) {
                foreach ($cmd as $c) {
                    $availableCommands[$c] = $key;
                }
            }

            $isAdmin = $centreon->user->admin;

            /**
             * If user is not admin we need to retrieve its ACL
             */
            if (!$isAdmin) {
                $userAcl = new CentreonACL($centreon->user->user_id, $isAdmin);
            }

            if ($fh = @fopen($this->centcore_file, 'a+')) {
                foreach ($this->arguments['commands'] as $command) {
                    $commandSplitted = explode(';', $command['command']);
                    $action = $commandSplitted[0];
                    if (!$isAdmin) {
                        if (preg_match('/HOST(_SVC)?/', $action, $matches)) {
                            if (!isset($commandSplitted[1])) {
                                throw new RestBadRequestException(_('Host not found'));
                            }
                            $query = 'SELECT acl.host_id
                                FROM centreon_acl acl, hosts h
                                WHERE acl.host_id = h.host_id
                                AND acl.service_id IS NULL
                                AND h.name = ?
                                AND acl.group_id IN (' . $userAcl->getAccessGroupsString() . ')';
                            $result = $this->pearDBMonitoring->query($query, array($commandSplitted[1]));
                            if ($result->fetch() === false) {
                                throw new RestBadRequestException(_('Host not found'));
                            }
                        } elseif (preg_match('/(?!HOST_)SVC/', $action, $matches)) {
                            if (!isset($commandSplitted[1]) || !isset($commandSplitted[2])) {
                                throw new RestBadRequestException(_('Service not found'));
                            }
                            $query = 'SELECT acl.service_id
                                FROM centreon_acl acl, hosts h, services s
                                WHERE h.host_id = s.host_id
                                AND acl.host_id = s.host_id
                                AND acl.service_id = s.service_id
                                AND h.name = :hostName
                                AND s.description = :serviceDescription
                                AND acl.group_id IN (' . $userAcl->getAccessGroupsString() . ')';

                            $statement = $this->pearDBMonitoring->prepare($query);
                            $statement->bindValue(':hostName', $commandSplitted[1], PDO::PARAM_STR);
                            $statement->bindValue(':serviceDescription', $commandSplitted[2], PDO::PARAM_STR);
                            $statement->execute();
                            if ($statement->fetch() === false) {
                                throw new RestBadRequestException(_('Service not found'));
                            }
                        }
                    }

                    // checking that action provided exists
                    if (!array_key_exists($action, $availableCommands)) {
                        throw new RestBadRequestException('Action ' . $action . ' not supported');
                    }

                    if (!$isAdmin) {
                        // Checking that the user has rights to do the action provided
                        if ($userAcl->checkAction($availableCommands[$action]) === 0) {
                            throw new RestUnauthorizedException(
                                'User is not allowed to execute ' . $action . ' action'
                            );
                        }
                    }

                    if (isset($pollers[$command['poller_id']])) {
                        fwrite(
                            $fh,
                            "EXTERNALCMD:" . $command["poller_id"] . ":[" .
                            $command['timestamp'] . "] " . $command['command'] . "\n"
                        );
                    }
                }
                fclose($fh);
                return (array('success' => true));
            } else {
                throw new RestException('Cannot open Centcore file');
            }
        } else {
            throw new RestBadRequestException('Bad arguments - Cannot find command list');
        }
    }
}
