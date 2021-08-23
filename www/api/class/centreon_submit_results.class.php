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
require_once dirname(__FILE__) . "/webService.class.php";

/**
 * Class for provide the webservice for submit a status for a service or host
 */
class CentreonSubmitResults extends CentreonWebService
{
    /**
     * @var string The path to the centcore pipe file
     */
    protected $centcoreFile;
    /**
     * @var boolean If the file pipe is open
     */
    protected $pipeOpened = false;
    /**
     * @var mixed The file descriptor of the centcore pipe
     */
    protected $fh;
    /**
     * @var CentreonDB The database connection to centreon_storage (realtime database)
     */
    protected $pearDBC;
    /**
     * @var array The cache for relation between hosts and pollers
     */
    protected $pollerHosts = array();
    /**
     * @var array The cache for relation between hosts and services
     */
    protected $hostServices;
    /**
     * @var array The list of accepted status
     */
    protected $acceptedStatus = array(
        'host' => array(
            0,
            1,
            2,
            'up',
            'down',
            'unknown'
        ),
        'service' => array(
            0,
            1,
            2,
            3,
            'ok',
            'warning',
            'critical',
            'unknown'
        )
    );
    /**
     * @var array The match between status string and number
     */
    protected $convertStatus = array(
        'host' => array(
            'up' => 0,
            'down' => 1,
            'unknown' => 2
        ),
        'service' => array(
            'ok' => 0,
            'warning' => 1,
            'critical' => 2,
            'unknown' => 3
        )
    );
    /**
     * @var string The rejex for validate perfdata
     */
    protected $perfDataRegex = "/(('([^'=]+)'|([^'= ]+))=[0-9\.-]+[a-zA-Z%\/]*(;[0-9\.-]*){0,4}[ ]?)+/";

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->centcoreFile = _CENTREON_VARLIB_ . '/centcore.cmd';
        $this->pearDBC = new CentreonDB('centstorage');
        $this->getPollers();
    }

    /**
     * Load the cache for pollers/hosts
     */
    private function getPollers()
    {
        if (!isset($this->pollerHosts) || count($this->pollerHosts) === 0) {
            $query = 'SELECT h.host_id, h.host_name, ns.nagios_server_id AS poller_id ' .
                'FROM host h, ns_host_relation ns ' .
                'WHERE host_host_id = host_id ' .
                'AND h.host_activate = "1" ' .
                'AND h.host_register = "1"';
            $dbResult = $this->pearDB->query($query);
            $this->pollerHosts = array('name' => array(), 'id' => array());
            while ($row = $dbResult->fetchRow()) {
                $this->pollerHosts['id'][$row['host_id']] = $row['poller_id'];
                $this->pollerHosts['name'][$row['host_name']] = $row['poller_id'];
            }
            $dbResult->free();
        }
    }

    /**
     * Load the cache for hosts/services
     */
    private function getHostServiceInfo()
    {
        if (!isset($this->hostServices)) {
            $query = "SELECT name, description " .
                "FROM hosts h, services s " .
                "WHERE h.host_id = s.host_id " .
                    "AND h.enabled = 1 " .
                    "AND s.enabled = 1 ";
            $dbResult = $this->pearDBC->query($query);
            $this->hostServices = array();
            while ($row = $dbResult->fetchRow()) {
                if (!isset($this->hostServices[$row['name']])) {
                    $this->hostServices[$row['name']] = array();
                }
                $this->hostServices[$row['name']][$row['description']] = 1;
            }
            $dbResult->free();
        }
    }

    /**
     * Open the centcore pipe file
     */
    private function openPipe()
    {
        if ($this->fh = @fopen($this->centcoreFile, 'a+')) {
            $this->pipeOpened = true;
        } else {
            throw new RestBadRequestException("Can't open centcore pipe");
        }
    }

    /**
     * Close the centcore pipe file
     */
    private function closePipe()
    {
        fclose($this->fh);
        $this->pipeOpened = false;
    }

    /**
     * Write into the centcore pipe filr
     */
    private function writeInPipe($string)
    {
        if ($this->pipeOpened === false) {
            return false;
        }

        if ($string != '') {
            fwrite($this->fh, $string . "\n");
        }
        return true;
    }

    /**
     * Send the data to CentCore
     */
    private function sendResults($data)
    {
        if (!isset($this->pollerHosts['name'][$data["host"]])) {
            throw new RestBadRequestException("Can't find poller_id for host: " . $data["host"]);
        }

        if (isset($data['service']) && $data['service'] !== '') {
            /* Services update */
            $command = $data["host"] . ";" . $data["service"] . ";" . $data["status"] . ";" .
                $data["output"] . "|" . $data["perfdata"];
            /* send data */
            return $this->writeInPipe("EXTERNALCMD:" . $this->pollerHosts['name'][$data["host"]] .
                ":[" . $data['updatetime'] . "] PROCESS_SERVICE_CHECK_RESULT;" . $command);
        } else {
            /* Host Update */
            $command = $data["host"] . ";" . $data["status"] . ";" . $data["output"] . "|" . $data["perfdata"];
            /* send data */
            return $this->writeInPipe("EXTERNALCMD:" . $this->pollerHosts['name'][$data["host"]] .
                ":[" . $data['updatetime'] . "] PROCESS_HOST_CHECK_RESULT;" . $command);
        }
    }

    /**
     * Entry point for submit a passive check result
     */
    public function postSubmit()
    {
        $this->getHostServiceInfo();

        $results = array();
        $hasError = false;

        if (isset($this->arguments['results']) && is_array($this->arguments['results'])) {
            if (count($this->arguments['results'])) {
                if ($this->pipeOpened === false) {
                    $this->openPipe();
                }
                foreach ($this->arguments['results'] as $data) {
                    try {
                        /* Validate the list of arguments */
                        /* Required fields */
                        if (!isset($data['host']) || $data['host'] === '' ||
                            !isset($data['status']) || !isset($data['updatetime']) || !isset($data['output'])) {
                            throw new RestBadRequestException('Missing argument.');
                        }

                        /* Validate is the host and service exists in poller */
                        if (!isset($this->pollerHosts['name'][$data['host']])) {
                            throw new RestNotFoundException('The host is not present.');
                        }
                        if (isset($data['service']) && $data['service'] !== '' &&
                            !$this->hostServices[$data['host']][$data["service"]]) {
                            throw new RestNotFoundException('The service is not present.');
                        }

                        /* Validate status format */
                        $status = strtolower($data['status']);
                        if (is_numeric($status)) {
                            $status = (int)$status;
                        }
                        if (isset($data['service']) && $data['service'] !== '') {
                            if (!in_array($status, $this->acceptedStatus['service'], true)) {
                                throw new RestBadRequestException('Bad status word.');
                            }
                            if (!is_numeric($status)) {
                                $status = $this->convertStatus['service'][$status];
                            }
                        } else {
                            if (!in_array($status, $this->acceptedStatus['host'], true)) {
                                throw new RestBadRequestException('Bad status word.');
                            }
                            if (!is_numeric($status)) {
                                $status = $this->convertStatus['host'][$status];
                            }
                        }
                        $data['status'] = $status;

                        /* Validate timestamp format */
                        if (!is_numeric($data['updatetime'])) {
                            throw new RestBadRequestException('The timestamp is not a integer.');
                        }

                        if (isset($data['perfdata'])) {
                            if ($data['perfdata'] !== '' && !preg_match($this->perfDataRegex, $data['perfdata'])) {
                                throw new RestBadRequestException('The format of performance data is not valid.');
                            }
                        } else {
                            $data['perfdata'] = '';
                        }

                        /* Execute the command */
                        if (!$this->sendResults($data)) {
                            throw new RestInternalServerErrorException('Error during send command to CentCore.');
                        }
                        $results[] = array(
                            'code' => 202,
                            'message' => 'The status send to the engine'
                        );
                    } catch (\Exception $error) {
                        $hasError = true;
                        $results[] = array(
                            'code' => $error->getCode(),
                            'message' => $error->getMessage()
                        );
                    }
                }
                $this->closePipe();
            }
            if ($hasError) {
                throw new RestPartialContent(json_encode(array('results' => $results)));
            }
            return array('results' => $results);
        } else {
            throw new RestBadRequestException('Bad arguments - Cannot find result list');
        }
    }

    /**
     * Authorize to access to the action
     *
     * @param string $action The action name
     * @param array $user The current user
     * @param boolean $isInternal If the api is call in internal
     * @return boolean If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false)
    {
        if (
            parent::authorize($action, $user, $isInternal)
            || ($user && $user->hasAccessRestApiRealtime())
        ) {
            return true;
        }

        return false;
    }
}
