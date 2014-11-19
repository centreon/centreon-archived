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

namespace CentreonAdministration\Repository;

use CentreonAdministration\Models\Domain;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class DomainRepository extends \CentreonAdministration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_domains';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Domain';
    
    /**
     * Generic create action
     *
     * @param array $givenParameters
     * @return int id of created object
     */
    public static function create($givenParameters)
    {
        $givenParameters['parent_id'] = Domain::getIdByParameter('name', array('Application'));
        $givenParameters['isroot'] = 0;
        parent::create($givenParameters);
    }
    
    /**
     * 
     * @param string $domain
     * @param boolean $withChildren
     * @return array
     */
    public static function getDomain($domain, $withChildren = false)
    {
        $domainList = array();
        $mainDomainId = Domain::getIdByParameter('name', array($domain));
        if (count($mainDomainId) > 0) {
            $domainList[] = Domain::get($mainDomainId[0]);
            if ($withChildren) {
                array_merge($domainList, Domain::getList('*', -1, 0, null, 'ASC', array('parent_id' => $mainDomainId[0]))); 
            }
        }
        return $domainList;
    }
    
    /**
     * 
     * @param type $hostId
     * @param type $domain
     * @param type $withChildren
     * @return type
     */
    public static function getServicesForDomain($hostId, $domain, $withChildren = false)
    {
        $domainList = self::getDomain($domain, $withChildren);
        $allServices = array();
        foreach($domainList as $domain) {
            $allServices = array_merge(
                $allServices,
                ServiceRepository::getServicesByDomainForHost($hostId, $domain['name'])
            );
        }
        return $allServices;
    }
    
    /**
     * 
     * @param array $metricList
     * @return array
     */
    public static function normalizeMetricsForNetwork($metricList)
    {
        $normalizeMetricSet = array();
        
        $in = $metricList['traffic_in'];
        $out = $metricList['traffic_out'];
        
        $normalizeMetricSet['in'] = $in['current_value'] . ' ' . $in['unit_name'];
        $normalizeMetricSet['out'] = $out['current_value'] . ' ' . $out['unit_name'];
        $normalizeMetricSet['status'] = '';
        
        return $normalizeMetricSet;
    }
}
