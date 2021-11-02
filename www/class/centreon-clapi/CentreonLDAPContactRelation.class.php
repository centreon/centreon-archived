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

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once "centreonUtils.class.php";
require_once "centreonTimePeriod.class.php";
require_once "Centreon/Object/Contact/Contact.php";
require_once "Centreon/Object/Command/Command.php";
require_once "Centreon/Object/Timezone/Timezone.php";
require_once "Centreon/Object/Relation/Contact/Command/Host.php";
require_once "Centreon/Object/Relation/Contact/Command/Service.php";
require_once "centreonLDAP.class.php";

/**
 * Class representating relation between a contact and his LDAP configuration
 *
 * @author sylvestre
 */
class CentreonLDAPContactRelation extends CentreonObject
{
    const ORDER_UNIQUENAME = 1;
    const ORDER_NAME = 0;
    const ORDER_MAIL = 2;
    const ORDER_PASS = 3;
    const ORDER_ADMIN = 4;
    const ORDER_ACCESS = 5;
    const ORDER_LANG = 6;
    const ORDER_AUTHTYPE = 7;
    const HOST_NOTIF_TP = "hostnotifperiod";
    const SVC_NOTIF_TP = "svcnotifperiod";
    const HOST_NOTIF_CMD = "hostnotifcmd";
    const SVC_NOTIF_CMD = "svcnotifcmd";
    const UNKNOWN_LOCALE = "Invalid locale";
    const UNKNOWN_TIMEZONE = "Invalid timezone";
    const CONTACT_LOCATION = "timezone";
    const UNKNOWN_NOTIFICATION_OPTIONS = "Invalid notifications options";
    private const LDAP_PARAMETER_NAME = "ar_name";

    protected $register;
    public static $aDepends = array(
        'CONTACT',
        'LDAP'
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->dependencyInjector = $dependencyInjector;
        $this->tpObject = new CentreonTimePeriod($dependencyInjector);
        $this->ldap = new CentreonLdap($dependencyInjector);
        $this->object = new \Centreon_Object_Contact($dependencyInjector);
        $this->timezoneObject = new \Centreon_Object_Timezone($dependencyInjector);
        $this->params = array(
            'contact_host_notification_options' => 'n',
            'contact_service_notification_options' => 'n',
            'contact_location' => '0',
            'contact_enable_notifications' => '0',
            'contact_type_msg' => 'txt',
            'contact_activate' => '1',
            'contact_register' => '1'
        );
        $this->insertParams = array(
            'contact_name',
            'contact_alias',
            'contact_email',
            'contact_passwd',
            'contact_admin',
            'contact_oreon',
            'contact_lang',
            'contact_auth_type'
        );
        $this->exportExcludedParams = array_merge(
            $this->insertParams,
            array(
                $this->object->getPrimaryKey(),
                "contact_register"
            )
        );
        $this->action = "LDAPCONTACT";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->register = 1;
        $this->activateField = 'contact_activate';
    }

    /**
     * @param $parameters
     * @return array
     * @throws CentreonClapiException
     */
    public function initUpdateParameters($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $objectId = $this->getObjectId($params[self::ORDER_NAME]);
        $params[self::ORDER_NAME] = str_replace(" ", "_", $params[self::ORDER_NAME]);
    }

    /**
     * Export data
     *
     * @param null $filterName
     * @return bool|void
     */
    public function export($filterName = null)
    {
        if (!$this->canBeExported($filterName)) {
            return false;
        }

        $labelField = $this->object->getUniqueLabelField();
        $filters = array("contact_register" => $this->register);
        if (!is_null($filterName)) {
            $filters[$labelField] = $filterName;
        }
        $elements = $this->object->getList(
            "*",
            -1,
            0,
            $labelField,
            'ASC',
            $filters,
            "AND"
        );
        foreach ($elements as $element) {
            foreach ($element as $parameter => $value) {
                if (!is_null($value) && $value != "" && !in_array($parameter, $this->exportExcludedParams)) {
                    if ($parameter === "ar_id") {
                        $parameter = self::LDAP_PARAMETER_NAME;
                        $value = $this->ldap->getObjectName($value);

                        $value = CentreonUtils::convertLineBreak($value);
                        echo $this->action . $this->delim
                        . "setparam" . $this->delim
                        . $element[$this->object->getUniqueLabelField()] . $this->delim
                        . $parameter . $this->delim
                        . $value . "\n";
                    }
                }
            }
        }
    }
}
