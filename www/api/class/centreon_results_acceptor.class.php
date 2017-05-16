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
require_once _CENTREON_PATH_ . "/www/class/centreonHost.class.php";


class CentreonResultsAcceptor extends CentreonConfigurationObjects
{
    /**
     *
     * @var type
     */
    protected $pearDBMonitoring;
    protected $centcore_file;
    protected $pollers;
    protected $pipeOpened;
    protected $fh;
    protected $pearDBC;
    protected $pollerHosts;
    protected $hostServices;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->centcore_file = _CENTREON_VARLIB_ . '/centcore.cmd';
        $this->pearDBC = new CentreonDB('centstorage');
        $this->getPollers();
        $this->pipeOpened = 0;
    }

    /*
     * Get poller Listing 
     */
    private function getPollers()
    {
        if (!isset($this->hostServices)) {
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

    private function getHostServiceInfo()
    {
        if (!isset($this->hostServices)) {
            $query = 'SELECT host_name, service_description ' .
                'FROM host h, service s, host_service_relation hs ' .
                'WHERE h.host_id = hs.host_host_id ' .
                'AND s.service_id = hs.service_service_id ' .
                'AND s.service_activate = "1" ' .
                'AND s.service_activate = "1" ' .
                'AND h.host_activate = "1" ' .
                'AND h.host_register = "1" ';
            $dbResult = $this->pearDB->query($query);
            $this->hostServices = array();
            while ($row = $dbResult->fetchRow()) {
                if (!isset($this->hostServices[$row['host_name']])) {
                    $this->hostServices[$row['host_name']] = array();
                }
                $this->hostServices[$row['host_name']][$row['service_description']] = 1;
            }
            $dbResult->free();
        }
    }

    private function openPipe()
    {
        if ($this->fh = @fopen($this->centcore_file, 'a+')) {
            $this->pipeOpened = 1;
        } else {
            throw new RestBadRequestException("Can't open centcore pipe");
        }
    }

    private function closePipe()
    {
        fclose($this->fh);
        $this->pipeOpened = 0;
    }

    private function writeInPipe($string)
    {
        if ($this->pipeOpened == 0) {
            throw new RestBadRequestException("Can't write results because pipe is closed");
        }

        if ($string != '') {
            fwrite($this->fh, $string . "\n");
        }
    }

    private function sendResults($data)
    {
        if (!isset($this->pollerHosts['name'][$data["host"]])) {
            throw new RestBadRequestException("Can't find poller_id for host: " . $data["host"]);
        }
        if (isset($data['service']) && $data['service'] == '') {
            /* Services update */
            $command = $data["host"] . ";" . $data["service"] . ";" . $data["status"] . ";" .
                $data["output"] . "|" . $data["perfdata"];
            $this->writeInPipe("EXTERNALCMD:" . $this->pollerHosts['name'][$data["host"]] .
                ":[" . $data['updatetime'] . "] PROCESS_HOST_CHECK_RESULT;" . $command);
        } else {
            /* Host Update */
            $command = $data["host"] . ";" . $data["status"] . ";" . $data["output"] . "|" . $data["perfdata"];
            $this->writeInPipe("EXTERNALCMD:" . $this->pollerHosts['name'][$data["host"]] .
                ":[" . $data['updatetime'] . "] PROCESS_SERVICE_CHECK_RESULT;" . $command);
        }
    }

    /**
     *
     * @return array
     */
    public function postSubmit()
    {
        //print_r($this->arguments);

        $this->getHostServiceInfo();

        if (isset($this->arguments['results']) && is_array($this->arguments['results'])) {
            if (count($this->arguments['results'])) {
                if ($this->pipeOpened == 0) {
                    $this->openPipe();
                }
                foreach ($this->arguments['results'] as $data) {
                    if (
                        !isset($this->hostServices[$data['host']])
                        || !isset($this->hostServices[$data['host']][$data["service"]])
                    ) {
                        if (!isset($this->pollerHosts['name'][$data['host']])) {
                            $host = new CentreonHost($this->pearDB);
                            $ret = array(
                                'host_name' => $data['host'],
                                'host_alias' => "Passif host - " . $data['host'],
                                'host_address' => $data['host'],
                                'host_active_checks_enabled' => array('host_active_checks_enabled', 0),
                                'host_passive_checks_enabled' => array('host_passive_checks_enabled' => 1),
                                'host_retry_check_interval' => 1,
                                'host_max_check_attempts' => 3,
                                'host_register' => 1,
                                'host_activate' => array('host_activate' => 1),
                                'host_comment' => "Host imported by rest API at " . date("Y/m/d") . ""
                            );
                            $host_id = $host->insert($ret);
                            $host->insertExtendedInfos(array('host_id' => $host_id));
                            $host->setPollerInstance($host_id, 1);

                            /* update reference table */
                            $this->hostServices[$data['host']] = array();
                        }
                        if (!isset($this->hostServices[$data['host']][$data["service"]])) {
                            if (!isset($host)) {
                                $host = new CentreonHost($this->pearDB);
                            }
                            $service = new CentreonService($this->pearDB);
                            $ret = array(
                                'service_description' => $data["service"],
                                'service_max_check_attempts' => 3,
                                'service_template_model_stm_id' => 1,
                                'service_normal_check_interval' => $data['interval'],
                                'service_retry_check_interval' => $data['interval'],
                                'service_active_checks_enabled' => array('service_active_checks_enabled' => 0),
                                'service_passive_checks_enabled' => array('service_passive_checks_enabled' => 1),
                                'service_register' => 1,
                                'service_activate' => array('service_activate' => 1),
                                'service_comment' => "Service imported by Rest API at " . date("Y/m/d") . ""
                            );
                            $service_id = $service->insert($ret);
                            if (!isset($host_id)) {
                                $host_id = $host->getHostId($data["host"]);
                            }
                            $service->insertExtendInfo(array('service_service_id' => $service_id));
                            $host->insertRelHostService($host_id, $service_id);
                        }
                    }
                    if (isset($this->pollerHosts['name'][$data['host']])) {
                        $this->sendResults($data);
                    } else {
                        throw new RestException(
                            "Can't find the pushed resource (" . $data['host'] . " / " . $data['service'] .
                            ")... Try again later"
                        );
                    }
                }
                $this->closePipe();
            }
            return (array('success' => true));
        } else {
            throw new RestBadRequestException('Bad arguments - Cannot find command list');
        }
    }
}
