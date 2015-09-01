<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonRealtime\Controllers;

use CentreonRealtime\Repository\ServicedetailRepository;
use CentreonRealtime\Repository\HostdetailRepository;
use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use Centreon\Internal\Controller;

/**
 * Handles external commands
 *
 * @author Sylvestre Ho
 * @package CentreonRealtime
 * @subpackage Controllers
 */
class ExternalcommandController extends Controller
{
    /**
     * Send external command
     * source defines whether actions comes from the service console
     * or the host console
     *
     * @method post
     * @route /externalcommands/[i:cmdid]/[i:source]
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
                break;
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
     * @route /externalcommands/advanced/[i:cmdid]
     * @todo 
     */
    public function advancedAction()
    {
        $params = $this->getParams();
        $cmdId = $params['cmdid'];
        $router = Di::getDefault()->get('router');
        try {
            if ($cmdId == ServicedetailRepository::DOWNTIME || $cmdId == HostdetailRepository::DOWNTIME) {
                list($start, $end) = explode(' - ', $params['period']);
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        $template->addCss('centreon.less');
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
