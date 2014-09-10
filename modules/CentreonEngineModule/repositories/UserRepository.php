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

namespace CentreonEngine\Repository;

use \Centreon\Internal\Di;
use \CentreonConfiguration\Repository\TimePeriodRepository as TimePeriodConfigurationRepository;;
use \CentreonConfiguration\Repository\UserRepository as UserConfigurationRepository;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class UserRepository
{
    public static function generate(& $filesList, $poller_id, $path, $filename)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Field to not display */
        //$disableField = static::getTripleChoice();
        $field = "contact_id, contact_name, contact_alias as alias, contact_email as email, "
            . "contact_pager as pager, contact_host_notification_options as host_notification_options, "
            . "contact_service_notification_options as service_notification_options, "
            . "contact_enable_notifications as host_notifications_enabled, "
            . "contact_enable_notifications as service_notifications_enabled, "
            . "timeperiod_tp_id as host_notification_period, timeperiod_tp_id2 as service_notification_period ";
        
        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT $field FROM cfg_contacts WHERE contact_activate = '1' ORDER BY contact_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "contact");
            $tmpData = array();
            $args = "";
            foreach ($row as $key => $value) {
                if ($key == "contact_id") {
                    $contact_id = $row["contact_id"];
                } else {
                    if ($key == "host_notification_period" || $key == "service_notification_period") {
                        $value = TimePeriodConfigurationRepository::getPeriodName($value);
                    }
                    if ($value != "") {
                        $tmpData[$key] = $value;
                    }
                }
            }

            /* Get contactgroups */
            $tmpData["contactgroups"] = UserConfigurationRepository::getContactContactGroup($contact_id);
            
            /* Get commands */
            $tmpData["host_notification_commands"] = UserConfigurationRepository::getNotificationCommand(
                $contact_id, 
                "host"
            );
            $tmpData["service_notification_commands"] = UserConfigurationRepository::getNotificationCommand(
                $contact_id, 
                "service"
            );

            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }

        /* Write Check-Command configuration file */
        WriteConfigFileRepository::writeObjectFile($content, $path.$poller_id."/".$filename, $filesList, $user = "API");
        unset($content);
    }
}
