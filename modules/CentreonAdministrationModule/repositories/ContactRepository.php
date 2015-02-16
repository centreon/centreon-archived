<?php
/*
 * Copyright 2005-2014 CENTREON
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
     * @param array $givenParameters
     */
    public static function addContactInfo($givenParameters)
    {
        $infoToInsert = array(
            'contact_id' => $givenParameters['object_id'],
            'info_key' => $givenParameters['contact_info_key'],
            'info_value' => $givenParameters['contact_info_value'],
        );
        return ContactInfo::insert($infoToInsert);
    }
    
    /**
     * 
     * @param integer $id
     */
    public static function removeContactInfo($id)
    {
        ContactInfo::delete($id);
    }

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
}
