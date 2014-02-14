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
class UserRepository extends \Centreon\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'contact';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'User';
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allContact" class="allContact" type="checkbox">' => 'contact_id',
        'Alias / Login' => 'contact_name',
        'Full name' => 'contact_alias',
        'Email' => 'contact_email',
        'Notifications Period' => array(
            'Hosts' => 'contact_host_notification_options',
            'Services' => 'contact_service_notification_options'
        ),
        'Language' => 'contact_lang',
        'Access' => 'contact_oreon',
        'Admin' => 'contact_admin',
        'Status' => 'contact_activate'
    );
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $researchIndex = array(
        'contact_id',
        'contact_name',
        'contact_alias',
        'contact_email',
        'contact_host_notification_options',
        'contact_service_notification_options',
        'contact_lang',
        'contact_oreon',
        'contact_admin',
        'contact_activate'
    );
    
    public static $specificConditions = "contact_register = '1' ";
    
    public static $columnCast = array(
        'contact_id' => array(
            'type' => 'checkbox',
            'parameters' => array()
        ),
        'contact_admin' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">No</span>',
                '1' => '<span class="label label-success">Yes</span>'
            )
        ),
        'contact_oreon' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>'
            )
        ),
        'contact_activate' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>'
            )
        ),
        'contact_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/user/[i:id]',
                'routeParams' => array(
                    'id' => '::contact_id::'
                ),
                'linkName' => '::contact_name::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'text',
        'text',
        'text',
        'none',
        'none',
        'text',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        ),
        array(
            'select' => array(
                'Yes' => '1',
                'No' => '0'
            )
        ),
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'text',
        'text',
        'text',
        'none',
        'none',
        'text',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        ),
        array(
            'select' => array(
                'Yes' => '1',
                'No' => '0'
            )
        ),
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        )
    );
    
    
    public static function formatDatas(&$resultSet)
    {
        foreach ($resultSet as &$myUserSet) {
            $myUserSet['contact_host_notification_options'] = self::getNotificationInfos(
                $myUserSet['contact_id'],
                'host'
            );
            $myUserSet['contact_service_notification_options'] = self::getNotificationInfos(
                $myUserSet['contact_id'],
                'service'
            );
            if ($myUserSet['contact_email'] != "") {
                $myUserSet['contact_name'] = "<img src='http://www.gravatar.com/avatar/".
                    md5($myUserSet['contact_email']).
                    "?rating=PG&size=16&default=' class='img-circle'>&nbsp;".
                    $myUserSet['contact_name'];
            } else {
                $myUserSet['contact_name'] = "<i class='fa fa-user'></i>&nbsp;".$myUserSet['contact_name'];
            }
        }
    }
    
    public static function getNotificationInfos($contactId, $object)
    {
        // Initializing connection
        $di = \Centreon\Core\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        if ($object == 'host') {
            $ctp = 'timeperiod_tp_id';
        } elseif ($object == 'service') {
            $ctp = 'timeperiod_tp_id2';
        }
        
        $query = "SELECT tp_name, contact_".$object."_notification_options "
            . "FROM contact, timeperiod "
            . "WHERE contact_id='$contactId' "
            . "AND tp_id = $ctp" ;
        
        $stmt = $dbconn->query($query);
        $resultSet = $stmt->fetch();
        
        if ($resultSet === false) {
            $return = '';
        } else {
            $return = $resultSet['tp_name'].' ('.$resultSet['contact_'.$object.'_notification_options'].')';
        }
        
        return $return;
    } 
}
