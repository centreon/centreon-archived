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
namespace Centreon\Internal\Datatable\Dataprovider;

/**
 * Description of CentreonStorageDb
 *
 * @author lionel
 */
class CentreonStorageDb implements iDataprovider
{
    public static function loadDatas($params, array $columns, array $specialFields, $modelClass = '', $additionnalClass = null)
    {
        // Get Fields to be request
        $fields = "";
        $otherFields = "";
        $specialFieldsKeys = array_keys($specialFields);
        foreach($columns as $column) {
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
        $fields = rtrim($fields, ',');
        $otherFields = rtrim($otherFields, ',');
        
        $conditions = array();
        
        $a = array();
        
        if (isset($additionnalClass)) {
                
            $result = $additionnalClass::getMergedParameters(
                explode(',', $fields),
                explode (',', $otherFields),
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
                $params['iDisplayLength'],
                $params['iDisplayStart'],
                $columns[$params['iSortCol_0']]['name'],
                $params['sSortDir_0'],
                $conditions,
                "AND"
            );
            
            $result2 = $modelClass::getList(
                'count(*)',
                -1,
                0,
                null,
                'ASC',
                $conditions,
                "AND"
            );
            $a['nbOfTotalDatas'] = $result2[0]['count(*)'];
        }
        
        $a['datas'] = $result;
        
        return $a;
    }
}
