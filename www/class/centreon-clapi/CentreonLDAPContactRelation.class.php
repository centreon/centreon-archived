<?php

/*
 * Copyright 2005-2021 CENTREON
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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
    private const ORDER_NAME = 0;
    private const LDAP_PARAMETER_NAME = "ar_name";

    protected int $register;
    public static $aDepends = array(
        'CONTACT',
        'LDAP'
    );

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
     * Constructor
     *
     * @return void
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->dependencyInjector = $dependencyInjector;
        $this->ldap = new CentreonLdap($dependencyInjector);
        $this->object = new \Centreon_Object_Contact($dependencyInjector);
        $this->action = "LDAPCONTACT";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->register = 1;
        $this->activateField = 'contact_activate';
    }

    /**
     * Export data
     *
     * @param string|null $filterName
     * @return bool
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
                        $value = $this->ldap->getObjectName($value);
                        $value = CentreonUtils::convertLineBreak($value);
                        echo $this->action . $this->delim
                        . "setparam" . $this->delim
                        . $element[$this->object->getUniqueLabelField()] . $this->delim
                        . self::LDAP_PARAMETER_NAME . $this->delim
                        . $value . "\n";
                    }
                }
            }
        }
        return true;
    }
}