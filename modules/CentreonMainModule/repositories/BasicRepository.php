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
}
