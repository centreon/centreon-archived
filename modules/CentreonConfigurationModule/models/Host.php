<?php
/*
 * Copyright 2005-2014 CENTREON
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

namespace CentreonConfiguration\Models;

use Centreon\Internal\Di;
use Centreon\Models\CentreonBaseModel;
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Relation\Host\Service as HostServiceRelation;
use CentreonConfiguration\Models\Relation\Host\Hosttemplate as HostHosttemplateRelation;

/**
 * Used for interacting with hosts
 *
 * @author sylvestre
 */
class Host extends CentreonBaseModel
{
    protected static $table = "cfg_hosts";
    protected static $primaryKey = "host_id";
    protected static $uniqueLabelField = "host_name";
    protected static $relations = array(
        "\CentreonConfiguration\Models\Relation\Host\Hostgroup",
        "\CentreonConfiguration\Models\Relation\Host\Hostcategory",
        "\CentreonConfiguration\Models\Relation\Host\Service",
        "\CentreonConfiguration\Models\Relation\Host\Hostparents",
        "\CentreonConfiguration\Models\Relation\Host\Hostchildren"
    );
    
    /**
     * Used for inserting object into database
     *
     * @param array $params
     * @return int
     */
    public static function insert($params = array())
    {
        $params['host_register'] = '1';
        $db = Di::getDefault()->get('db_centreon');
        $sql = "INSERT INTO " . static::$table;
        $sqlFields = "";
        $sqlValues = "";
        $sqlParams = array();
        $not_null_attributes = array();
        $is_int_attribute = array();
        static::setAttributeProps($params, $not_null_attributes, $is_int_attribute);

        foreach ($params as $key => $value) {
            if ($key == static::$primaryKey || is_null($value)) {
                continue;
            }
            if ($sqlFields != "") {
                $sqlFields .= ",";
            }
            if ($sqlValues != "") {
                $sqlValues .= ",";
            }
            $sqlFields .= $key;
            $sqlValues .= "?";
            if ($value == "" && !isset($not_null_attributes[$key])) {
                $value = null;
            } elseif (!is_numeric($value) && isset($is_int_attribute[$key])) {
                $value = null;
            }
            $type = \PDO::PARAM_STR;
            if (is_null($value)) {
                $type = \PDO::PARAM_NULL;
            }
            $sqlParams[] = array('value' => trim($value), 'type' => $type);
            
            
            // Custom macros
            
            
            
        }
        if ($sqlFields && $sqlValues) {
            $sql .= "(".$sqlFields.") VALUES (".$sqlValues.")";
            $stmt = $db->prepare($sql);
            $i = 1;
            foreach ($sqlParams as $v) {
                $stmt->bindValue($i, $v['value'], $v['type']);
                $i++;
            }
            $stmt->execute();
            return $db->lastInsertId(static::$table, static::$primaryKey);
        }
        return null;
    }

    /**
     * Deploy services by host templates
     *
     * @param int $hostId
     * @param int $hostTemplateId
     */
    public static function deployServices($hostId, $hostTemplateId = null)
    {
        static $deployedServices = array();

        $db = Di::getDefault()->get('db_centreon');
        $hid = is_null($hostTemplateId) ? $hostId : $hostTemplateId;
        $services = HostServiceRelation::getMergedParameters(
            array(),
            array('service_id', 'service_description', 'service_alias'),
            -1,
            0,
            null,
            'ASC',
            array(
                HostServiceRelation::getFirstKey() => $hid
            ),
            'AND'
        );
        foreach ($services as $service) {
            if (is_null($hostTemplateId)) {
                $deployedServices[$hostId][$service['service_description']] =  true;
            } elseif (!isset($deployedServices[$hostId][$service['service_alias']])) {
                $serviceId = Service::insert(
                    array(
                        'service_description' => $service['service_alias'],
                        'service_template_model_stm_id' => $service['service_id'],
                        'service_register' => 1,
                        'service_activate' => 1
                    )
                );
                HostServiceRelation::insert($hostId, $serviceId);
                $deployedServices[$hostId][$service['service_alias']] = true;
            }
        }
        $templates = HostHosttemplateRelation::getTargetIdFromSourceId(
            'host_tpl_id',
            'host_host_id',
            $hid
        );
        foreach ($templates as $tplId) {
            self::deployServices($hostId, $tplId);
        }
    }
    
    /**
     * 
     * @param type $parameterNames
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param array $filters
     * @param type $filterType
     * @return type
     */
    public static function getList(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR",
        $tablesString = null,
        $staticFilter = null,
        $aAddFilters  = array()
    ) {
        $filters['host_register'] = '1';
        if (is_array($filterType)) {
            $filterType['host_register'] = 'AND';
        } else {
            $filterType = array(
                '*' => $filterType,
                'host_register' => 'AND'
            );
        }
        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, null, null, $aAddFilters);
    }
    
    /**
     * 
     * @param type $parameterNames
     * @param type $count
     * @param type $offset
     * @param type $order
     * @param type $sort
     * @param array $filters
     * @param type $filterType
     * @return type
     */
    public static function getListBySearch(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR"
    ) {
        $aAddFilters = array();
        $tablesString =  '';
        
        $filters['host_register'] = '1';
        if (is_array($filterType)) {
            $filterType['host_register'] = 'AND';
        } else {
            $filterType = array(
                '*' => $filterType,
                'host_register' => 'AND'
            );
        }
                
        if (array('tagname', array_values($filters)) && !empty($filters['tagname'])) {
            $aAddFilters = array(
                'tables' => array('cfg_tags', 'cfg_tags_hosts'),
                'join'   => array('cfg_tags.tag_id = cfg_tags_hosts.tag_id', 'cfg_tags.tag_id = cfg_tags_hosts.tag_id',
                    'cfg_tags_hosts.resource_id=cfg_hosts.host_id ')
            ); 
        }
        
        return parent::getListBySearch($parameterNames, $count, $offset, $order, $sort, $filters, $filterType, $tablesString, null, $aAddFilters);
    }
}
