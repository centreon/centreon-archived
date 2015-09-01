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
namespace CentreonAdministration\Repository;

use Centreon\Internal\Di;
use CentreonAdministration\Models\ContactInfo;

/**
 * Description of NotificationWayRepository
 *
 * @author Kevin Duret <kduret@centreon.com>
 */
class NotificationWayRepository
{
    /**
     * Save contact notification ways
     *
     * @param int $id The contact id
     * @param string $action The action
     * @param array $params The parameters to save
     */
    public static function saveNotificationWays($id, $action = 'add', $listWays = array())
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        if ($action == 'update') {
            $contactInfos = ContactInfo::getIdByParameter('contact_id', $id);
            foreach ($contactInfos as $contactInfo) {
                ContactInfo::delete($contactInfo);
            }
        }

        if (count($listWays) > 0) {
            foreach ($listWays as $name => $params) {
                ContactInfo::insert(array(
                    'contact_id' => $id,
                    'info_key' => $name,
                    'info_value' => $params['value']
                ));
            }
        }
    }

    /**
     *
     * @return array $notificationWays The list of existing notification ways
     */
    public static function getNotificationWays()
    {
        // @todo store notification ways in database
        $notificationWays = array('sms', 'email', 'twitter', 'whatsapp');
        return $notificationWays;
    }

    /**
     * 
     * @param type $objectId
     */
    public static function loadContactNotificationWay($objectId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        $getRequest = "SELECT info_key, info_value"
            . " FROM cfg_contacts_infos "
            . " WHERE contact_id = :contact";
        $stmtGet = $dbconn->prepare($getRequest);
        $stmtGet->bindParam(':contact', $objectId, \PDO::PARAM_INT);
        $stmtGet->execute();
        $rowWay = $stmtGet->fetchAll(\PDO::FETCH_ASSOC);
        return $rowWay;
    }
}
