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

use \CentreonRealtime\Repository\HostdetailRepository,
    \Centreon\Internal\Utils\Status,
    \Centreon\Internal\Utils\Datetime;


/**
 * Display service monitoring states
 *
 * @author Sylvestre Ho
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class HostController extends \Centreon\Internal\Controller
{
    /**
     * Display services
     *
     * @method get
     * @route /realtime/host
     * @todo work on ajax refresh
     */
    public function displayHostsAction()
    {
        /* Load css */
        $this->tpl->addCss('dataTables.css')
        	->addCss('dataTables.bootstrap.css')
        	->addCss('dataTables-TableTools.css');

        /* Load js */
        $this->tpl->addJs('jquery.min.js')
        	->addJs('jquery.dataTables.min.js')
        	->addJs('jquery.dataTables.TableTools.min.js')
        	->addJs('bootstrap-dataTables-paging.js')
        	->addJs('jquery.dataTables.columnFilter.js')
        	->addJs('jquery.select2/select2.min.js')
        	->addJs('jquery.validate.min.js')
        	->addJs('additional-methods.min.js');

        /* Datatable */
        $this->tpl->assign('moduleName', 'CentreonRealtime');
        $this->tpl->assign('objectName', 'Host');
        $this->tpl->assign('objectListUrl', '/realtime/host/list');
        $this->tpl->display('file:[CentreonRealtimeModule]console.tpl');
    }

    /**
     * The page structure for display
     *
      * @method get
     * @route /realtime/host/list
     */
    public function listAction()
    {
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        $router->response()->json(
            \Centreon\Internal\Datatable::getDatas(
                'CentreonRealtime',
                'host',
                $this->getParams('get')
            )
        );
    }

    /**
     * Host detail page
     *
     * @method get
     * @route /realtime/host/[i:id]
     */
    public function hostDetailAction()
    {

    }

    /**
     * Host tooltip
     *
     * @method get
     * @route /realtime/host/[i:id]/tooltip
     */
    public function hostTooltipAction()
    {
        $params = $this->getParams();
        $rawdata = HostdetailRepository::getRealtimeData($params['id']);
        if (isset($rawdata[0])) {
            $data = $this->transformRawData($rawdata[0]);
            $this->tpl->assign('title', $rawdata[0]['host_name']);
            $this->tpl->assign('state', $rawdata[0]['state']);
            $this->tpl->assign('data', $data);
        } else {
            $this->tpl->assign('error', sprintf(_('No data found for host id:%s'), $params['id']));
        }
        $this->tpl->display('file:[CentreonRealtimeModule]host_tooltip.tpl');
    }

    /**
     * Transform raw data
     *
     * @param array $rawdata
     * @return array
     */
    protected function transformRawData($rawdata)
    {
        $data = array();

        /* Address */
        $data[] = array(
            'label' => _('Address'),
            'value' => $rawdata['host_address']
        ); 

       /* Instance */
        $data[] = array(
            'label' => _('Poller'),
            'value' => $rawdata['instance_name']
        ); 

        /* State */
        $data[] = array(
            'label' => _('State'),
            'value' => Status::numToString(
                $rawdata['state'], 
                Status::TYPE_HOST, 
                true
            ) . " (" . ($rawdata['state_type'] ? "HARD" : "SOFT") . ")"
        );

        /* Output */
        $data[] = array(
            'label' => _('Output'),
            'value' => $rawdata['output']
        );

        /* Acknowledged */
        $data[] = array(
            'label' => _('Acknowledged'),
            'value' => $rawdata['acknowledged'] ? _('Yes') : _('No')
        );

        /* Downtime */
        $data[] = array(
            'label' => _('In downtime'),
            'value' => $rawdata['scheduled_downtime_depth'] ? _('Yes') : _('No')
        );

        /* Latency */
        $data[] = array(
            'label' => _('Latency'),
            'value' => $rawdata['latency'] . ' s'
        );

        /* Check period */
        $data[] = array(
            'label' => _('Check period'),
            'value' => $rawdata['check_period']
        );

        /* Last check */
        $data[] = array(
            'label' => _('Last check'),
            'value' => Datetime::format($rawdata['last_check'])
        );

        /* Next check */
        $data[] = array(
            'label' => _('Next check'),
            'value' => Datetime::format($rawdata['next_check'])
        );

        return $data;
    }
}
