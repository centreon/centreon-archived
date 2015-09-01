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

namespace CentreonBam\Repository;

use CentreonMain\Repository\FormRepository;
use Centreon\Internal\Di;
use CentreonConfiguration\Models\VirtualHost;
use CentreonConfiguration\Models\VirtualService;
use CentreonBam\Models\AclresourceBusinessActivitiesParams;
use CentreonBam\Models\Relation\Aclresource\BusinessActivity as AclresourceBusinessactivityRelation;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package CentreonBam
 * @subpackage Repository
 */
class BusinessActivityRepository extends FormRepository
{
    
    
    public static $objectClass = '\CentreonBam\Models\BusinessActivity';
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array('bam' => 'cfg_bam, ba_id, name'
        ),
    );

    /**
     * Generic create action
     *
     * @param array $givenParameters
     * @return int id of created object
     */
    public static function create($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        $id = parent::create($givenParameters, $origin, $route, $validate, $validateMandatory);

        self::createVirtualService($id);

        return $id;
    }

    /**
     * Delete an object
     *
     * @param array $ids | array of ids to delete
     */
    public static function delete($ids)
    {
        parent::delete($ids);

        self::deleteVirtualService($ids);
    }
    
    /**
     * 
     * @param string $name
     * @return string
     */
    public static function getIconImage($name)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        
        $finalRoute = "";
        
        $stmt = $dbconn->prepare(
            "SELECT b.filename "
            . "FROM cfg_bam ba, cfg_binaries b "
            . "WHERE ba.name = :baName "
            . "AND ba.icon_id = b.binary_id "
        );
        $stmt->bindParam(":baName", $name, \PDO::PARAM_STR);
        $stmt->execute();
        $baIconResult = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!is_null($baIconResult['filename'])) {
            $filenameExploded = explode('.', $baIconResult['filename']);
            $nbOfOccurence = count($filenameExploded);
            $fileFormat = $filenameExploded[$nbOfOccurence-1];
            $filenameLength = strlen($baIconResult['filename']);
            $routeAttr = array(
                'image' => substr($baIconResult['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                'format' => '.'.$fileFormat
            );
            $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
            $finalRoute .= '<img src="'.$imgSrc.'" style="width:16px;height:16px;">&nbsp;';
        } elseif (is_null($baIconResult['filename'])/* && !is_null($tplResult['host_tpl_id'])*/) {
            $finalRoute .= "<i class='icon-BAM ico-16'></i>&nbsp;";
        }
        
        return $finalRoute;
    }

    /**
     *
     *
     * @return string
     */
    public static function getIndicatorsForBa($id)
    {
        // Get datatabases connections
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $sql = "SELECT k.kpi_id "
            . "FROM cfg_bam_kpi k "
            . "WHERE k.id_ba= :id";

        $stmt = $dbconn->prepare($sql);
        $stmt->bindValue(":id", $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $resultIndicators = array();
        foreach ($result as $indicator) {
            array_push($resultIndicators, $indicator);
        }

        return $resultIndicators;
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public static function getBaList()
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        #$router = $di->get('router');

        $baList = static::getList("ba_id,name", -1, 0, null, "ASC", array('ba_type_id' => 2));

        return $baList;
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public static function getBuList()
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        #$router = $di->get('router');

        $buList = static::getList("ba_id,name", -1, 0, null, "ASC", array('ba_type_id' => 1));

        return $buList;
    }

    /**
     *
     *
     * @return $id
     */
    public static function getVirtualHost()
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $selectRequest = "SELECT host_id"
            . " FROM cfg_hosts"
            . " WHERE host_name='_Module_BAM'";
        $stmtSelect = $dbconn->prepare($selectRequest);
        $stmtSelect->bindParam(':id', $hostId, \PDO::PARAM_INT);
        $stmtSelect->execute();
        $result = $stmtSelect->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($result[0]['host_id'])) {
            return $result[0]['host_id'];
        } else {
            $id = static::createVirtualHost();
            return $id;
        }
    }

    /**
     *
     * 
     * @return $id
     */
    public static function createVirtualHost()
    {
        $virtualHost = array(
            'host_name' => '_Module_BAM',
            'organization_id' => 1
        );

        $id = VirtualHost::insert($virtualHost);

        return $id;
    }

    /**
     *
     * @param string $id
     * 
     */
    public static function createVirtualService($id)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $virtualService = array(
            'service_description' => 'ba_' . $id,
            'organization_id' => 1
        );

        $hostId = static::getVirtualHost();

        $dbconn->beginTransaction();

        $serviceId = VirtualService::insert($virtualService);

        $insertRelationRequest = "INSERT INTO cfg_hosts_services_relations(host_host_id, service_service_id)"
            . " VALUES(:host_id, :service_id)";
        $stmtRelationInsert = $dbconn->prepare($insertRelationRequest);
        $stmtRelationInsert->bindParam(':host_id', $hostId, \PDO::PARAM_INT);
        $stmtRelationInsert->bindParam(':service_id', $serviceId, \PDO::PARAM_INT);
        $stmtRelationInsert->execute();

        $dbconn->commit();
    }

    /**
     *
     * @param string $id
     *
     */
    public static function deleteVirtualService($ids)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $dbconn->beginTransaction();

        foreach ($ids as $id) {
            $serviceDescription = 'ba_' . $id;
            $selectRequest = "SELECT service_id"
            . " FROM cfg_services"
            . " WHERE service_description=:service_description";
            $stmtSelect = $dbconn->prepare($selectRequest);
            $stmtSelect->bindParam(':service_description', $serviceDescription, \PDO::PARAM_STR);
            $stmtSelect->execute();
            $result = $stmtSelect->fetchAll(\PDO::FETCH_ASSOC);

            if (isset($result[0]['service_id'])) {
                VirtualService::delete($result[0]['service_id']);
            }
        }

        $dbconn->commit();
    }

    /**
     * update Business activity acl
     *
     * @param string $action
     * @param int $objectId
     * @param array $baIds
     */
    public static function updateBusinessActivityAcl($action, $objectId, $baIds)
    {
        if ($action === 'update') {
            AclresourceBusinessactivityRelation::delete($objectId);
            foreach ($baIds as $baId) {
                AclresourceBusinessactivityRelation::insert($objectId, $baId);
            }
        }
    }

    /**
     * get Hosts by acl id
     *
     * @param int $aclId
     */
    public static function updateAllBusinessActivitiesAcl($action, $objectId, $allBas)
    {
        if (($action === 'create') || ($action === 'update')) {
            try {
                AclresourceBusinessActivitiesParams::delete($objectId);
            } catch (\Exception $e) {

            }
            AclresourceBusinessActivitiesParams::insert(array(
                "acl_resource_id" => $objectId,
                "all_business_activities" => $allBas
                ),
                true
            );
        }
    }

    /**
     * get Business activities by acl id
     *
     * @param int $aclId
     */
    public static function getBusinessActivitiesByAclResourceId($aclId)
    {
        $baList = AclresourceBusinessactivityRelation::getMergedParameters(
            array(),
            array('ba_id', 'name'),
            -1,
            0,
            null,
            "ASC",
            array('cfg_acl_resources_bas_relations.acl_resource_id' => $aclId),
            "AND"
        );

        $finalBaList = array();
        foreach ($baList as $ba) {
            $finalBaList[] = array(
                "id" => $ba['ba_id'],
                "text" => $ba['name']
            );
        }

        return $finalBaList;
    }
    
    public static function formatDataForHeader($data){
        
    }
    
}
