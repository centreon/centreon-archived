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

namespace CentreonConfiguration\Controllers;

use Centreon\Internal\Di;
use Centreon\Controllers\FormController;

/**
 * Configure scheduled downtime
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package CentreonConfiguration
 * @subpackage Controller
 * @version 3.0.0
 */
class NotificationMethodController extends FormController
{
    protected $objectDisplayName = 'Notification Method';
    public static $objectName = 'notification-method';
    protected $objectBaseUrl = '/centreon-configuration/notification-method';
    protected $datatableObject = '\CentreonConfiguration\Internal\NotificationMethodDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\NotificationMethod';
    protected $repository = '\CentreonConfiguration\Repository\NotificationMethodRepository';
    public static $isDisableable = true;

    public static $relationMap = array(
    );

    /**
     * List the notification methods
     *
     * @method get
     * @route /notification-method
     */
    public function listAction()
    {
        $this->tpl->addJs('component/centreon.inputWithUnit.js');

        $this->tpl->addCustomJs('$(function() {
                $("#modal").on("loaded.bs.modal", function () {
                    $(".input-time-unit").centreonInputWithUnit();
                });
            });');

        parent::listAction();
    }

    /**
     * Get the nofication command for a specific notification method
     *
     * @method get
     * @route /notification-method/[i:id]/command
     */
    public function commandForHostAction()
    {
        parent::getSimpleRelation('command_id', '\CentreonConfiguration\Models\Command');
    }
}
