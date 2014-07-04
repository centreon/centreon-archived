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

use \CentreonRealtime\Repository\ServicedetailRepository;
use \CentreonRealtime\Repository\HostdetailRepository;
use \Centreon\Internal\Di;
use \Centreon\Internal\Exception;

/**
 * Handles external commands
 *
 * @author Sylvestre Ho
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class ExternalcommandController extends \Centreon\Internal\Controller
{
    /**
     * Send external command
     * source defines whether actions comes from the service console
     * or the host console
     *
     * @method post
     * @route /realtime/externalcommands/[i:cmdid]/[i:source]
     */
    public function sendCommandAction()
    {
        $params = $this->getParams();
        $cmdId = $params['cmdid'];
        switch ($cmdId) {
            case ServicedetailRepository::SCHEDULE_CHECK:
            case ServicedetailRepository::SCHEDULE_FORCED_CHECK:
            case ServicedetailRepository::SCHEDULE_CHECK:
            case ServicedetailRepository::REMOVE_ACKNOWLEDGE:
            case ServicedetailRepository::REMOVE_DOWNTIME:
            case ServicedetailRepository::ENABLE_CHECK:
            case ServicedetailRepository::DISABLE_CHECK:
                $this->displayConfirmationBox(
                    "\CentreonRealtime\Repository\ServicedetailRepository",
                    $cmdId,
                    $params['ids']
                );
                break;
            case HostdetailRepository::SCHEDULE_CHECK:
            case HostdetailRepository::SCHEDULE_FORCED_CHECK:
            case HostdetailRepository::SCHEDULE_CHECK:
            case HostdetailRepository::REMOVE_ACKNOWLEDGE:
            case HostdetailRepository::REMOVE_DOWNTIME:
            case HostdetailRepository::ENABLE_CHECK:
            case HostdetailRepository::DISABLE_CHECK:
                $this->displayConfirmationBox(
                    '\CentreonRealtime\Repository\HostdetailRepository',
                    $cmdId,
                    $this->getHostIds($params['ids'], $params['source'])
                );
                break;
            case ServicedetailRepository::ACKNOWLEDGE:
                $this->displayAcknowledgementBox(
                    $cmdId,
                    $params['ids']
                );
                break;
            case HostdetailRepository::ACKNOWLEDGE:
                $this->displayAcknowledgementBox(
                    $cmdId,
                    $this->getHostIds($params['ids'], $params['source'])
                );
                break;
            case ServicedetailRepository::DOWNTIME:
                $this->displayDowntimeBox(
                    $cmdId,
                    $params['ids']
                );
            case HostdetailRepository::DOWNTIME:
                $this->displayDowntimeBox(
                    $cmdId,
                    $this->getHostIds($params['ids'], $params['source'])
                );
                break;
 
        }
    }

    /**
     * Acknowledge host / service problems 
     *
     * @method post
     * @route /realtime/externalcommands/advanced/[i:cmdid]
     * @todo 
     */
    public function advancedAction()
    {
        $params = $this->getParams();
        $cmdId = $params['cmdid'];
        $router = Di::getDefault()->get('router');
        try {
            if ($cmdId == ServicedetailRepository::DOWNTIME || $cmdId == HostdetailRepository::DOWNTIME) {
                list($start, $end) = split(' - ', $params['period']);
                $params['start_time'] = strtotime($start);
                $params['end_time'] = strtotime($end);
            }
            switch ($cmdId) {
                case ServicedetailRepository::DOWNTIME:
                case ServicedetailRepository::ACKNOWLEDGE:
                    ServicedetailRepository::processCommand($cmdId, $params['ids'], $params);
                    break;
                case HostdetailRepository::DOWNTIME:
                case HostdetailRepository::ACKNOWLEDGE:
                    HostdetailRepository::processCommand($cmdId, $params['ids'], $params);
                    break;
            }
            $router->response()->json(array('message' => _('Command has been successfully submitted')));
        } catch (Exception $e) {
            $router->response()->json(array('message' => $e->getMessage()));
        }
    }

    /**
     * Display confirmation box
     *
     * @param string $repository
     * @param int $cmdId
     * @param array $objectIds
     */
    protected function displayConfirmationBox($repository, $cmdId, $objectIds)
    {
        $template = Di::getDefault()->get('template');
        
        try {
            $repository::processCommand($cmdId, $objectIds);
            $template->assign('commandResult', _('command has been successfully submitted.'));
        } catch (Exception $e) {
            $template->assign('commandResult', $e->getMessage());
        }
        $template->display('file:[CentreonRealtimeModule]action_confirm.tpl');
    }

    /**
     * Display acknowldgement box
     *
     * @param array $objectIds
     */
    protected function displayAcknowledgementBox($cmdId, $objectIds)
    {
        $template = Di::getDefault()->get('template');
        $user = $_SESSION['user'];
        $template->assign('user', $user->getName());

        $template->assign('ids', $objectIds);
        $template->assign('cmdid', $cmdId);
        $template->display('file:[CentreonRealtimeModule]action_acknowledgement.tpl');
    }

    /**
     * Display downtime box
     *
     * @param array $objectIds
     */
    protected function displayDowntimeBox($cmdId, $objectIds)
    {
        $template = Di::getDefault()->get('template');
        $template->addCss('daterangepicker-bs3.css');
        $template->addJs('daterangepicker.js');
        $user = $_SESSION['user'];
        $template->assign('user', $user->getName());
        $template->assign('ids', $objectIds);
        $template->assign('cmdid', $cmdId);
        $template->display('file:[CentreonRealtimeModule]action_downtime.tpl');
    }

    /**
     * Convert ids when necessary
     * Service ids could be received while we want to perform actions on hosts
     *
     * @param array $objectIds
     * @param int $source
     */
    protected function getHostIds($objectIds, $source)
    {
        /* we come from service console, need conversion */
        if ($source == 1) {
            return ServicedetailRepository::getHostIdFromServiceId($objectIds);
        }
        return $objectIds;
    }
}
