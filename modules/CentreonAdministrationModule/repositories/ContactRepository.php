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

namespace CentreonAdministration\Repository;

use CentreonAdministration\Models\Contact;
use CentreonAdministration\Models\ContactInfo;
use Centreon\Internal\Di;
use CentreonAdministration\Repository\TagsRepository;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ContactRepository extends \CentreonAdministration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_contacts';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Contact';
    
    /**
     * 
     * @param type $contactId
     * @param type $grouped
     * @return type
     */
    public static function getContactInfo($contactId, $grouped = false)
    {
        $finalInfos = array();
        $contactInfos = ContactInfo::getList('*', -1, 0, null, 'ASC', array('contact_id' => $contactId));
        
        if ($grouped) {
            foreach ($contactInfos as $info) {
                if (!isset($finalInfos[$info['info_key']])) {
                    $finalInfos[$info['info_key']] = array();
                }
                $finalInfos[$info['info_key']][] = array(
                    'value' => $info['info_value'],
                    'id' => $info['contact_info_id']
                );
            }
        } else {
            $finalInfos = $contactInfos;
        }
        
        return $finalInfos;
    }

    /**
     * Update contact
     * @param array $givenParameters
     */
    public static function update($givenParameters)
    {
        $aTagList = array();
        $aTags = array();
        
        if (isset($givenParameters['contact_tags'])) {
            $aTagList = explode(",", $givenParameters['contact_tags']);
            foreach ($aTagList as $var) {
                if (strlen($var)>1) {
                    array_push($aTags, $var);
                }
            }
        }
        
        if (count($aTags) > 0) {
            TagsRepository::saveTagsForResource(self::$objectName, $givenParameters['object_id'], $aTags);
        }

        $wayList = array();
        if (isset($givenParameters['way_name']) && isset($givenParameters['way_value'])) {
            $wayName = $givenParameters['way_name'];
            $wayValue = $givenParameters['way_value'];

            $nbWay = count($wayName);
            for($i=0; $i<$nbWay; $i++) {
                if (!empty($wayName[$i])) {
                    $wayList[] = array(
                        'name' => $wayName[$i],
                        'value' => $wayValue[$i]
                    );
                }
            }
        }

        $contactInfos = ContactInfo::getIdByParameter('contact_id', $givenParameters['object_id']);
        foreach ($contactInfos as $contactInfo) {
            ContactInfo::delete($contactInfo);
        }

        if (count($wayList) > 0) {
            foreach ($wayList as $notificationWay) {
                ContactInfo::insert(array(
                    'contact_id' => $givenParameters['object_id'],
                    'info_key' => $notificationWay['name'],
                    'info_value' => $notificationWay['value']
                ));
            }
        }

        $infoToUpdate['contact_id'] = $givenParameters['object_id'];

        if (isset($givenParameters['timezone_id']) && is_numeric($givenParameters['timezone_id'])) {
            $infoToUpdate['timezone_id'] = $givenParameters['timezone_id'];
        }

        if (isset($givenParameters['description'])) {
            $infoToUpdate['description'] = $givenParameters['description'];
        }

        return Contact::update($givenParameters['object_id'], $infoToUpdate);
    }            
}
