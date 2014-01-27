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

namespace Centreon\Repository;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class CommandRepository implements \Centreon\Repository\RepositoryInterface
{
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        'Type' => 'command_type',
        'Name' => 'command_name',
        'Line' => 'command_line'
    );
    
    public static $datatableHeader = array(
        'select' => array(
            'Check' => '2',
            'Notifications' => '1',
            'Miscelleanous' => '3'
        ),
        'search' => '',
        'search' => ''
    );
    
    public static $datatableFooter = array(
        'select' => array(
            'Check' => '2',
            'Notifications' => '1',
            'Miscelleanous' => '3'
        ),
        'search' => '',
        'search' => ''
    );
    
    public static function  getParametersForDatatable()
    {
        return array(
            'column' => self::$datatableColumn,
            'header' => self::$datatableHeader,
            'footer' => self::$datatableFooter
        );
    }

    public static function getDatasForDatatable($params)
    {
        if (!isset($params['fields']) || count($params['fields']) === 0) {
            $params['fields'] = self::$datatableColumn;
        }
        return self::getCustomDatas($params);
    }
    
    public static function getCustomDatas($params)
    {
        //
        $field_list = '';
        $additionalTables = '';
        $conditions = '';
        
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        // Getting selected field(s)
        if (isset($params['fields'])) {
            if (is_array($params['fields'])) {
                foreach ($params['fields'] as $field) {
                    $field_list .= $field.',';
                }
                $field_list = trim($field_list, ',');
            } else {
                $field_list = $params['fields'];
            }
        } else {
            $field_list = "*";
        }
        
        // Building the final request
        $finalRequest = "SELECT $field_list FROM command $additionalTables $conditions";
        
        // Executing the request
        $stmt = $dbconn->query($finalRequest);
        
        // Returning the result
        return $stmt->fetchAll();
    }
    
    public static function getTotalRecords()
    {
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        // Building the final request
        $request = "SELECT COUNT('id') as nbCommand FROM command";
        
        // Executing the request
        $stmt = $dbconn->query($request);
        
        // Getting the result
        $result = $stmt->fetchAll();
        
        // Returing the result
        return $result[0]['nbCommand'];
    }
}
