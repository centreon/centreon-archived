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
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class IssuesController extends \Centreon\Internal\Controller
{
    /**
     * The page structure for display the list
     *
     * @method GET
     * @route /realtime/issues
     */
    public function displayListAction()
    {
        $di = \Centreon\Internal\Di::getDefault();

        $tmpl = $di->get('template');
        $tmpl->addJs('hogan-3.0.0.min.js');
        $tmpl->addJs('centreon-table-infinite-scroll.js');
        $tmpl->display('file:[CentreonRealtimeModule]issues_list.tpl');
    }

    /**
     * Get the list of issues
     *
     * @method POST
     * @route /realtime/issues
     */
    public function getListIssuesAction()
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
        $listIssues = \CentreonRealtime\Repository\IssuesRepository::getIssues(
            $fromTime,
            'DESC',
            20,
            $filters
        );

        $router->response()->json($listIssues);
    }
}
