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

    /**
     * Display the graph map of issue
     *
     * @route /realtime/issueGraph/[i:id]
     * @method GET
     */
    public function displayIssueGraphAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        $issueId = $router->request()->param('id');

        $tmpl = $di->get('template');
        $tmpl->addJs('jquery.jsPlumb-1.6.1-min.js');
        $tmpl->addJs('centreon.issuesGraph.js');
        $tmpl->addCss('centreon.issuesGraph.css');
        $tmpl->assign('issue_id', $issueId);

        $tmpl->display('file:[CentreonRealtimeModule]issue_graph.tpl');
    }

    /**
     * Get information for a issue
     *
     * @route /realtime/issueGraph
     * @method POST
     */
    public function getIssueGraphInfoAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        $response = array();

        $action = $router->request()->param('action', null);
        $issueId = $router->request()->param('issue_id', null);
        if (is_null($issueId)) {
            $router->response()->code(400);
            return;
        }
        switch ($action) {
            case 'get_info':
                $issue = \CentreonRealtime\Repository\IssuesRepository::getIssue($issueId);
                $fullname = $issue['name'];
                if (false === is_null($issue['description'])) {
                    $fullname .= ' - ' . $issue['description'];
                }
                $response = array(
                    'id' => $issue['issue_id'],
                    'name' => $fullname,
                    'status' => self::getCssStatus($issue['state']),
                    'output' => '',
                    'last_update' => '',
                    'has_children' => $issue['nb_children'] > 0 ? true : false,
                    'has_parent' => $issue['nb_parents'] > 0 ? true : false,
                    'parents' => array_map(function($values) {
                        $parent = array();
                        $parent['id'] = $values['issue_id'];
                        $fullname = $values['name'];
                        if (!is_null($values['description'])) {
                            $fullname .= ' - ' . $values['description'];
                        }
                        $parent['name'] = $fullname;
                        return $parent;
                    }, $issue['parents'])
                );
                break;
            case 'getChildren':
                $listChildren = \CentreonRealtime\Repository\IssuesRepository::getChildren($issueId);
                $response = array();
                foreach ($listChildren as $child) {
                    $fullname = $child['name'];
                    if (false === is_null($child['description'])) {
                        $fullname .= ' - ' . $child['description'];
                    }
                    $response[] = array(
                        'id' => $child['issue_id'],
                        'name' => $fullname,
                        'status' => self::getCssStatus($child['state']),
                        'output' => '',
                        'last_update' => '',
                        'has_children' => $child['nb_children'] > 0 ? true : false,
                        'has_parent' => $child['nb_parents'] > 0 ? true : false
                    );
                }
                break;
            default:
                $router->response()->code(400);
                return;
        }
        $router->response()->json($response);
    }

    /**
     * Get the status CSS for issue graph
     *
     * @param int $state The state number
     */
    public static function getCssStatus($state)
    {
        $status = "";
        switch ($state) {
            case 0:
                $status = "panel-default";
                break;
            case 1:
                $status = "panel-success";
                break;
            case 2:
                $status = "panel-warning";
                break;
            case 3:
                $status = "panel-warning";
                break;
            case 4:
                $status = "panel-danger";
                break;
        }
        return $status;
    }
}
