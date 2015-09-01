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

namespace Centreon\Internal\Datatable\Dataprovider;

/**
 * Description of CentreonStorageDb
 *
 * @author lionel
 */
class CentreonStorageDb implements DataProviderInterface
{
    public static function loadDatas(
        $params,
        array $columns,
        array $specialFields,
        $datatableClass,
        $modelClass = '',
        $additionnalClass = null
    ) {
        // Get Fields to be request
        $fields = "";
        $otherFields = "";
        $otherTables = "";
        $conditions = array();
        $conditionsForTable = array();
        
        $specialFieldsKeys = array_keys($specialFields);
        foreach ($columns as $column) {
            if (isset($column['dbName'])) {
                $column['name'] = $column['dbName'];
            }

            if (!in_array($column['name'], $specialFieldsKeys)) {
                if (isset($column['source'])) {
                    if (is_array($column['source'])) {
                        $otherTables .= $column['source']['table'] . ',';
                        $conditionsForTable[$column['source']['condition']['first']] =
                            $column['source']['condition']['second'];
                        $fields .= $column['name'] . ',';
                    }
                    $otherFields .= $column['name'] . ',';
                } else {
                    $fields .= $column['name'] . ',';
                }
            } elseif (in_array($column['name'], $specialFieldsKeys) && $specialFields[$column['name']]['sameSource']) {
                $fields .= $specialFields[$column['name']]['source'] . ',';
            }
        }
        $fields = rtrim($fields, ',');
        $otherFields = rtrim($otherFields, ',');
        $otherTables = rtrim($otherTables, ',');
        
        $a = array();
        
        // Get
        $fieldList = array();
        foreach ($datatableClass::$columns as $column) {
            $fieldList[] = $column['name'];
        }

        // Get the field label for the search
        foreach ($params as $key => $value) {
            if (substr($key, 0, 7) == 'sSearch') {
                if (!empty($value)) {
                    if ($key === 'host_enabled') {
                        $conditions['h.enabled'] = $value;
                    } elseif ($key === 'service_enabled') {
                        $conditions['s.enabled'] = $value;
                    } else {
                        $b = explode('_', $key);
                        
                        if (is_string($value)) {
                            $possibleValues = array();
                            preg_match_all('/[a-zA-Z\s]+|"[a-zA-Z\s]+"/i', $value, $possibleValues);
                            if (count($possibleValues) > 0) {
                                $value = array();
                                foreach ($possibleValues[0] as $pVal) {
                                    $value[] = str_replace('"', '', trim($pVal));
                                }
                            }
                        }
                        
                        $conditions[$fieldList[$b[1]]] = $value;
                    }
                }
            }
        }
        
        if (isset($additionnalClass)) {
                
            $result = $additionnalClass::getMergedParameters(
                explode(',', $fields),
                explode(',', $otherFields),
                $params['iDisplayLength'],
                $params['iDisplayStart'],
                $columns[$params['iSortCol_0']]['name'],
                $params['sSortDir_0'],
                $conditions,
                "AND"
            );
            
            $result2 = $additionnalClass::getMergedParameters(
                explode(',', $fields),
                array(),
                -1,
                0,
                null,
                'ASC',
                $conditions,
                "AND"
            );
            $a['nbOfTotalDatas'] = count($result2);
        } else {
            $result = $modelClass::getList(
                $fields,
                explode(',', $otherTables),
                $params['iDisplayLength'],
                $params['iDisplayStart'],
                $columns[$params['iSortCol_0']]['name'],
                $params['sSortDir_0'],
                $conditions,
                "AND",
                $conditionsForTable
            );
            
            $result2 = $modelClass::getList(
                'count(*)',
                explode(',', $otherTables),
                -1,
                0,
                null,
                'ASC',
                $conditions,
                "AND",
                $conditionsForTable
            );
            $a['nbOfTotalDatas'] = $result2[0]['count(*)'];
        }
        
        $a['datas'] = $result;
        
        return $a;
    }
}
