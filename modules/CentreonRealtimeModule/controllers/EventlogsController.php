<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */
namespace CentreonRealtime\Controllers;

/**
 * Display the logs of nagios
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Controllers
 */
class EventlogsController extends \Centreon\Internal\Controller
{
    /**
     * The page structure for display
     *
     * @method GET
     * @route /realtime/eventlogs
     */
    public function displayAction()
    {
        $di = \Centreon\Internal\Di::getDefault();

        $tmpl = $di->get('template');
        $tmpl->addJs('moment-with-langs.min.js');
        $tmpl->addJs('daterangepicker.js');
        $tmpl->addJs('jquery.select2/select2.min.js');
        $tmpl->addJs('centreon-table-infinite-scroll.js');
        $tmpl->addCss('select2.css');
        $tmpl->addCss('select2-bootstrap.css');
        $tmpl->addCss('daterangepicker-bs3.css');
        $tmpl->display('file:[CentreonRealtime]eventlogs.tpl');
    }

    /**
     * Get the list of event logs
     *
     * @method POST
     * @route /realtime/eventlogs
     */
    public function getListEventAction()
    {
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        $params = $router->request()->paramsPost();
        $filters = $params->all();

        $fromTime = null;
        if (isset($params['startTime']) && !is_null($params['startTime']) && $params['startTime'] !== '') {
            $fromTime = $params['startTime'];
        }
        if (isset($params['startTime'])) {
            unset($filters['startTime']);
        }
        $listEvents = \CentreonRealtime\Repository\EventlogsRepository::getEventLogs(
            $fromTime,
            'DESC',
            20,
            $filters
        );
        /* Purge data */
        if (isset($_SESSION['eventlogs_lasttime'])
            && count($listEvents['data']) > 0
            && date('Y-m-d H:i:s', $_SESSION['eventlogs_lasttime'][0]) == $listEvents['data'][0]['datetime']) {
            for ($i = 0; $i < $_SESSION['eventlogs_lasttime'][1]; $i++) {
                array_shift($listEvents['data']);
            }
        }

        /* Save new information for last time entry */
        $_SESSION['eventlogs_lasttime'] = array(
            $listEvents['lastTimeEntry'],
            $listEvents['nbEntryForLastTime']
        );
        $router->response()->json($listEvents);
    }

    /**
     * Get new events
     *
     * @method POST
     * @route /realtime/eventlogs/refresh
     */
    public function refreshNewEventLogsAction()
    {
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        $params = $router->request()->paramsPost();
        $filters = $params->all();
        if (isset($params['startTime'])) {
            unset($filters['startTime']);
        }
        $listEvents = \CentreonRealtime\Repository\EventlogsRepository::getEventLogs(
            $params['startTime'],
            'ASC',
            null,
            $filters
        );
        $router->response()->json($listEvents);
    }
}
