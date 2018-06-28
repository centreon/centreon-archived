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

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once dirname(__FILE__) . "/webService.class.php";

class CentreonTopCounter extends CentreonWebService
{
    /**
     * @var CentreonDB
     */
    protected $pearDBMonitoring;

    /**
     * @var int
     */
    protected $timeUnit = 300;

    /**
     * @var int
     */
    protected $refreshTime = 15;

    protected $hasAccessToTopCounter = false;

    protected $hasAccessToPollers = false;

    protected $hasAccessToProfile = false;

    protected $soundNotificationsEnabled = false;

    protected $centreonUser;

    /**
     * CentreonTopCounter constructor.
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->pearDBMonitoring = new CentreonDB('centstorage');
        /* Get the refresh time for top counter */
        $query = 'SELECT `value` FROM options WHERE `key` = "AjaxTimeReloadStatistic"';
        $res = $this->pearDB->query($query);
        if (!PEAR::isError($res) && $res->numRows() > 0) {
            $row = $res->fetchRow();
            $this->refreshTime = (int)$row['value'];
        }

        $this->checkAccess();
    }

    /**
     * @throws RestUnauthorizedException
     */
    private function checkAccess()
    {
        if (!isset($_SESSION['centreon'])) {
            throw new \RestUnauthorizedException('Session does not exists.');
        }
        $this->centreonUser = $_SESSION['centreon']->user;

        if ($this->centreonUser->access->admin == 0) {
            $tabActionACL = $this->centreonUser->access->getActions();
            if (isset($tabActionACL["top_counter"])) {
                $this->hasAccessToTopCounter = true;
            }
            if (isset($tabActionACL["poller_stats"])) {
                $this->hasAccessToPollers = true;
            }
        } else {
            $this->hasAccessToTopCounter = true;
            $this->hasAccessToPollers = true;
        }

        if ($this->centreonUser->topology['50104'] == '1') {
            $this->hasAccessToProfile = true;
        }
    }

    /**
     * The current time of the server
     *
     * Method GET
     */
    public function getClock()
    {
        if (!isset($_SESSION['centreon'])) {
            throw new \RestUnauthorizedException('Session does not exists.');
        }
        $user = $_SESSION['centreon']->user;
        $gmt = $_SESSION['centreon']->CentreonGMT;

        $locale = $user->lang === 'browser' ? null : $user->lang;

        return array(
            'time' => time(),
            'locale' => $locale,
            'timezone' => $gmt->getActiveTimezone($user->gmt)
        );
    }

    /**
     * If the user must be disconnected
     *
     * Method GET
     */
    public function getAutologout()
    {
        $logout = true;
        if (isset($_SESSION['centreon'])) {
            $query = $this->pearDB->prepare('SELECT user_id FROM session WHERE session_id = ?');
            $res = $this->pearDB->execute($query, array(session_id()));
            if ($res->numRows()) {
                $logout = false;
            }
        }

        return array(
            'autologout' => $logout
        );
    }

    /**
     * Get the user information
     *
     * Method PUT
     */
    public function putAutoLoginToken()
    {
        $userId = $this->arguments['userId'];
        $autoLoginKey = $this->arguments['token'];

        global $centreon;

        $query = "UPDATE contact SET contact_autologin_key = ? " .
            "WHERE contact_id = ?";

        $stmt = $this->pearDB->prepare($query);
        $res = $this->pearDB->execute($stmt, array($autoLoginKey, $userId));

        if (PEAR::isError($res)) {
            throw new \Exception('Error while update autologinKey ' . $autoLoginKey);
        }

        /**
         * Update user object
         */
        $centreon->user->setToken($autoLoginKey);
    }

    /**
     * Get the user information
     *
     * Method GET
     */
    public function getUser()
    {
        if (!isset($_SESSION['centreon'])) {
            throw new \RestUnauthorizedException('Session does not exists.');
        }
        $user = $_SESSION['centreon']->user;

        $locale = $user->lang === 'browser' ? null : $user->lang;

        if (isset($_SESSION['disable_sound'])) {
            $this->soundNotificationsEnabled = !$_SESSION['disable_sound'];
        } else {
            $this->soundNotificationsEnabled = true;
        }

        /* Get autologinkey */
        $query = 'SELECT contact_autologin_key FROM contact WHERE contact_id = ' . (int)$user->user_id;
        $res = $this->pearDB->query($query);
        if (PEAR::isError($res)) {
            throw new \RestInternalServerErrorException('Error getting the user.');
        }
        if ($res->numRows() === 0) {
            throw new \RestUnauthorizedException('User does not exists.');
        }
        $row = $res->fetchRow();

        return array(
            'userId' => $user->user_id,
            'fullname' => $user->name,
            'username' => $user->alias,
            'locale' => $locale,
            'timezone' => $user->gmt,
            'hasAccessToProfile' => $this->hasAccessToProfile,
            'autologinkey' => $row['contact_autologin_key'],
            'soundNotificationsEnabled' => $this->soundNotificationsEnabled
        );
    }

    /**
     * Get the pollers status
     *
     * Method GET
     */
    public function getPollersStatus()
    {
        if (!$this->hasAccessToPollers) {
            throw new \RestUnauthorizedException("You're not authorized to access poller datas");
        }

        $pollers = $this->pollersStatusList();
        $result = array(
            'latency' => array(
                'warning' => 0,
                'critical' => 0
            ),
            'stability' => array(
                'warning' => 0,
                'critical' => 0
            ),
            'database' => array(
                'warning' => 0,
                'critical' => 0
            ),
            'total' => count($pollers),
            'refreshTime' => $this->refreshTime
        );

        foreach ($pollers as $poller) {
            if ($poller['stability'] === 1) {
                $result['stability']['warning']++;
            } elseif ($poller['stability'] === 2) {
                $result['stability']['critical']++;
            }
            if ($poller['database']['state'] === 1) {
                $result['database']['warning']++;
            } elseif ($poller['database']['state'] === 2) {
                $result['database']['critical']++;
            }
            if ($poller['latency']['state'] === 1) {
                $result['latency']['warning']++;
            } elseif ($poller['latency']['state'] === 2) {
                $result['latency']['critical']++;
            }
        }

        return $result;
    }


    /**
     * Get the list of pollers by status type
     *
     * Method GET
     */
    public function getPollers()
    {
        $listType = array('configuration', 'stability', 'database', 'latency');
        if (!isset($this->arguments['type']) || !in_array($this->arguments['type'], $listType)) {
            throw new \RestBadRequestException('Missing type argument or bad type name.');
        }

        $result = array(
            'type' => $this->arguments['type'],
            'pollers' => array(),
            'total' => 0,
            'refreshTime' => $this->refreshTime
        );

        if ($this->arguments['type'] === 'configuration') {
            $pollers = $this->pollersList();
            $result['total'] = count($pollers);
            foreach ($pollers as $poller) {
                if ($this->checkChangeState($poller['id'], $poller['lastRestart'])) {
                    $result['pollers'][] = array(
                        'id' => $poller['id'],
                        'name' => $poller['name'],
                        'status' => 1,
                        'information' => ''
                    );
                }
            }
        } else {
            $type = $this->arguments['type'];
            $pollers = $this->pollersStatusList();
            foreach ($pollers as $poller) {
                $state = 0;
                $info = '';
                if ($type === 'stability') {
                    $state = $poller['stability'];
                } else {
                    $state = $poller[$type]['state'];
                    $info = $poller[$type]['time'];
                }
                if ($state > 0) {
                    $result['pollers'][] = array(
                        'id' => $poller['id'],
                        'name' => $poller['name'],
                        'status' => $state,
                        'information' => $info
                    );
                }
            }
            $result['total'] = count($pollers);
        }

        return $result;
    }

    /**
     * Get the list of pollers with problems
     *
     * Method GET
     */
    public function getPollersListIssues()
    {
        if (!$this->hasAccessToPollers) {
            throw new \RestUnauthorizedException("You're not authorized to access poller datas");
        }

        $pollers = $this->pollersStatusList();
        $result = array(
            'latency' => array(
                'warning' => array(
                    'poller' => array(),
                    'total' => 0
                ),
                'critical' => array(
                    'poller' => array(),
                    'total' => 0
                ),
                'total' => 0
            ),
            'stability' => array(
                'warning' => array(
                    'poller' => array(),
                    'total' => 0
                ),
                'critical' => array(
                    'poller' => array(),
                    'total' => 0
                ),
                'total' => 0
            ),
            'database' => array(
                'warning' => array(
                    'poller' => array(),
                    'total' => 0
                ),
                'critical' => array(
                    'poller' => array(),
                    'total' => 0
                ),
                'total' => 0
            ),
            'total' => count($pollers),
            'refreshTime' => $this->refreshTime
        );

        $staWar = $staCri = $datWar = $datCri = $latWar = $latCri = 0;

        foreach ($pollers as $poller) {
            //stability
            if ($poller['stability'] === 1) {
                $result['stability']['warning']['poller'][] = array(
                    'id' => $poller['id'],
                    'name' => $poller['name'],
                    'freetime' => ''
                );
                $staWar++;
            } elseif ($poller['stability'] === 2) {
                $result['stability']['critical']['poller'][] = array(
                    'id' => $poller['id'],
                    'name' => $poller['name'],
                    'freetime' => ''
                );
                $staCri++;
            }

            //database
            if ($poller['database']['state'] === 1) {
                $result['database']['warning']['poller'][] = array(
                    'id' => $poller['id'],
                    'name' => $poller['name'],
                    'freetime' => $poller['database']['time']
                );
                $datWar++;
            } elseif ($poller['database']['state'] === 2) {
                $result['database']['critical']['poller'][] = array(
                    'id' => $poller['id'],
                    'name' => $poller['name'],
                    'freetime' => $poller['database']['time']
                );
                $datCri++;
            }

            //latency
            if ($poller['latency']['state'] === 1) {
                $result['latency']['warning']['poller'][] = array(
                    'id' => $poller['id'],
                    'name' => $poller['name'],
                    'freetime' => $poller['warning']['time']
                );
                $latWar++;
            } elseif ($poller['latency']['state'] === 2) {
                $result['latency']['critical']['poller'][] = array(
                    'id' => $poller['id'],
                    'name' => $poller['name'],
                    'freetime' => $poller['warning']['time']
                );
                $latCri++;
            }
        }

        //total and unset empty
        $staTotal = $staWar + $staCri;
        if ($staTotal === 0) {
            unset($result['stability']);
        } else {
            if ($staWar === 0) {
                unset($result['stability']['warning']);
                $result['stability']['critical']['total'] = $staCri;
            } elseif ($staCri === 0) {
                unset($result['stability']['critical']);
                $result['stability']['warning']['total'] = $staWar;
            } else {
                $result['stability']['warning']['total'] = $staWar;
                $result['stability']['critical']['total'] = $staCri;
            }
            $result['stability']['total'] = $staTotal;
        }

        $datTotal = $datWar + $datCri;
        if ($datTotal === 0) {
            unset($result['database']);
        } else {
            if ($datWar === 0) {
                unset($result['database']['warning']);
                $result['database']['critical']['total'] = $datCri;
            } elseif ($datCri === 0) {
                unset($result['database']['critical']);
                $result['database']['warning']['total'] = $datWar;
            } else {
                $result['database']['warning']['total'] = $datWar;
                $result['database']['critical']['total'] = $datCri;
            }
            $result['database']['total'] = $datTotal;
        }

        $latTotal = $latWar + $latCri;
        if ($latTotal === 0) {
            unset($result['latency']);
        } else {
            if ($latWar === 0) {
                unset($result['latency']['warning']);
                $result['latency']['critical']['total'] = $latCri;
            } elseif ($latCri === 0) {
                unset($result['latency']['critical']);
                $result['latency']['warning']['total'] = $latWar;
            } else {
                $result['latency']['warning']['total'] = $latWar;
                $result['latency']['critical']['total'] = $latCri;
            }
            $result['latency']['total'] = $latTotal;
        }

        return $result;
    }

    /**
     * Get the hosts status
     *
     * Method GET
     */
    public function getHosts_status()
    {
        if (!$this->hasAccessToTopCounter) {
            throw new \RestUnauthorizedException("You're not authorized to access resource datas");
        }

        $query = 'SELECT
            SUM(CASE WHEN h.state = 0 THEN 1 ELSE 0 END) AS up_total,
            SUM(CASE WHEN h.state = 1 THEN 1 ELSE 0 END) AS down_total,
            SUM(CASE WHEN h.state = 2 THEN 1 ELSE 0 END) AS unreachable_total,
            SUM(CASE WHEN h.state = 4 THEN 1 ELSE 0 END) AS pending_total,
            SUM(CASE WHEN h.state = 1 AND (h.acknowledged = 0 AND h.scheduled_downtime_depth = 0)
                THEN 1 ELSE 0 END) AS down_unhandled,
            SUM(CASE WHEN h.state = 2 AND (h.acknowledged = 0 AND h.scheduled_downtime_depth = 0)
                THEN 1 ELSE 0 END) AS unreachable_unhandled
            FROM hosts h, instances i';
        $query .= ' WHERE i.deleted = 0
            AND h.instance_id = i.instance_id
            AND h.enabled = 1
            AND h.name NOT LIKE "_Module_%"';

        if (!$this->centreonUser->admin) {
            $query .= ' AND EXISTS (
                SELECT a.host_id FROM centreon_acl a
                  WHERE a.host_id = h.host_id
                    AND a.group_id IN (' . $this->centreonUser->access->getAccessGroupsString() . '))';
        }

        $res = $this->pearDBMonitoring->query($query);
        if (PEAR::isError($res)) {
            throw new \RestInternalServerErrorException();
        }
        $row = $res->fetchRow();

        $result = array(
            'down' => array(
                'total' => $row['down_total'],
                'unhandled' => $row['down_unhandled']
            ),
            'unreachable' => array(
                'total' => $row['unreachable_total'],
                'unhandled' => $row['unreachable_unhandled']
            ),
            'ok' => $row['up_total'],
            'pending' => $row['pending_total'],
            'total' => $row['up_total'] + $row['pending_total'] + $row['down_total'] + $row['unreachable_total'],
            'refreshTime' => $this->refreshTime
        );
        return $result;
    }

    /**
     * Get the services status
     *
     * Method GET
     */
    public function getServicesStatus()
    {
        if (!$this->hasAccessToTopCounter) {
            throw new \RestUnauthorizedException("You're not authorized to access resource datas");
        }

        $query = 'SELECT
            SUM(CASE WHEN s.state = 0 THEN 1 ELSE 0 END) AS ok_total,
            SUM(CASE WHEN s.state = 1 THEN 1 ELSE 0 END) AS warning_total,
            SUM(CASE WHEN s.state = 2 THEN 1 ELSE 0 END) AS critical_total,
            SUM(CASE WHEN s.state = 3 THEN 1 ELSE 0 END) AS unknown_total,
            SUM(CASE WHEN s.state = 4 THEN 1 ELSE 0 END) AS pending_total,
            SUM(CASE WHEN s.state = 1 AND (s.acknowledged = 0 AND s.scheduled_downtime_depth = 0)
                THEN 1 ELSE 0 END) AS warning_unhandled,
            SUM(CASE WHEN s.state = 2 AND (s.acknowledged = 0 AND s.scheduled_downtime_depth = 0)
                THEN 1 ELSE 0 END) AS critical_unhandled,
            SUM(CASE WHEN s.state = 3 AND (s.acknowledged = 0 AND s.scheduled_downtime_depth = 0)
                THEN 1 ELSE 0 END) AS unknown_unhandled
            FROM hosts h, services s, instances i';
        $query .= ' WHERE i.deleted = 0
            AND h.instance_id = i.instance_id
            AND h.enabled = 1
            AND (h.name NOT LIKE "_Module_%" OR h.name LIKE "_Module_Meta%")
            AND s.enabled = 1
            AND h.host_id = s.host_id';
        if (!$this->centreonUser->admin) {
            $query .= ' AND EXISTS (
                SELECT a.service_id FROM centreon_acl a
                    WHERE a.host_id = h.host_id
                        AND a.service_id = s.service_id
                        AND a.group_id IN (' . $this->centreonUser->access->getAccessGroupsString() . ')
            )';
        }
        $res = $this->pearDBMonitoring->query($query);
        if (PEAR::isError($res)) {
            throw new \RestInternalServerErrorException();
        }
        $row = $res->fetchRow();

        $result = array(
            'critical' => array(
                'total' => $row['critical_total'],
                'unhandled' => $row['critical_unhandled']
            ),
            'warning' => array(
                'total' => $row['warning_total'],
                'unhandled' => $row['warning_unhandled']
            ),
            'unknown' => array(
                'total' => $row['unknown_total'],
                'unhandled' => $row['unknown_unhandled']
            ),
            'ok' => $row['ok_total'],
            'pending' => $row['pending_total'],
            'total' => $row['ok_total'] + $row['pending_total'] + $row['critical_total'] + $row['unknown_total'] +
                $row['warning_total'],
            'refreshTime' => $this->refreshTime
        );
        return $result;
    }

    /**
     * Get the configured pollers
     */
    protected function pollersList()
    {
        if (!isset($_SESSION['centreon'])) {
            throw new \RestUnauthorizedException('Session does not exists.');
        }
        /* Get the list of configured pollers */
        $listPoller = array();
        $query = 'SELECT id, name, last_restart FROM nagios_server WHERE ns_activate = "1"';

        /* Add ACL */
        $user = $_SESSION['centreon']->user;
        $aclPoller = $user->access->getPollerString('id');
        if (!$user->admin) {
            if ($aclPoller === '') {
                return array();
            }
            $query .= ' AND id IN (' . $aclPoller . ')';
        }


        $res = $this->pearDB->query($query);
        if (PEAR::isError($res)) {
            throw new \RestInternalServerErrorException();
        }
        if ($res->numRows() === 0) {
            return array();
        }
        while ($row = $res->fetchRow()) {
            $listPoller[$row['id']] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'lastRestart' => $row['last_restart']
            );
        }
        return $listPoller;
    }

    /**
     * Get information for pollers
     */
    protected function pollersStatusList()
    {
        $listPoller = array();
        $listConfPoller = $this->pollersList();
        foreach ($listConfPoller as $poller) {
            $listPoller[$poller['id']] = array(
                'id' => $poller['id'],
                'name' => $poller['name'],
                'stability' => 0,
                'database' => array(
                    'state' => 0,
                    'time' => null
                ),
                'latency' => array(
                    'state' => 0,
                    'time' => null
                )
            );
        }

        /* Get status of pollers */
        $query = 'SELECT instance_id, last_alive, running FROM instances
            WHERE deleted = 0 AND instance_id IN (' . implode(', ', array_keys($listPoller)) . ')';
        $res = $this->pearDBMonitoring->query($query);
        if (PEAR::isError($res)) {
            throw new \RestInternalServerErrorException();
        }
        while ($row = $res->fetchRow()) {
            /* Test if poller running and activity */
            if (time() - $row['last_alive'] >= $this->timeUnit * 10) {
                $listPoller[$row['instance_id']]['stability'] = 2;
                $listPoller[$row['instance_id']]['database']['state'] = 2;
                $listPoller[$row['instance_id']]['database']['time'] = time() - $row['last_alive'];
            } elseif (time() - $row['last_alive'] >= $this->timeUnit * 5) {
                $listPoller[$row['instance_id']]['stability'] = 1;
                $listPoller[$row['instance_id']]['database']['state'] = 1;
                $listPoller[$row['instance_id']]['database']['time'] = time() - $row['last_alive'];
            }
            if ($row['running'] == 0) {
                $listPoller[$row['instance_id']]['stability'] = 2;
            }
        }
        /* Get latency */
        $query = 'SELECT n.stat_value, i.instance_id
            FROM nagios_stats n, instances i
            WHERE n.stat_label = "Service Check Latency"
                AND n.stat_key = "Average"
                AND n.instance_id = i.instance_id
                AND i.deleted = 0
                AND i.instance_id IN (' . implode(', ', array_keys($listPoller)) . ')';
        $res = $this->pearDBMonitoring->query($query);
        if (PEAR::isError($res)) {
            throw new \RestInternalServerErrorException();
        }
        while ($row = $res->fetchRow()) {
            if ($row['stat_value'] >= 120) {
                $listPoller[$row['instance_id']]['latency']['state'] = 2;
                $listPoller[$row['instance_id']]['database']['time'] = $row['stat_value'];
            } elseif ($row['stat_value'] >= 60) {
                $listPoller[$row['instance_id']]['latency']['state'] = $row['stat_value'];
            }
        }

        return $listPoller;
    }

    /**
     * Duplicate code because include doesn't work
     */
    protected function checkChangeState($pollerId, $lastRestart)
    {
        global $conf_centreon;

        if (!isset($lastRestart) || $lastRestart == "") {
            return true;
        }

        $query = "SELECT * FROM log_action WHERE action_log_date > $lastRestart " .
            "AND ((object_type = 'host' AND ((action_type = 'd' AND object_id IN (SELECT host_id FROM hosts)) " .
            "OR object_id IN (SELECT host_host_id FROM " .
            $conf_centreon['db'] . ".ns_host_relation WHERE nagios_server_id = '$pollerId'))) " .
            "OR (object_type = 'service' AND ((action_type = 'd' AND object_id IN (SELECT service_id FROM services)) OR " .
            "object_id IN (SELECT service_service_id FROM " .
            $conf_centreon['db'] . ".ns_host_relation nhr, " . $conf_centreon['db'] . ".host_service_relation hsr " .
            "WHERE nagios_server_id = '$pollerId' AND hsr.host_host_id = nhr.host_host_id)))" .
            "OR (object_type = 'servicegroup' AND ((action_type = 'd' AND object_id IN (SELECT DISTINCT servicegroup_id " .
            "FROM services_servicegroups)) OR object_id IN (SELECT DISTINCT servicegroup_sg_id FROM " .
            $conf_centreon['db'] . ".servicegroup_relation sgr, " . $conf_centreon['db'] . ".ns_host_relation nhr " .
            "WHERE sgr.host_host_id = nhr.host_host_id AND nhr.nagios_server_id = '$pollerId')))" .
            "OR (object_type = 'hostgroup' AND ((action_type = 'd' AND object_id IN (SELECT DISTINCT hostgroup_id " .
            "FROM hosts_hostgroups)) OR object_id IN (SELECT DISTINCT hr.hostgroup_hg_id FROM " .
            $conf_centreon['db'] . ".hostgroup_relation hr, " . $conf_centreon['db'] . ".ns_host_relation nhr " .
            "WHERE hr.host_host_id = nhr.host_host_id AND nhr.nagios_server_id = '$pollerId'))))";
        $dbResult = $this->pearDBMonitoring->query($query);
        if (PEAR::isError($dbResult)) {
            throw new \RestInternalServerErrorException();
        }
        if ($dbResult->numRows()) {
            return true;
        }
        return false;
    }
}
