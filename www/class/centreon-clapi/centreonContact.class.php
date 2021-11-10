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

/**
 * Class for managing Contact configuration
 *
 * @author sylvestre
 */
class CentreonContact extends CentreonObject
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

    protected $register;
    public static $aDepends = array(
        'CONTACTTPL',
        'CMD',
        'TP'
    );
    /**
     *
     * @var array
     * Contains : list of authorized notifications_options for each objects
     */
    public static $aAuthorizedNotificationsOptions = array(
        'host' => array(
            'd' => 'Down',
            'u' => 'Unreachable',
            'r' => 'Recovery',
            'f' => 'Flapping',
            's' => 'Downtime Scheduled',
            'n' => 'None'
        ),
        'service' => array(
            'w' => 'Warning',
            'u' => 'Unreachable',
            'c' => 'Critical',
            'r' => 'Recovery',
            'f' => 'Flapping',
            's' => 'Downtime Scheduled',
            'n' => 'None'
        )
    );

    /**
     *
     * @var CentreonTimePeriod
     */
    protected $tpObject;

    /**
     *
     * @var Timezone
     */
    protected $timezoneObject;

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
            'contact_auth_type',
            'ar_id'
        );
        $this->exportExcludedParams = array_merge(
            $this->insertParams,
            array(
                $this->object->getPrimaryKey(),
                "contact_register"
            )
        );
        $this->action = "CONTACT";
        $this->nbOfCompulsoryParams = count($this->insertParams);
        $this->register = 1;
        $this->activateField = 'contact_activate';
    }

    /**
     * Get contact ID
     *
     * @param null $contact_name
     * @return mixed
     * @throws CentreonClapiException
     */
    public function getContactID($contact_name = null)
    {
        $cIds = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($contact_name));
        if (!count($cIds)) {
            throw new CentreonClapiException("Unknown contact: " . $contact_name);
        }
        return $cIds[0];
    }

    /**
     * Validate Name format
     *
     * @param $name
     * @return mixed
     */
    protected function validateName($name)
    {
        if (preg_match('/^[0-9a-zA-Z\_\-\ \/\\\.]*$/', $name, $matches) && strlen($name)) {
            return $this->checkNameformat($name);
        } else {
            print "Name '$name' doesn't match with Centreon naming rules.\n";
            exit(1);
        }
    }

    /**
     * Checks if language exists
     *
     * @param string $locale
     * @return bool
     */
    protected function checkLang($locale)
    {
        if (!$locale || $locale == "") {
            return true;
        }
        if (strtolower($locale) === "en_us.utf-8" || strtolower($locale) === "browser") {
            return true;
        }
        $centreonDir = realpath(__DIR__ . "/../../../");
        $dir = $centreonDir . "/www/locale/$locale";
        if (is_dir($dir)) {
            return true;
        }
        return false;
    }

    /**
     * Delete action
     *
     * @param string $parameters
     */
    public function del($parameters)
    {
        if (isset($parameters)) {
            $parameters = str_replace(" ", "_", $parameters);
        }
        parent::del($parameters);
    }

    /**
     * @param null $parameters
     * @param array $filters
     */
    public function show($parameters = null, $filters = array())
    {
        $filters = array('contact_register' => $this->register);
        if (isset($parameters)) {
            $parameters = str_replace(" ", "_", $parameters);
            $filters[$this->object->getUniqueLabelField()] = "%" . $parameters . "%";
        }

        $params = array(
            'contact_id',
            'contact_name',
            'contact_alias',
            'contact_email',
            'contact_pager',
            'contact_oreon',
            'contact_admin',
            'contact_activate'
        );
        $paramString = str_replace("contact_", "", implode($this->delim, $params));
        $paramString = str_replace("oreon", "gui access", $paramString);
        echo $paramString . "\n";
        $elements = $this->object->getList(
            $params,
            -1,
            0,
            null,
            null,
            $filters,
            "AND"
        );
        foreach ($elements as $tab) {
            echo implode($this->delim, $tab) . "\n";
        }
    }

    /**
     * @param $parameters
     * @throws CentreonClapiException
     */
    public function initInsertParameters($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }

        $addParams = array();
        $params[self::ORDER_UNIQUENAME] = str_replace(" ", "_", $params[self::ORDER_UNIQUENAME]);
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['contact_name'] = $this->checkIllegalChar($params[self::ORDER_NAME]);
        $addParams['contact_email'] = $params[self::ORDER_MAIL];
        $addParams['contact_passwd'] = md5($params[self::ORDER_PASS]);

        $algo = $this->dependencyInjector['utils']->detectPassPattern($params[self::ORDER_PASS]);
        if (!$algo) {
            $addParams['contact_passwd'] = $this->dependencyInjector['utils']->encodePass($params[self::ORDER_PASS]);
        } else {
            $addParams['contact_passwd'] = $params[self::ORDER_PASS];
        }

        $addParams['contact_admin'] = $params[self::ORDER_ADMIN];
        if ($addParams['contact_admin'] == '') {
            $addParams['contact_admin'] = '0';
        }
        $addParams['contact_oreon'] = $params[self::ORDER_ACCESS];
        if ($addParams['contact_oreon'] == '') {
            $addParams['contact_oreon'] = '1';
        }
        if (
            empty($params[self::ORDER_LANG])
            || strtolower($params[self::ORDER_LANG]) === "browser"
            || strtoupper(substr($params[self::ORDER_LANG], -6)) === '.UTF-8'
        ) {
            $completeLanguage = $params[self::ORDER_LANG];
        } else {
            $completeLanguage = $params[self::ORDER_LANG] . '.UTF-8';
        }
        if ($this->checkLang($completeLanguage) == false) {
            throw new CentreonClapiException(self::UNKNOWN_LOCALE);
        }
        $addParams['contact_lang'] = $completeLanguage;
        $addParams['contact_auth_type'] = $params[self::ORDER_AUTHTYPE];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
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

        if ($objectId != 0) {
            $regularParam = true;
            if ($params[1] == self::HOST_NOTIF_TP) {
                $params[1] = "timeperiod_tp_id";
                $params[2] = $this->tpObject->getTimeperiodId($params[2]);
            } elseif ($params[1] == self::SVC_NOTIF_TP) {
                $params[1] = "timeperiod_tp_id2";
                $params[2] = $this->tpObject->getTimeperiodId($params[2]);
            } elseif ($params[1] == self::HOST_NOTIF_CMD || $params[1] == self::SVC_NOTIF_CMD) {
                $this->setNotificationCmd($params[1], $objectId, $params[2]);
                $regularParam = false;
            } elseif ($params[1] == self::CONTACT_LOCATION) {
                $iIdTimezone = $this->timezoneObject->getIdByParameter(
                    $this->timezoneObject->getUniqueLabelField(),
                    $params[2]
                );
                if (count($iIdTimezone)) {
                    $iIdTimezone = $iIdTimezone[0];
                } else {
                    throw new CentreonClapiException(self::UNKNOWN_TIMEZONE);
                }
                $params[1] = 'contact_location';
                $params[2] = $iIdTimezone;
            } elseif (!preg_match("/^contact_/", $params[1])) {
                if ($params[1] == "access") {
                    $params[1] = "oreon";
                } elseif ($params[1] == "template") {
                    $params[1] = "template_id";
                    $contactId = $this->getContactID($params[2]);
                    if (!isset($contactId) || !$contactId) {
                        throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[2]);
                    }
                    $params[2] = $contactId;
                } elseif ($params[1] == "authtype") {
                    $params[1] = "auth_type";
                } elseif ($params[1] == "lang" || $params[1] == "language" || $params[1] == "locale") {
                    if (
                        empty($params[2])
                        || strtoupper(substr($params[2], -6)) === '.UTF-8'
                        || strtolower($params[2]) === "browser"
                    ) {
                        $completeLanguage = $params[2];
                    } else {
                        $completeLanguage = $params[2] . '.UTF-8';
                    }
                    if ($this->checkLang($completeLanguage) == false) {
                        throw new CentreonClapiException(self::UNKNOWN_LOCALE);
                    }
                    $params[1] = "lang";
                    $params[2] = $completeLanguage;
                } elseif ($params[1] == "password") {
                    $params[1] = "passwd";
                    $params[2] = md5(trim($params[2]));
                } elseif ($params[1] == "hostnotifopt") {
                    $params[1] = "host_notification_options";
                    $aNotifs = explode(",", $params[2]);
                    foreach ($aNotifs as $notif) {
                        if (!array_key_exists($notif, self::$aAuthorizedNotificationsOptions['host'])) {
                            throw new CentreonClapiException(self::UNKNOWN_NOTIFICATION_OPTIONS);
                        }
                    }
                } elseif ($params[1] == "servicenotifopt") {
                    $params[1] = "service_notification_options";
                    $aNotifs = explode(",", $params[2]);
                    foreach ($aNotifs as $notif) {
                        if (!array_key_exists($notif, self::$aAuthorizedNotificationsOptions['service'])) {
                            throw new CentreonClapiException(self::UNKNOWN_NOTIFICATION_OPTIONS);
                        }
                    }
                }
                if (
                    !in_array(
                        $params[1],
                        [
                            'reach_api',
                            'reach_api_rt',
                            'default_page',
                            'ar_id',
                            'show_deprecated_pages',
                            'enable_one_click_export',
                        ]
                    )
                ) {
                    $params[1] = "contact_" . $params[1];
                }
            }

            if ($regularParam == true) {
                $updateParams = array($params[1] => $params[2]);
                $updateParams['objectId'] = $objectId;
                return $updateParams;
            }
            return array();
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Set Notification Commands
     *
     * @param string $type
     * @param int $contactId
     * @param string $commands
     * @throws CentreonClapiException
     */
    protected function setNotificationCmd($type, $contactId, $commands)
    {
        $cmds = explode("|", $commands);
        $cmdIds = array();
        $cmdObject = new \Centreon_Object_Command($this->dependencyInjector);
        foreach ($cmds as $commandName) {
            $tmp = $cmdObject->getIdByParameter($cmdObject->getUniqueLabelField(), $commandName);
            if (count($tmp)) {
                $cmdIds[] = $tmp[0];
            } else {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND . ":" . $commandName);
            }
        }
        if ($type == self::HOST_NOTIF_CMD) {
            $relObj = new \Centreon_Object_Relation_Contact_Command_Host($this->dependencyInjector);
        } else {
            $relObj = new \Centreon_Object_Relation_Contact_Command_Service($this->dependencyInjector);
        }
        $relObj->delete($contactId);
        foreach ($cmdIds as $cmdId) {
            $relObj->insert($contactId, $cmdId);
        }
    }

    /**
     * Export notification commands
     *
     * @param string $objType
     * @param int $contactId
     * @param string $contactName
     * @return void
     */
    private function exportNotifCommands($objType, $contactId, $contactName)
    {
        $commandObj = new \Centreon_Object_Command($this->dependencyInjector);
        if ($objType == self::HOST_NOTIF_CMD) {
            $obj = new \Centreon_Object_Relation_Contact_Command_Host($this->dependencyInjector);
        } else {
            $obj = new \Centreon_Object_Relation_Contact_Command_Service($this->dependencyInjector);
        }

        $cmds = $obj->getMergedParameters(
            array(),
            array($commandObj->getUniqueLabelField()),
            -1,
            0,
            null,
            null,
            array($this->object->getPrimaryKey() => $contactId),
            "AND"
        );
        $str = "";
        foreach ($cmds as $element) {
            if ($str != "") {
                $str .= "|";
            }
            $str .= $element[$commandObj->getUniqueLabelField()];
        }
        if ($str) {
            echo $this->action . $this->delim
                . "setparam" . $this->delim
                . $contactName . $this->delim
                . $objType . $this->delim
                . $str . "\n";
        }
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
            $algo = $this->dependencyInjector['utils']->detectPassPattern($element['contact_passwd']);
            if (!$algo) {
                $element['contact_passwd'] = $this->dependencyInjector['utils']->encodePass($element['contact_passwd']);
            }
            $addStr = $this->action . $this->delim . "ADD";
            foreach ($this->insertParams as $param) {
                $addStr .= $this->delim . $element[$param];
            }
            $addStr .= "\n";
            echo $addStr;
            foreach ($element as $parameter => $value) {
                if (!is_null($value) && $value != "" && !in_array($parameter, $this->exportExcludedParams)) {
                    if ($parameter == "timeperiod_tp_id") {
                        $parameter = self::HOST_NOTIF_TP;
                        $value = $this->tpObject->getObjectName($value);
                        CentreonTimePeriod::getInstance()->export($value);
                    } elseif ($parameter == "timeperiod_tp_id2") {
                        $parameter = self::SVC_NOTIF_TP;
                        $value = $this->tpObject->getObjectName($value);
                        CentreonTimePeriod::getInstance()->export($value);
                    } elseif ($parameter == "contact_lang") {
                        $parameter = "locale";
                    } elseif ($parameter == "contact_host_notification_options") {
                        $parameter = "hostnotifopt";
                    } elseif ($parameter == "contact_service_notification_options") {
                        $parameter = "servicenotifopt";
                    } elseif ($parameter == "contact_template_id") {
                        $parameter = "template";
                        $result = $this->object->getParameters($value, $this->object->getUniqueLabelField());
                        $value = $result[$this->object->getUniqueLabelField()];
                        CentreonContactTemplate::getInstance()->export($value);
                    } elseif ($parameter == "contact_location") {
                        $parameter = self::CONTACT_LOCATION;
                        $result = $this->timezoneObject->getParameters(
                            $value,
                            $this->timezoneObject->getUniqueLabelField()
                        );
                        if ($result !== false) {
                            $value = $result[$this->timezoneObject->getUniqueLabelField()];
                        }
                    }
                    $value = CentreonUtils::convertLineBreak($value);
                    echo $this->action . $this->delim
                        . "setparam" . $this->delim
                        . $element[$this->object->getUniqueLabelField()] . $this->delim
                        . $parameter . $this->delim
                        . $value . "\n";
                }
            }
            $objId = $element[$this->object->getPrimaryKey()];
            $this->exportNotifCommands(self::HOST_NOTIF_CMD, $objId, $element[$this->object->getUniqueLabelField()]);
            $this->exportNotifCommands(self::SVC_NOTIF_CMD, $objId, $element[$this->object->getUniqueLabelField()]);
        }
    }
}
