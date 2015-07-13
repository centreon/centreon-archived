<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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
