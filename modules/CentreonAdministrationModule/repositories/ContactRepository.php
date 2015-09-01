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

use CentreonAdministration\Models\Contact;
use CentreonAdministration\Models\ContactInfo;
use Centreon\Internal\Di;
use CentreonAdministration\Repository\TagsRepository;
use Centreon\Internal\CentreonSlugify;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ContactRepository extends Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_contacts';
    
    public static $objectClass = '\CentreonAdministration\Models\Contact';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Contact';
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'contact' => 'cfg_contacts, contact_id, description'
        ),
    );
    
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
    public static function update($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        if ($validate) {
            self::validateForm($givenParameters, $origin, $route, $validateMandatory);
        }
        
        $aTagList = array();
        $aTags = array();
        
        if (isset($givenParameters['contact_tags'])) {
            $aTagList = explode(",", $givenParameters['contact_tags']);
            foreach ($aTagList as $var) {
                $var = trim($var);
                if (!empty($var)) {
                    array_push($aTags, $var);
                }
            }
        }
        
        if (count($aTags) > 0) {
            TagsRepository::saveTagsForResource(self::$objectName, $givenParameters['object_id'], $aTags, '', false, 1);
        }

        $infoToUpdate['contact_id'] = $givenParameters['object_id'];

        if (isset($givenParameters['timezone_id']) && is_numeric($givenParameters['timezone_id'])) {
            $infoToUpdate['timezone_id'] = $givenParameters['timezone_id'];
        } else {
            $infoToUpdate['timezone_id'] = "";
        }

        if (isset($givenParameters['description'])) {
            $infoToUpdate['description'] = $givenParameters['description'];
        }
        $class = static::$objectClass;
        $sField = $class::getUniqueLabelField();
        if (isset($givenParameters[$sField])) {
            $oSlugify = new CentreonSlugify($class, get_called_class());
            $sSlug = $oSlugify->slug($givenParameters[$sField]);
            $infoToUpdate[$class::getSlugField()] = $sSlug;
        }
        return Contact::update($givenParameters['object_id'], $infoToUpdate);
    }
}
