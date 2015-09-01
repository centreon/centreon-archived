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
 * Description of CentreonDb
 *
 * @author lionel
 */
class CentreonDb implements DataProviderInterface
{

    public static function loadDatas(
        $params,
        array $columns,
        array $specialFields,
        $datatableClass,
        $modelClass = '',
        $additionnalClass = null,
        $aFieldNotAuthorized = array()
    ) {
       
        // Get Fields to be request
        $fields = "";
        $otherFields = "";
        $result = array();
        $specialFieldsKeys = array_keys($specialFields);
        foreach ($columns as $column) {
            if (!in_array($column['name'], $aFieldNotAuthorized)) {
                if (!in_array($column['name'], $specialFieldsKeys)) {
                    if (isset($column['source'])) {
                        $otherFields .= $column['name'] . ',';
                    } else {
                        $fields .= $column['name'] . ',';
                    }
                } elseif (in_array($column['name'], $specialFieldsKeys) && $specialFields[$column['name']]['sameSource']) {
                    $fields .= $specialFields[$column['name']]['source'] . ',';
                }
            }
        }
        $fields = rtrim($fields, ',');
        $otherFields = rtrim($otherFields, ',');       
        
        // get fields for search  
        $conditions = array();
        foreach ($params['columns'] as $columnSearch) {
            if ($columnSearch['searchable'] === "true" && (!empty($columnSearch['search']['value']) || $columnSearch['search']['value'] == "0")) {
                if ($columnSearch['data'] == 'tagname') {
                    $aSearch = explode(" ", $columnSearch['search']['value']);
                    foreach ($aSearch as $sSearch) {
                        $conditions[$columnSearch['data']][] = $sSearch;
                    }
                } else {
                    foreach ($columns as $column) {
                        if ($column['data'] === $columnSearch['data']) {
                            if (isset($column['type']) && (strtolower($column['type']) === 'string')) {
                                preg_match('"([^\\"]+)"', $columnSearch['search']['value'], $result);
                                preg_replace('"([^\\"]+)"','',$columnSearch['search']['value']);
                                $aSearch = preg_split( '/[, ]+/', $columnSearch['search']['value'] ); 
                                foreach($result as $res){
                                    $aSearch[] = $res;
                                }
                                foreach ($aSearch as $sSearch) {
                                    $conditions[$columnSearch['data']][] = '%' .$sSearch. '%';
                                }
                                //$conditions[$columnSearch['data']] = '%' . $columnSearch['search']['value'] . '%';
                            } else {
                                $aSearch = preg_split( '/[, ]+/', $columnSearch['search']['value'] );
                                foreach ($aSearch as $sSearch) {
                                    $conditions[$columnSearch['data']][] = $sSearch;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if (isset($additionnalClass)) {
            $result = $additionnalClass::getMergedParametersBySearch(
                explode(',', $fields),
                explode(',', $otherFields),
                $params['length'],
                $params['start'],
                $columns[$params['order'][0]['column']]['name'],
                $params['order'][0]['dir'],
                $conditions,
                "AND"
            );

            $result2 = $additionnalClass::getMergedParametersBySearch(
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
            $result = $modelClass::getListBySearch(
                $fields,
                $params['length'],
                $params['start'],
                $columns[$params['order'][0]['column']]['name'],
                $params['order'][0]['dir'],
                $conditions,
                "AND"
            );
            
            $result2 = $modelClass::getListBySearch(
                'count(*)',
                -1,
                0,
                null,
                'ASC',
                $conditions,
                "AND"
            );

            if (isset($result2[0]['count(*)'])) {
                $a['nbOfTotalDatas'] = $result2[0]['count(*)'];
            } else {
                $a['nbOfTotalDatas'] = 0;
            }
        }

        $a['datas'] = $result;
        
        return $a;
    }
}
