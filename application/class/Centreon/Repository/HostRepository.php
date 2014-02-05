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
class HostRepository extends \Centreon\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'host';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Host';
    
    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allHost" type="checkbox">' => 'host_id',
        'Name' => 'host_name',
        'Description' => 'host_alias',
        'IP Address / DNS' => 'host_address',
        'Status' => 'host_activate'
    );
    
    public static $specificConditions = "host_register = '1' ";
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'search_name',
        'search_description',
        'search_address',
        array('select' => array(
                'Enabled' => '1',
                'Disabled' => '0',
                'Trash' => '2'
            )
        )
    );
    
    public static $columnCast = array(
        'host_activate' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => 'Disabled',
                '1' => 'Enabled',
                '2' => 'Trash',
        )
        ),
        'host_id' => array(
            'type' => 'checkbox',
            'parameters' => array()
        ),
        'host_name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/host/update',
                'routeParams' => array(
                    'id' => '::host_id::'
                ),
                'linkName' => '::host_name::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'search_name',
        'search_description',
        'search_address',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0',
                'Trash' => '2'
            )
        )
    );
    
    public static function getMyHostExtendedInfoImage($host_id = null, $field, $flag1stLevel = null, $antiLoop = null)
    {
		global $pearDB, $oreon;

		if (!$host_id) {
			return;
        }

		if (isset($flag1stLevel) && $flag1stLevel) {
			$rq = "SELECT ehi.`".$field."` " .
					"FROM extended_host_information ehi " .
					"WHERE ehi.host_host_id = '".$host_id."' LIMIT 1";
			$DBRESULT = $pearDB->query($rq);
			$row = $DBRESULT->fetchRow();
			if (isset($row[$field]) && $row[$field])	{
				$DBRESULT2 = $pearDB->query("SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr WHERE vi.img_id = '".$row[$field]."' AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1");
				$row2 = $DBRESULT2->fetchRow();
				if (isset($row2["dir_alias"]) && isset($row2["img_path"]) && $row2["dir_alias"] && $row2["img_path"]) {
					return $row2["dir_alias"]."/".$row2["img_path"];
                }
			} else {
				if ($result_field = getMyHostExtendedInfoImage($host_id, $field)) {
					return $result_field;
				}
			}
			return null;
		} else {
			$rq = "SELECT host_tpl_id " .
				"FROM host_template_relation " .
				"WHERE host_host_id = '".$host_id."' " .
				"ORDER BY `order`";
			$DBRESULT = $pearDB->query($rq);
			while ($row = $DBRESULT->fetchRow()) {
				$rq2 = "SELECT ehi.`".$field."` " .
						"FROM extended_host_information ehi " .
						"WHERE ehi.host_host_id = '".$row['host_tpl_id']."' LIMIT 1";
				$DBRESULT2 = $pearDB->query($rq2);
				$row2 = $DBRESULT2->fetchRow();
				if (isset($row2[$field]) && $row2[$field])	{
					$DBRESULT3 = $pearDB->query("SELECT img_path, dir_alias FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr WHERE vi.img_id = '".$row2[$field]."' AND vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id LIMIT 1");
					$row3 = $DBRESULT3->fetchRow();
					if (isset($row3["dir_alias"]) && isset($row3["img_path"]) && $row3["dir_alias"] && $row3["img_path"])
						return $row3["dir_alias"]."/".$row3["img_path"];
				} else {
					if (isset($antiLoop) && $antiLoop) {
					    if ($antiLoop != $row['host_tpl_id']) {
    					    if ($result_field = getMyHostExtendedInfoImage($row['host_tpl_id'], $field, null, $antiLoop)) {
        						return $result_field;
        				    }
					    }
					} else {
					    if ($result_field = getMyHostExtendedInfoImage($row['host_tpl_id'], $field, null, $row['host_tpl_id'])) {
    						return $result_field;
    				    }
					}
				}
			}
			return null;
		}
	}
    
}
