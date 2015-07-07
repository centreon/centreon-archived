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
 *
 */

namespace Centreon\Internal\Command;

use Centreon\Internal\Di;

abstract class AbstractCommand
{
    /**
     *
     * @var type 
     */
    protected $db;
    
    /**
     *
     * @var type 
     */
    protected $di;
    
    /**
     *
     * @var string 
     */
    public static $moduleName = 'Core';

    /**
     * 
     */
    public function __construct()
    {
        $this->di = Di::getDefault();
        $this->db = $this->di->get('db_centreon');
    }
    
    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function named($methodReflection, array $pass)
    {
        $methodReflection->invokeArgs($this, $pass);
    }
    
    /**
     * 
     * @param type $paramsArray
     * @param type $globalOptional
     */
    public function getCustomsParams($paramsArray,$globalOptional = false)
    {
        foreach($paramsArray as $param){
            $multiple = false;
            $typeInfos = explode('|',$param['paramType']);
            
            $paramName = $param['paramName'];

            $type = 'string';
            $defaultValue = null;
            $booleanValue = null;
            $booleanSetDefault = null;
            $isNotNone = true;
            if (!empty($typeInfos[0])) {
                $hasDefault = false;
                if ($typeInfos[0] == 'none') {
                    unset($this->options[$paramName]);
                    $isNotNone = false;
                } else if($typeInfos[0] == 'Array') {
                    $multiple = true;
                } else {
                   $type = $typeInfos[0]; 
                }
                if ($typeInfos[0] === 'boolean') {
                    if (!empty($typeInfos[1])) {
                        $booleanValue = ($typeInfos[1] == 'true') ? true : false;
                        if ($param['paramRequired']) {
                            $booleanSetDefault = true;
                        }
                    }
                } else if(!empty($typeInfos[1])) {
                    $defaultValue = $typeInfos[1];
                }
            }
            
            if($isNotNone){
                $this->options[$paramName] = array(
                    'paramType' => 'params',
                    'help' => $param['paramComment'],
                    'type' => $type,
                    'multiple' => $multiple,
                    'required' => ($globalOptional) ? false : $param['paramRequired']
                );

                if (!is_null($defaultValue)) {
                    $this->options[$paramName]['defaultValue'] = $defaultValue;
                }

                if (!is_null($booleanValue)) {
                    $this->options[$paramName]['booleanValue'] = $booleanValue;
                }

                if (!is_null($booleanSetDefault)) {
                    $this->options[$paramName]['booleanSetDefault'] = $booleanSetDefault;
                }
            }
        }
    }
    
    /**
     * 
     * @param type $objectArray
     * @param type $globalOptional
     */
    public function getObject($objectArray, $globalOptional = false)
    {
        $required = true;
        if ($globalOptional) {
            $required = false;
        }
        foreach($objectArray as $object){
            $typeInfos = explode('|',$object['objectType']);
            $type = 'string';
            $multiple = false;
            $objectName = $object['objectName'];
            if ($typeInfos[0] == 'Array') {
                $multiple = true;
            } else {
                $type = $typeInfos[0]; 
            }
            $this->options[$objectName] = array(
                'paramType' => 'object',
                'help' => $object['objectComment'],
                'type' => $type,
                'multiple' => $multiple,
                'required' => $required
            );
        }
    }
    
    
}
