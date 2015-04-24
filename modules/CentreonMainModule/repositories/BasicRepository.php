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

namespace CentreonMain\Repository;

use Centreon\Internal\Di;


class BasicRepository
{
    const ORGANIZATION_FIELD = 'organization_id';

    /**
     * @var array
     */
    protected static $relationMap;

    /**
     * @var string
     */
    protected static $objectClass;

    /**
     * @var string
     */
    protected static $secondaryObjectClass;

    /**
     * @var string
     */
    protected static $objectName;


    /**
     * Reset all static properties
     */ 
    public static function reset()
    {
        static::$relationMap = null;
        static::$objectClass = null;
        static::$secondaryObjectClass = null;
        static::$objectName = null;
    }

    /**
     * Set relation map property
     *
     * @param array $relationMap
     */
    public static function setRelationMap($relationMap)
    {
        static::$relationMap = $relationMap;
    }

    /**
     * Set object name property
     *
     * @param string $objectName
     */
    public static function setObjectName($objectName)
    {
        static::$objectName = $objectName;
    }

    /**
     * Set object class property
     *
     * @param string $objectClass
     */ 
    public static function setObjectClass($objectClass)
    {
        static::$objectClass = $objectClass;
    }

    /**
     * Set secondary object class property
     *
     * @param string $secondaryObjectClass
     */
    public static function setSecondaryObjectClass($secondaryObjectClass)
    {
        static::$secondaryObjectClass = $secondaryObjectClass;
    }
    
    /**
     * 
     * @param string $formRoute
     * @param string $formField
     */
    public static function getFormHelp($formRoute, $formField)
    {
        $finalHelpReturn = array(
            'text' => '',
            'url' => ''
        );
        
        // request to get Help and Help url for the field
        $fieldHelpRequest = "SELECT help, help_url "
            . "FROM cfg_forms_fields cff, cfg_forms_blocks_fields_relations cfbfr, cfg_forms_blocks cfb, cfg_forms_sections cfs, cfg_forms cf "
            . "WHERE cff.name = '$formField' "
            . "AND cf.route = '$formRoute' "
            . "AND cfs.form_id = cf.form_id "
            . "AND cfb.section_id = cfs.section_id "
            . "AND cfbfr.block_id = cfb.block_id "
            . "AND cfbfr.field_id = cff.field_id ";
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->query($fieldHelpRequest);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (count($result) > 0) {
            $finalHelpReturn['text'] = $result[0]['help'];
            $finalHelpReturn['url'] = $result[0]['help_url'];
        }
        
        return $finalHelpReturn;
    }
    
    /**
     * 
     * @param type $unicityParams
     */
    public static function getIdFromUnicity($unicityParams)
    {
        $objClass = static::$objectClass;
        $tables = array();
        $conditions = array();
        
        // Building Query
        $query = 'SELECT ' . $objClass::getPrimaryKey() . ' ';
        
        // Checking por unicity's params
        foreach ($unicityParams as $key => $unicityParam) {
            if (isset(static::$unicityFields['fields'][$key])) {
                $fieldComponents = explode (',', static::$unicityFields['fields'][$key]);
                $tables[] = $fieldComponents[0];
                $conditions[] = $fieldComponents[2] . "='$unicityParam'";
            }
        }
        
        // 
        if (isset(static::$unicityFields['joint'])) {
            $tables[] = static::$unicityFields['joint'];
            $conditions[] = static::$unicityFields['jointCondition'];
        }
        
        // FInalizing query
        $query .= 'FROM ' . implode(', ', $tables) . ' WHERE ' . implode(' AND ', $conditions);
        
        // Execute request
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->query($query);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (count($result) > 0) {
            $objectId = $result[0][$objClass::getPrimaryKey()];
        } else {
            throw new \Centreon\Internal\Exception\Validator\MissingParameterException("The given object doesn't exist");
        }
        
        return $objectId;
    }
}
