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
 *
 */


namespace CentreonConfiguration\Models;

/**
 * Used for interacting with hosts
 *
 * @author sylvestre
 */
class Hosttemplate extends \Centreon\Models\CentreonBaseModel
{
    protected static $table = "host";
    protected static $primaryKey = "host_id";
    protected static $uniqueLabelField = "host_name";
    protected static $relations = array(
        "\CentreonConfiguration\Models\Relation\Host\Contactgroup",
        "\CentreonConfiguration\Models\Relation\Host\Contact",
        "\CentreonConfiguration\Models\Relation\Host\Hostgroup",
        "\CentreonConfiguration\Models\Relation\Host\Poller",
        "\CentreonConfiguration\Models\Relation\Host\Hostcategory",
        "\CentreonConfiguration\Models\Relation\Host\Service",
        "\CentreonConfiguration\Models\Relation\Host\Hostparent",
        "\CentreonConfiguration\Models\Relation\Host\Hostchild"
    );

    /**
     * Deploy services by host templates
     *
     * @param int $hostId
     * @param int $hostTemplateId
     */
    public static function deployServices($hostId, $hostTemplateId = null)
    {
        static $deployedServices = array();

        $db = \Centreon\Internal\Di::getDefault()->get('db_centreon');
        $hid = is_null($hostTemplateId) ? $hostId : $hostTemplateId;
        $services = \CentreonConfiguration\Models\Relation\Host\Service::getMergedParameters(
            array(),
            array('service_id', 'service_description', 'service_alias'),
            -1,
            0,
            null,
            'ASC',
            array(
                \CentreonConfiguration\Models\Relation\Host\Service::getFirstKey() => $hid
            ),
            'AND'
        );
        foreach ($services as $service) {
            if (is_null($hostTemplateId)) {
                $deployedServices[$hostId][$service['service_description']] =  true;
            } elseif (!isset($deployedServices[$hostId][$service['service_alias']])) {
                $serviceId = \CentreonConfiguration\Models\Service::insert(
                    array(
                        'service_description' => $service['service_alias'],
                        'service_template_model_stm_id' => $service['service_id'],
                        'service_register' => 1,
                        'service_activate' => 1
                    )
                );
                \CentreonConfiguration\Models\Relation\Host\Service::insert($hostId, $serviceId);
                $deployedServices[$hostId][$service['service_alias']] = true;
            }
        }
        $templates = \CentreonConfiguration\Models\Relation\Host\Hosttemplate::getTargetIdFromSourceId(
            'host_tpl_id',
            'host_host_id',
            $hid
        );
        foreach ($templates as $tplId) {
            self::deployServices($hostId, $tplId);
        }
    }
    
    public static function getList(
        $parameterNames = "*",
        $count = -1,
        $offset = 0,
        $order = null,
        $sort = "ASC",
        $filters = array(),
        $filterType = "OR"
    ) {
        $filters['host_register'] = '0';
        return parent::getList($parameterNames, $count, $offset, $order, $sort, $filters, $filterType);
    }
}
