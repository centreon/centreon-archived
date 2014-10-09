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

namespace Centreon\Internal;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
class Datatable
{
    /**
     *
     * @var string 
     */
    protected static $hook = '';

    /**
     * Parameters send to hook
     *
     * A var _ids is add to this parameters and content the list of id of the table
     * 
     * @var array
     */
    protected static $hookParams = array();
    
    /**
     *
     * @var string 
     */
    protected static $objectId = '';
    
    /**
     *
     * @var string 
     */
    protected static $objectName = '';

    /**
     *
     * @var string 
     */
    protected $objectModelClass;
    
    /**
     *
     * @var string 
     */
    protected static $dataprovider = '';
    
    /**
     *
     * @var array 
     */
    public static $fieldList = array();
    
    /**
     *
     * @var array 
     */
    protected $options = array();
    
    /**
     *
     * @var array 
     */
    public static $columns = array();
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array();
    
    /**
     *
     * @var array 
     */
    protected $specialFields = array();
    
    /**
     *
     * @var array 
     */
    protected $params = array();
    
    /**
     *
     * @var type 
     */
    protected $rawDatasFromDb;
    
    /**
     *
     * @var string 
     */
    protected static $additionnalDatasource = null;
    
    /**
     *
     * @var array 
     */
    private static $nonDatatableParams = array(
        'cast',
        'searchParam',
        'source',
        'searchvalues'
    );
    
    /**
     * 
     * @param array $params
     * @param string $objectModelClass
     */
    public function __construct($params, $objectModelClass = '')
    {
        $this->params = $params;
        $this->objectModelClass = $objectModelClass;
    }
    
    /**
     * 
     * @return array
     */
    public function getDatas()
    {
        $provider = static::$dataprovider;
        $datasFromDb = $provider::loadDatas(
            $this->params,
            static::$columns,
            $this->specialFields,
            get_class($this),
            $this->objectModelClass,
            static::$additionnalDatasource
        );
        
        static::addAdditionnalDatas($datasFromDb['datas']);
        static::processHooks($datasFromDb['datas']);
        $this->formatDatas($datasFromDb['datas']);
        $sendableDatas = $this->prepareDatasForSending($datasFromDb);
        
        return $sendableDatas;
    }
    
    /**
     * 
     * @param array $datasToFormat
     */
    protected function formatDatas(&$datasToFormat)
    {
        
    }
    
    /**
     * 
     * @param array $datasToSend
     * @return array
     */
    protected function prepareDatasForSending($datasToSend)
    {
        $datasToSend['datas'] = $this->castResult($datasToSend['datas']);
        
        // format the data before returning
        $finalDatas = array(
            "sEcho" => intval($this->params['sEcho']),
            "iTotalRecords" => count($datasToSend['datas']),
            "iTotalDisplayRecords" => $datasToSend['nbOfTotalDatas'],
            "aaData" => $datasToSend['datas']
        );
        
        return $finalDatas;
    }
    
    /**
     * 
     * @param array $resultSet
     */
    protected static function addAdditionnalDatas(&$resultSet)
    {
        
    }
    
    /**
     * 
     * @return array
     */
    public static function getHeader()
    {
        $columnHeader = "";
        $columnSearchIndex = array();
        $nbFixedTr = count(static::$columns);

        if (isset(static::$hook) && static::$hook) {
            $hookData = Hook::execute(static::$hook, array());
            foreach ($hookData as $data) {
                $columnName = $data['columnName'];
                static::$columns[] = array(
                    'name' => $columnName,
                    'title' => $columnName,
                    'data' => $columnName,
                    'orderable' => false,
                    'searchable' => false
                );
            }
        }

        foreach (static::$columns as $column) {
            static::$fieldList[] = $column['name'];
            $columnHeader .= '{';
            $searchable = false;
            
            foreach ($column as $key => $value) {
                if (!in_array($key, self::$nonDatatableParams)) {
                    if (is_string($value)) {
                        $columnHeader .= '"' . $key . '":"' . addslashes($value) . '",';
                    } elseif (is_bool($value)) {
                        if ($value === true) {
                            $columnHeader .= '"' . $key . '":true,';
                        } else {
                            $columnHeader .= '"' . $key . '":false,';
                        }
                    } else {
                        $columnHeader .= '"' . $key . '":' . (string)$value . ',';
                    }

                    if (($key === 'searchable')) {
                        $searchable = $value;
                    }
                }
            }
            
            $columnHeader .= "},\n";
            if ($searchable) {
                $searchParam = array ('type' => 'text');
                if (isset($column['searchParam'])) {
                    $searchParam = array_merge($searchParam, $column['searchParam']);
                }
                $searchParam['title'] = $column['title'];
                $searchParam['colIndex'] = array_search($column['name'], static::$fieldList);
                
                if (!isset($searchParam['main'])) {
                    $searchParam['main'] = false;
                }
                
                $columnSearchIndex[addslashes($column['data'])] = $searchParam;
            }
            
        }
        
        return array(
            'columnHeader' => $columnHeader,
            'columnSearch' => $columnSearchIndex,
            'nbFixedTr' => $nbFixedTr
        );
    }

    /**
     * 
     * @return string
     */
    public static function getConfiguration()
    {
        $configurationParams = "";
        foreach (static::$configuration as $configName => $configEntry) {
            
            if ($configName == 'order') {
                $configEntry = self::initOrder($configEntry);
            }
            
            if ($configName == 'searchCols') {
                $configEntry = self::initSearch($configEntry);
            }
            
            $configEntry = (is_array($configEntry)) ? json_encode($configEntry) : $configEntry;
            
            if (is_bool($configEntry)) {
                if ($configEntry === true) {
                    $configEntry = 'true';
                } else {
                    $configEntry = 'false';
                }
            }
            
            $configurationParams .= '"' . $configName . '":' . $configEntry . ",\n";
        }
        
        return trim($configurationParams);
    }

    /**
     * 
     * @param type $configEntry
     * @return array
     */
    private static function initSearch($configEntry)
    {
        $rawSeachTable = array();
        $listOfSearchField = array_keys(static::$configuration['searchCols']);
        foreach (static::$columns as $column) {
            if (in_array($column['name'], $listOfSearchField)) {
                $rawSeachTable[] = array('sSearch' => $configEntry[$column['name']]);
            } else {
                $rawSeachTable[] = null;
            }
        }
        return $rawSeachTable;
    }
    
    /**
     * 
     * @param array $configEntry
     * @return string
     */
    private static function initOrder($configEntry)
    {
        $line = "[";
        foreach ($configEntry as $order) {
            $line .= "[" . array_search($order[0], static::$fieldList) . ", '". $order[1] ."'],";
        }
        return rtrim($line, ',') . ']';
    }

    /**
     * 
     * @param type $datas
     * @return type
     */
    public static function castResult($datas)
    {
        try {
            $columnsToCast = array();
            foreach (static::$columns as $column) {
                if (isset($column['cast'])) {
                    $columnsToCast[$column['name']] = $column['cast'];
                    $columnsToCast[$column['name']]['caster'] = 'add'.ucwords($column['cast']['type']);
                }
            }

            foreach ($datas as &$singleData) {
                $originalData = $singleData;
                foreach ($columnsToCast as $colName => $colCast) {
                    if (preg_match('/[A-z]\./', $colName)) {
                        $a = explode('.', $colName);
                        array_shift($a);
                        $a = implode('.', $a);
                    } else {
                        $a = $colName;
                    }
                    $singleData[$a] =  self::$colCast['caster']($a, $originalData, $colCast['parameters']);
                }
            }
            
            return $datas;
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    /**
     * 
     * @param type $field
     * @param type $values
     * @param type $cast
     * @return type
     */
    public static function addUrl($field, $values, $cast)
    {
        $castedElement = \array_map(
            function ($n) {
                return "::$n::";
            },
            array_keys($values)
        );
        
        $routeParams = array();
        if (isset($cast['routeParams']) && is_array($cast['routeParams'])) {
            $routeParams = str_replace($castedElement, $values, $cast['routeParams']);
        }
        
        $finalRoute = str_replace(
            "//",
            "/",
            \Centreon\Internal\Di::getDefault()
                ->get('router')
                ->getPathFor($cast['route'], $routeParams)
        );
        
        $linkName =  str_replace($castedElement, $values, $cast['linkName']);
        
        $class = '';
        if (isset($cast['styleClass'])) {
            $class .=$cast['styleClass'];
        }
        
        return '<a class="' . $class . '" href="' . $finalRoute . '">' . $linkName . '</a>';
    }
    
    /**
     * 
     * @param type $field
     * @param type $values
     * @param type $cast
     * @return type
     */
    public static function addCheckbox($field, $values, $cast)
    {
        $datasource = static::$datasource;
        $uniqueField = $datasource::getUniqueLabelField();
        $object = ucwords(str_replace('_', '', $field));
        $input = '<input class="all'. static::$objectName .'Box" '
            . 'id="'. static::$objectName .'::'. $field .'::" '
            . 'name="'. static::$objectName .'[]" '
            . 'type="checkbox" '
            . 'value="::'. $field .'::" '
            . 'data-name="' . htmlentities($values[$uniqueField]) . '"'
            . '/>';
        $castedElement = \array_map(
            function ($n) {
                return "::$n::";
            },
            array_keys($values)
        );
        
        return str_replace($castedElement, $values, $input);
    }
    
    /**
     * 
     * @param type $field
     * @param type $values
     * @param type $cast
     * @return type
     */
    public static function addSelect($field, $values, $cast)
    {
        $myElement = "";
        if (isset($cast['selecttype']) && ($cast['selecttype'] != 'none')) {
            $subCaster = 'add'.ucwords($cast['selecttype']);
            $myElement = static::$subCaster($field, $values, $cast['parameters'][$values[$field]]['parameters']);
        } elseif (isset($values[$field])) {
            $myElement = $cast[$values[$field]];
        }
        
        return $myElement;
    }
    
    /**
     * 
     * @param type $field
     * @param type $values
     * @param type $cast
     * @return type
     */
    public static function addDate($field, $values, $cast)
    {
        return date($cast['date'], $values[$field]);
    }

    /**
     * 
     * @param type $resultSet
     */
    public static function processHooks(&$resultSet)
    {
       	$params = static::$hookParams;
        $params['_ids'] = array();
        foreach ($resultSet as $set) {
            if (isset($set[static::$objectId])) {
                $params['_ids'][] = $set[static::$objectId];
            }
        }
        if (isset(static::$hook) && static::$hook) {
            $hookData = Hook::execute(static::$hook, $params);
            foreach ($hookData as $data) {
                $columnName = $data['columnName'];
                foreach ($data['values'] as $k => $value) {
                    foreach ($resultSet as $key => $set) {
                        if ($set[static::$objectId] == $k) {
                            $resultSet[$key][$columnName] = $value;
                        }
                    }
                }
            }
        }
    }
}
