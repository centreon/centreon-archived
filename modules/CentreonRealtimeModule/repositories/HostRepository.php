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

namespace CentreonRealtime\Repository;

use Centreon\Internal\Di;
use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Utils\Status as UtilStatus;
use CentreonRealtime\Repository\Repository;
use Centreon\Internal\Utils\YesNoDefault;
use CentreonConfiguration\Models\Command;
use CentreonConfiguration\Models\Timeperiod;

/**
 * @author Julien Mathis <jmathis@centreon.com>
 * @package CentreonRealtime
 * @subpackage Repository
 */
class HostRepository extends Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'rt_hosts';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Host';

    /**
     *
     * @var string
     */
    public static $objectId = 'host_id';

    /**
     *
     * @var string
     */
    public static $hook = 'displayHostRtColumn';
    
    /**
     * Format data so that it can be displayed in slider
     *
     * @param array $data
     * @return array $checkdata
     */
    public static function formatDataForHeader($data)
    {
        /* Check data */
        
        
        
        $checkdata = array();
        $checkdata[_('id')] = $data['host_id'];
        if(!empty($data['host_name'])){
            $checkdata[_('name')] = $data['host_name'];
        }else if(!empty($data['name'])){
            $checkdata[_('name')] = $data['name'];
        }
        
        $checkdata[_('state')] = $data['state'];
      
        $checkdata[_('icon')] = "";
        if (!empty($data['icon'])) {
            $checkdata[_('icon')] = $data['icon'];
        }
        
        $checkdata[_('url')] = "";
        if (!empty($data['url'])) {
            $checkdata[_('url')] = $data['url'];
        }
        
        $checkdata[_('issue_duration')] = "";
        if (!empty($data['issue_duration'])) {
            $checkdata[_('issue_duration')] = $data['issue_duration'];
        }
        
        $checkdata[_('states')] = "";
        if (!empty($data['states'])) {
            $checkdata[_('states')] = $data['states'];
        }
        
        return $checkdata;
    }
    
    /**
     * Format data so that it can be displayed in slider
     *
     * @param array $data
     * @return array $checkdata
     */
    public static function formatDataForSlider($data)
    {
        /* Check data */
        $checkdata = array();
        $checkdata[_('id')] = $data['configurationData']['host_id'];
        $checkdata[_('name')] = $data['configurationData']['host_name'];

        $checkdata[_('command')] = "";
        if (isset($data['configurationData']['command_command_id']) && !is_null($data['configurationData']['command_command_id'])) {
            $checkdata[_('command')] = Command::getParameters($data['configurationData']['command_command_id'], 'command_name');
        }

        $checkdata[_('time_period')] = "";
        if (isset($data['configurationData']['timeperiod_tp_id']) && !is_null($data['configurationData']['timeperiod_tp_id'])) {
            $checkdata[_('time_period')] = Timeperiod::getParameters($data['configurationData']['timeperiod_tp_id'], 'tp_name');
        }

        $checkdata[_('max_check attempts')] = "";
        if(isset($data['configurationData']['host_max_check_attempts'])){
            $checkdata[_('max_check attempts')] = $data['configurationData']['host_max_check_attempts'];
        }

        $checkdata[_('check_interval')] = "";
        if(isset($data['configurationData']['host_check_interval'])){
            $checkdata[_('check_interval')] = $data['configurationData']['host_check_interval'];
        }

        $checkdata[_('retry_check_interval')] = "";
        if(isset($data['configurationData']['host_retry_check_interval'])){
            $checkdata[_('retry_check_interval')] = $data['configurationData']['host_retry_check_interval'];
        }

        $checkdata[_('active_checks_enabled')] = "";
        if(isset($data['configurationData']['host_active_checks_enabled'])){
            $checkdata[_('active_checks_enabled')] = YesNoDefault::toString($data['configurationData']['host_active_checks_enabled']);
        }

        $checkdata[_('passive_checks_enabled')] = "";
        if(isset($data['configurationData']['host_passive_checks_enabled'])){
            $checkdata[_('passive_checks_enabled')] = YesNoDefault::toString($data['configurationData']['host_passive_checks_enabled']);
        }

        if(!empty($data['configurationData']['icon'])){
            $checkdata[_('icon')] = $data['configurationData']['icon'];
        }

        $checkdata[_('state')] = "";
        if(!empty($data['realtimeData']['state'])){
            $checkdata[_('state')] = $data['realtimeData']['state'];
        }

        $checkdata[_('last_check')] = "";
        if(!empty($data['realtimeData']['last_check'])){
            $checkdata[_('last_check')] = $data['realtimeData']['last_check'];
        }

        $checkdata[_('next_check')] = "";
        if(!empty($data['realtimeData']['next_check'])){
            $checkdata[_('next_check')] = $data['realtimeData']['next_check'];
        }

        $checkdata[_('acknowledged')] = "";
        if(!empty($data['realtimeData']['acknowledged'])){
            $checkdata[_('acknowledged')] = $data['realtimeData']['acknowledged'];
        }

        $checkdata[_('downtime')] = "";
        if(!empty($data['realtimeData']['scheduled_downtime_depth'])){
            $checkdata[_('downtime')] = $data['realtimeData']['scheduled_downtime_depth'];
        }

        return $checkdata;
    }
    
    
    
    
    /**
     * Get host status
     *
     * @param int $hostId
     * @return mixed
     */
    public static function getStatus($hostId)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $stmt = $dbconn->prepare('SELECT state as state FROM rt_hosts WHERE host_id = ? AND enabled = 1 LIMIT 1');
        $stmt->execute(array($hostId));

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row['state'];
        }
        return -1;
    }
    
    /**
     * Get host acknowledgement information
     *
     * @param int $host_id
     * @return array
     */
    public static function getAcknowledgementInfos($host_id)
    {
        $acknowledgement = array();
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $stmt = $dbconn->prepare('SELECT acknowledgement_id, entry_time, author, comment_data
            FROM rt_acknowledgements
            WHERE host_id = ?
            AND service_id IS NULL
            AND deletion_time IS NULL');

        $stmt->execute(array($host_id));

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $acknowledgement = $row;
        }

        return $acknowledgement;
    }
    
    /**
     * 
     * @param type $hostId
     * @return type
     */
    public static function getHostShortInfo($hostId)
    {
        $finalInfo = array();
        
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $stmt = $dbconn->prepare('SELECT '
            . 'state as state, output as output, last_state_change as lastChange, '
            . 'last_check as lastCheck, next_check as nextCheck '
            . 'FROM rt_hosts WHERE host_id = ? LIMIT 1');
        $stmt->execute(array($hostId));
        
        $infos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (count($infos) > 0) {
            $finalInfo['status'] = strtolower(UtilStatus::numToString($infos[0]['state'], UtilStatus::TYPE_HOST));
            $finalInfo['output'] = $infos[0]['output'];
            $finalInfo['lastChange'] = $infos[0]['lastChange'];
            $finalInfo['lastCheck'] = $infos[0]['lastCheck'];
            $finalInfo['nextCheck'] = $infos[0]['nextCheck'];
        } else {
            $finalInfo['status'] = -1;
            $finalInfo['output'] = '';
            $finalInfo['lastChange'] = '';
            $finalInfo['lastCheck'] = '';
            $finalInfo['nextCheck'] = '';
        }
        
        
        return $finalInfo;
    }

    /**
     * Format small badge status
     *
     * @param int $status
     */
    public static function getStatusBadge($status)
    {
        switch ($status) {
            case 0:
                $status = "label-success";
                break;
            case 1:
                $status = "label-danger";
                break;
            case 2:
                $status = "label-default";
                break;
            default:
                $status = "";
                break;
        }
        return "<span class='patchState $status'>"
            . "</span>";
    }
    
    public static function getImpactNbr($hostId = null, $serviceId = null){
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $serviceQuery = "";
        if(!empty($serviceId)){
            $serviceQuery = " and i.service_id = :service_id ";
        }
        $hostQuery = "";
        if(!empty($hostId)){
            $hostQuery = " and i.host_id = :host_id ";
        }
        
        
        
        $query = "SELECT i.issue_id, iis.child_id "
                . "FROM rt_issues i "
                . "INNER JOIN rt_services rs ON i.service_id = rs.service_id and i.host_id = rs.host_id "
                . "LEFT JOIN rt_issues_issues_parents iis ON i.issue_id = iis.parent_id "
                . "INNER JOIN rt_servicestateevents sse ON sse.host_id = i.host_id "
                . "AND sse.service_id = i.service_id AND sse.start_time >= i.start_time " 
                . "AND (sse.end_time is null OR sse.end_time <= i.end_time) "
                . "WHERE i.end_time is null ".$serviceQuery.$hostQuery
                . "AND iis.child_id is not null "; 
        $stmt = $dbconn->prepare($query);

        if(!empty($serviceId)){
            $stmt->bindParam(':service_id', $serviceId, \PDO::PARAM_INT);
        }
        if(!empty($hostId)){
            $stmt->bindParam(':host_id', $hostId, \PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $impact = 0;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $impact += self::getAllImpactNbr($row['issue_id']);
        }
        return $impact;
        
    }
    
    public static function getAllImpactNbr($issue_id){
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $query = "SELECT i.issue_id "
                . "FROM rt_issues_issues_parents iis "
                . "INNER JOIN rt_issues i ON i.issue_id = iis.parent_id "
                . "INNER JOIN rt_services rs ON i.service_id = rs.service_id and i.host_id = rs.host_id "
                . "INNER JOIN rt_servicestateevents sse ON sse.host_id = i.host_id and sse.service_id = i.service_id "
                . "and sse.start_time >= i.start_time "
                . "AND (sse.end_time is null OR sse.end_time <= i.end_time) "
                . "WHERE iis.parent_id = ? and i.end_time is null";  
        $stmt = $dbconn->prepare($query);
        var_dump($query);
        var_dump($issue_id);
        $stmt->execute(array($issue_id));
        $impact = 0;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $impact++;
            $impact += self::getAllImpactNbr($row['issue_id']);
        }
        return $impact;
        
    }
    
    
    
    /**
     * Get recursivly the first parent of a given issue
     *
     * @param array $node The issue node
     * @return array
     */
    public static function recursiveTree($node,$firstIssueId){
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $query = "SELECT iis.parent_id, i.*, sse.*,rs.description,FROM_UNIXTIME(i.start_time) as start_time, FROM_UNIXTIME(i.end_time) as end_time, ".$firstIssueId." as is_parent "
                . "FROM rt_issues_issues_parents iis "
                . "INNER JOIN rt_issues i ON i.issue_id = iis.parent_id "
                . "INNER JOIN rt_services rs ON i.service_id = rs.service_id and i.host_id = rs.host_id "
                . "INNER JOIN rt_servicestateevents sse ON sse.host_id = i.host_id and sse.service_id = i.service_id "
                . "AND sse.service_id = i.service_id and sse.start_time >= i.start_time "
                . "AND (sse.end_time is null OR sse.end_time <= i.end_time) "
                . "WHERE iis.child_id = ? and i.end_time is null"; 
        $stmt = $dbconn->prepare($query);
        $stmt->execute(array($node['issue_id']));
        $parent = array($node);
        $flag = false;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if(!$flag){
                $parent = array();
                $flag = true;
            }
            $tmp = self::recursiveTree($row,$firstIssueId);
            foreach($tmp as $tmp2){
                // is the parent already in the array ?
                if(!in_array($tmp2,$parent)){
                    $parent[] = $tmp2;
                }
            }
        }
        return $parent;
    }
    
    
    /**
     * Get the list of direct and parent incidents for a host or a host/service
     *
     * @param int $hostId The host ID
     * @param int $serviceId The Service ID
     * @return array
     */
    public static function getParentIncidentsFromHost($hostId,$serviceId=null){
        
        $serviceQuery = "";
        if(!empty($serviceId)){
            $serviceQuery = "and i.service_id = ?";
        }
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $queryServices = "SELECT i.*, sse.*,rs.description,FROM_UNIXTIME(i.start_time) as start_time, FROM_UNIXTIME(i.end_time) as end_time, 0 as is_parent "
                . "FROM rt_issues i "
                . "INNER JOIN rt_services rs ON i.service_id = rs.service_id and i.host_id = rs.host_id "
                . "INNER JOIN rt_servicestateevents sse ON sse.host_id = i.host_id "
                . "AND sse.service_id = i.service_id and sse.start_time >= i.start_time "
                . "AND (sse.end_time is null OR sse.end_time <= i.end_time) "
                . "WHERE i.host_id = ? ".$serviceQuery." and i.end_time is null"; 

        $stmt = $dbconn->prepare($queryServices);
        if(!empty($serviceId)){
            $stmt->execute(array($hostId,$service_id));
        }else{
            $stmt->execute(array($hostId));
        }
        
        //$issues = array();
        $issues['indirect_issues'] = array();
        $issues['direct_issues'] = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $finalParent = self::recursiveTree($row,$row['issue_id']);
            if($finalParent[0]['is_parent'] != 0){
                $row['parents'] = $finalParent;
                $issues['indirect_issues'][] = $row;
            }else{
                $issues['direct_issues'] = array_merge($issues['direct_issues'],$finalParent);
            }
        }
        return $issues;
    }
    
    
}
