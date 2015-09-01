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
 * Configure notification rules
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package CentreonConfiguration
 * @subpackage Controller
 * @version 3.0.0
 */
class NotificationRuleController extends FormController
{
    protected $objectDisplayName = 'Notification Rule';
    public static $objectName = 'notification-rule';
    protected $objectBaseUrl = '/centreon-configuration/notification-rule';
    protected $datatableObject = '\CentreonConfiguration\Internal\NotificationRuleDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\NotificationRule';
    protected $repository = '\CentreonConfiguration\Repository\NotificationRuleRepository';
    public static $isDisableable = true;

    public static $relationMap = array(
        'notification_rules_contacts' => '\CentreonConfiguration\Models\Relation\NotificationRule\Contact',
        'notification_rules_tags_contacts' => '\CentreonConfiguration\Models\Relation\NotificationRule\ContactTag',
        'notification_rules_hosts' => '\CentreonConfiguration\Models\Relation\NotificationRule\Host',
        'notification_rules_tags_hosts' => '\CentreonConfiguration\Models\Relation\NotificationRule\HostTag',
        'notification_rules_services' => '\CentreonConfiguration\Models\Relation\NotificationRule\Service',
        'notification_rules_tags_services' => '\CentreonConfiguration\Models\Relation\NotificationRule\ServiceTag',
    );

    /**
     * Get the contact for a specific rule
     *
     * @method get
     * @route /notification-rule/[i:id]/contact
     */
    public function getContactAction()
    {
        parent::getRelations(static::$relationMap['notification_rules_contacts']);
    }

    /**
     * Get the list of contact tags for a rule
     * 
     * @method get
     * @route /notification-rule/[i:id]/contact/tag
     */
    public function getTagContactAction()
    {
        parent::getRelations(static::$relationMap['notification_rules_tags_contacts']);
    }

    /**
     * Get the host for a specific rule
     *
     * @method get
     * @route /notification-rule/[i:id]/host
     */
    public function getHostAction()
    {
        parent::getRelations(static::$relationMap['notification_rules_hosts']);
    }

    /**
     * Get the list of host tags for a rule
     * 
     * @method get
     * @route /notification-rule/[i:id]/host/tag
     */
    public function getTagHostAction()
    {
        parent::getRelations(static::$relationMap['notification_rules_tags_hosts']);
    }

    /**
     * Get the service for a specific rule
     *
     * @method get
     * @route /notification-rule/[i:id]/service
     */
    public function getServiceAction()
    {
        parent::getRelations(static::$relationMap['notification_rules_services']);
    }

    /**
     * Get the list of service tags for a rule
     * 
     * @method get
     * @route /notification-rule/[i:id]/service/tag
     */
    public function getTagServiceAction()
    {
        parent::getRelations(static::$relationMap['notification_rules_tags_services']);
    }

    /**
     * Get the notification method for a rule
     *
     * @method get
     * @route /notification-rule/[i:id]/notification-method
     */
    public function getMethodAction()
    {
        parent::getSimpleRelation('method_id', '\CentreonConfiguration\Models\NotificationMethod');
    }

    /**
     * Get the timeperiod for a rule
     *
     * @method get
     * @route /notification-rule/[i:id]/timeperiod
     */
    public function getTimezoneAction()
    {
        parent::getSimpleRelation('timeperiod_id', '\CentreonConfiguration\Models\Timeperiod');
    }
}
