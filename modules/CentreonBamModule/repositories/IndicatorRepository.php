<?php
/*
 * Copyright 2005-2015 CENTREON
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
 */

namespace CentreonBam\Repository;

use Centreon\Internal\Di;
use CentreonMain\Repository\FormRepository;
use CentreonBam\Models\BooleanIndicator;
use CentreonBam\Models\Indicator;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Repository\HostRepository;
use Centreon\Internal\CentreonSlugify;
/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package CentreonBam
 * @subpackage Repository
 */
class IndicatorRepository extends FormRepository
{
    
    /**
     * kpi_type = 0 ==> Service
     * kpi_type = 1 ==> MetaService
     * kpi_type = 2 ==> BA
     * kpi_type = 3 ==> boolean
     */

    /**
     *
     * @param int $id
     * @return string
     */
    public static function getIndicatorType($id)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $selectRequest = "SELECT k.kpi_type"
            . " FROM cfg_bam_kpi k"
            . " WHERE k.kpi_id=:id";
        $stmtSelect = $dbconn->prepare($selectRequest);
        $stmtSelect->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmtSelect->execute();
        $result = $stmtSelect->fetchAll(\PDO::FETCH_ASSOC);

        $typeId = $result[0]['kpi_type'];

        return $typeId;
    }

    /**
     *
     * @param int $id
     * @return array
     */
    public static function getBooleanParameters($id)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $selectRequest = "SELECT k.kpi_type, b.name, b.expression, b.bool_state"
            . " FROM cfg_bam_kpi k, cfg_bam_boolean b"
            . " WHERE k.kpi_id=:id and k.boolean_id=b.boolean_id";
        $stmtSelect = $dbconn->prepare($selectRequest);
        $stmtSelect->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmtSelect->execute();
        $result = $stmtSelect->fetchAll(\PDO::FETCH_ASSOC);

        $booleanParameters = array();
        $booleanParameters['boolean_name'] = $result[0]['name'];
        $booleanParameters['boolean_expression'] = $result[0]['expression'];
        $booleanParameters['bool_state'] = $result[0]['bool_state'];

        return $booleanParameters;
    }
    
    /**
     *
     * @param string $givenParameters
     * @return string
     */
    public static function createIndicator($givenParameters, $origin, $route)
    {
        $givenParameters[static::ORGANIZATION_FIELD] = Di::getDefault()->get('organization');

        $parameters = array();
        foreach ($givenParameters as $k => $v) {
            $parameters[$k] = $v;
        }

        //if($parameters['kpi_type'] !== '1'){
            if($origin !== 'api'){
                if (is_a($givenParameters, '\Klein\DataCollection\DataCollection')) {
                    $givenParameters = $givenParameters->all();
                }
                $givenParameters['host_id'] = true;
            }
            self::validateForm($givenParameters, $origin, $route);
       // }
        $lastIndicatorId = self::createBasicIndicator($parameters);
        $booleanId = null;
        if ($parameters['kpi_type'] === '0') {
            if($origin === 'api'){
                //$parameters['service_id'] = $parameters['service_id'].'_'.$parameters['host_id'];
            }
            self::createServiceIndicator($lastIndicatorId, $parameters);
        } else if ($parameters['kpi_type'] === '1') {
            self::createMetaserviceIndicator($lastIndicatorId, $parameters);
        } else if ($parameters['kpi_type'] === '2') {
            self::createBaIndicator($lastIndicatorId, $parameters);
        } else if ($parameters['kpi_type'] === '3') {
            $booleanId = self::createBooleanIndicator($lastIndicatorId, $parameters);
        }
        return $booleanId;
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createBasicIndicator($parameters)
    {
        foreach ($parameters as $k => $v) {
            if ($k !== 'kpi_type' && $k !== 'drop_warning' && $k !== 'drop_critical' && $k !== 'drop_unknown' && $k !== 'id_ba' && $k !== 'organization_id') {
                unset($parameters[$k]);
            }
        }

        if (trim($parameters['id_ba']) == "") {
            unset($parameters['id_ba']);
        }
        $class = static::$objectClass;
        $lastIndicatorId = $class::insert($parameters);
        if (is_null($lastIndicatorId)) {
            throw new \Exception('Could not create object');
        }

        return $lastIndicatorId;
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createServiceIndicator($lastIndicatorId, $parameters)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        //list($serviceId, $hostId) = explode('_', $parameters['service_id']);
        $serviceId = $parameters['service_id'];
        $insertRequest = "UPDATE cfg_bam_kpi kpi "
            . " INNER JOIN cfg_hosts_services_relations hsr ON hsr.service_service_id = :service_id "
            . " INNER JOIN cfg_services s ON s.service_id = :service_id and s.service_register = '1'"
            . " SET kpi.host_id=hsr.host_host_id, kpi.service_id=:service_id"
            . " WHERE kpi_id=:kpi_id ";
        $stmtInsert = $dbconn->prepare($insertRequest);
        $stmtInsert->bindParam(':service_id', $serviceId, \PDO::PARAM_INT);
        $stmtInsert->bindParam(':kpi_id', $lastIndicatorId, \PDO::PARAM_INT);
        $stmtInsert->execute();
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createMetaserviceIndicator($lastIndicatorId, $parameters)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $insertRequest = "UPDATE cfg_bam_kpi"
            . " SET meta_id=:meta_id"
            . " WHERE kpi_id=:kpi_id";
        $stmtInsert = $dbconn->prepare($insertRequest);
        $stmtInsert->bindParam(':meta_id', $parameters['meta_id'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':kpi_id', $lastIndicatorId, \PDO::PARAM_INT);
        $stmtInsert->execute();
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createBaIndicator($lastIndicatorId, $parameters)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $insertRequest = "UPDATE cfg_bam_kpi"
            . " SET id_indicator_ba=:id_indicator_ba"
            . " WHERE kpi_id=:kpi_id";
        $stmtInsert = $dbconn->prepare($insertRequest);
        $stmtInsert->bindParam(':id_indicator_ba', $parameters['id_indicator_ba'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':kpi_id', $lastIndicatorId, \PDO::PARAM_INT);
        $stmtInsert->execute();
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createBooleanIndicator($lastIndicatorId, $parameters)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        $class = '\CentreonBam\Models\BooleanIndicator';
        $repoClass = '\CentreonBam\Repository\BooleanIndicatorRepository';
        
        $sSlug = $parameters['boolean_name'];
 
        $oSlugify = new CentreonSlugify($class, $repoClass);
        $sSlug = $oSlugify->slug($parameters['boolean_name']);
        
        $insertBooleanRequest = "INSERT INTO cfg_bam_boolean(name, expression, bool_state, slug)"
                . " VALUES(:boolean_name, :boolean_expression, :bool_state, :slug)";
        $stmtBooleanInsert = $dbconn->prepare($insertBooleanRequest);
        $stmtBooleanInsert->bindParam(':boolean_name', $parameters['boolean_name'], \PDO::PARAM_STR);
        $stmtBooleanInsert->bindParam(':slug', $sSlug, \PDO::PARAM_STR);
        $stmtBooleanInsert->bindParam(':boolean_expression', $parameters['boolean_expression'], \PDO::PARAM_INT);
        $stmtBooleanInsert->bindParam(':bool_state', $parameters['bool_state'], \PDO::PARAM_INT);
        $stmtBooleanInsert->execute();
        $lastBooleanId = $dbconn->lastInsertId('cfg_bam_boolean','boolean_id');

        $insertIndicatorRequest = "UPDATE cfg_bam_kpi"
            . " SET boolean_id=:boolean_id WHERE kpi_id = :kpi_id";
        $stmtIndicatorInsert = $dbconn->prepare($insertIndicatorRequest);
        $stmtIndicatorInsert->bindParam(':boolean_id', $lastBooleanId, \PDO::PARAM_INT);
        $stmtIndicatorInsert->bindParam(':kpi_id', $lastIndicatorId, \PDO::PARAM_INT);
        $stmtIndicatorInsert->execute();
        return $lastBooleanId;
        
    }

    /**
     * Generic update function
     *
     * @param array $givenParameters
     * @throws \Centreon\Internal\Exception
     */
    public static function updateIndicator($givenParameters, $origin = "", $route = "", $validateMandatory = true, $object = null)
    {
        self::validateForm($givenParameters, $origin, $route, $validateMandatory);

        switch ($origin){
            case 'api' : 
                $kpi = Indicator::getKpi($object);
                if(!empty($kpi)){
                    $givenParameters['object_id'] = $kpi['kpi_id'];
                    if(!isset($givenParameters['kpi_type'])){
                        $givenParameters['kpi_type'] = $kpi['kpi_type'];
                        if(isset($givenParameters['service_id']) && isset($givenParameters['host_id'])){
                            $givenParameters['service_id'] = $givenParameters['service_id'].'_'.$givenParameters['host_id'];
                        }else if(isset($kpi['service_id']) && !is_null($kpi['service_id'])){
                            $givenParameters['service_id'] = $kpi['service_id'].'_'.$kpi['host_id'];
                        }
                    }else{
                        if(isset($givenParameters['service_id']) && isset($givenParameters['host_id'])){
                            $givenParameters['service_id'] = $givenParameters['service_id'].'_'.$givenParameters['host_id'];
                        }
                    }
                    if($givenParameters['kpi_type'] == '3'){
                        if(!isset($givenParameters['boolean_name'])){
                            $givenParameters['boolean_name'] = $kpi['name'];
                        }
                        if(!isset($givenParameters['boolean_expression'])){
                            $givenParameters['boolean_expression'] = $kpi['expression'];
                        }
                        if(!isset($givenParameters['bool_state'])){
                            $givenParameters['bool_state'] = $kpi['bool_state'];
                        }
                    }
                }else{
                    throw new \Exception('This object is not defined');
                }
                break;
            case 'web' : 
                break;
            default :
                break;
        }
        
        $class = static::$objectClass;
        $pk = $class::getPrimaryKey();
        $givenParameters[$pk] = $givenParameters['object_id'];
        if (!isset($givenParameters[$pk])) {
            throw new \Exception('Primary key of object is not defined');
        }
        $db = Di::getDefault()->get('db_centreon');
        $id = $givenParameters[$pk];
        $columns = $class::getColumns();
        $updateValues = array();
        foreach ($givenParameters as $key => $value) {
            if (in_array($key, $columns)) {
                if (is_string($value)) {
                    $updateValues[$key] = trim($value);
                } else {
                    $updateValues[$key] = $value;
                }
            }
        }

        $relBooleanIndicator = '\CentreonBam\Models\BooleanIndicator';

        if ($givenParameters['kpi_type'] === '0') {
            $serviceHostId = explode('_',$updateValues['service_id']);
            $updateValues['service_id'] = $serviceHostId[0];
            $updateValues['host_id'] = $serviceHostId[1];
            $updateValues['boolean_id'] = null;
            $updateValues['id_indicator_ba'] = null;
            $updateValues['meta_id'] = null;
        } elseif ($givenParameters['kpi_type'] === '1') {
            $updateValues['host_id'] = null;
            $updateValues['service_id'] = null;
            $updateValues['boolean_id'] = null;
            $updateValues['id_indicator_ba'] = null;
        } elseif ($givenParameters['kpi_type'] === '2') {
            $updateValues['host_id'] = null;
            $updateValues['service_id'] = null;
            $updateValues['boolean_id'] = null;
            $updateValues['meta_id'] = null;
        } elseif ($givenParameters['kpi_type'] === '3') {
            $updateValues['host_id'] = null;
            $updateValues['service_id'] = null;
            $updateValues['meta_id'] = null;
            $updateValues['id_indicator_ba'] = null;
            $updateValuesBoolean = array();
            $updateValuesBoolean['expression'] = $givenParameters['boolean_expression'];
            $updateValuesBoolean['name'] = $givenParameters['boolean_name'];
            $updateValuesBoolean['bool_state'] = $givenParameters['bool_state'];
            
            $resultBoolean = BooleanIndicator::getIdByParameter('name', $updateValuesBoolean['name']);
            if (count($resultBoolean) > 0) {
                $iIdBoolean = $resultBoolean[0];
            }
            if (!isset($iIdBoolean) || (isset($iIdBoolean) && empty($iIdBoolean))) {
                $updateValues['boolean_id'] = $relBooleanIndicator::insert($updateValuesBoolean);
            } else {
                $updateValues['boolean_id'] = $iIdBoolean;
                $relBooleanIndicator::update($updateValues['boolean_id'], $updateValuesBoolean);
            }
        }

        if ($givenParameters['kpi_type'] !== '3') {
            $booleanId = $class::getParameters($givenParameters['kpi_id'], 'boolean_id');
            $class::update($id, $updateValues);
            if (isset($booleanId['boolean_id'])) {
                $relBooleanIndicator::delete($booleanId['boolean_id']);
            }
        } else {
            $class::update($id, $updateValues);
        }

        if (method_exists(get_called_class(), 'postSave')) {
            static::postSave($id, 'update', $givenParameters);
        }
    }
    
    
    public static function updateIndicatorConsole($object,$givenParameters, $origin = "", $route = "", $validateMandatory = true){

        self::validateForm($givenParameters, $origin, $route, $validateMandatory);
        $class = static::$objectClass;
        $db = Di::getDefault()->get('db_centreon');
        $kpi = Indicator::getKpi($object);
        $id = $kpi['kpi_id'];
        
        $columns = $class::getColumns();
        $updateValues = array();
        
        
        foreach ($givenParameters as $key => $value) {
            if (in_array($key, $columns)) {
                if (is_string($value)) {
                    $updateValues[$key] = trim($value);
                } else {
                    $updateValues[$key] = $value;
                }
            }
        }

        $relBooleanIndicator = '\CentreonBam\Models\BooleanIndicator';

        if ($kpi['kpi_type'] === '0') {
            $serviceHostId = explode('_',$updateValues['service_id']);
            $updateValues['service_id'] = $serviceHostId[0];
            $updateValues['host_id'] = $serviceHostId[1];
            $updateValues['boolean_id'] = null;
            $updateValues['id_indicator_ba'] = null;
            $updateValues['meta_id'] = null;
        } elseif ($kpi['kpi_type'] === '1') {
            $updateValues['host_id'] = null;
            $updateValues['service_id'] = null;
            $updateValues['boolean_id'] = null;
            $updateValues['id_indicator_ba'] = null;
        } elseif ($kpi['kpi_type'] === '2') {
            $updateValues['host_id'] = null;
            $updateValues['service_id'] = null;
            $updateValues['boolean_id'] = null;
            $updateValues['meta_id'] = null;
        } elseif ($kpi['kpi_type'] === '3') {
            $updateValues['host_id'] = null;
            $updateValues['service_id'] = null;
            $updateValues['meta_id'] = null;
            $updateValues['id_indicator_ba'] = null;
            $updateValuesBoolean = array();
            $updateValuesBoolean['expression'] = $givenParameters['boolean_expression'];
            $updateValuesBoolean['name'] = $givenParameters['boolean_name'];
            $updateValuesBoolean['bool_state'] = $givenParameters['bool_state'];
            
            $resultBoolean = BooleanIndicator::getIdByParameter('name', $updateValuesBoolean['name']);
            if (count($resultBoolean) > 0) {
                $iIdBoolean = $resultBoolean[0];
            }
            if (!isset($iIdBoolean) || (isset($iIdBoolean) && empty($iIdBoolean))) {
                $updateValues['boolean_id'] = $relBooleanIndicator::insert($updateValuesBoolean);
            } else {
                $updateValues['boolean_id'] = $iIdBoolean;
                $relBooleanIndicator::update($updateValues['boolean_id'], $updateValuesBoolean);
            }
        }

        if ($kpi['kpi_type'] !== '3') {
            $booleanId = $class::getParameters($kpi['kpi_id'], 'boolean_id');
            $class::update($id, $updateValues);
            if (isset($booleanId['boolean_id'])) {
                $relBooleanIndicator::delete($booleanId['boolean_id']);
            }
        } else {
            $class::update($id, $updateValues);
        }

        if (method_exists(get_called_class(), 'postSave')) {
            static::postSave($id, 'update', $givenParameters);
        }
    } 
    
    

    /**
     * Used for duplicating object
     *
     * @param int $sourceObjectId
     * @param int $duplicateEntries
     */
    public static function duplicate($listDuplicate)
    {
        $class = static::$objectClass;
        $db = Di::getDefault()->get('db_centreon');

        foreach ($listDuplicate as $objectId => $duplicateEntries) {
            $sourceParams = $class::getParameters($objectId, "*");
            if (false === $sourceParams) {
                throw new Exception($class::OBJ_NOT_EXIST);
            }
            $originalName = static::getIndicatorName($sourceParams['kpi_id']);
            unset($sourceParams['kpi_id']);

            $i = 1;
            /* Add the number for new entries */
            while ($i <= $duplicateEntries) {
                if ($sourceParams['kpi_type'] === '3') {
                    BooleanIndicator::duplicate($sourceParams['boolean_id']);
                    $booleanId = $db->lastInsertId('cfg_bam_boolean','boolean_id');
                    $sourceParams['boolean_id'] = $booleanId;
                }
                $class::insert($sourceParams);
                $i++;
            }
        }
    }

    /**
     * Get indicators name
     *
     * @return string
     */
    public static function getIndicatorsName($filterName = "", $baId = null)
    {
        // Get datatabases connections
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $sqlKpiService = 'SELECT k.kpi_id, h.host_name, s.service_id, s.service_description
            FROM cfg_hosts h, cfg_services s, cfg_hosts_services_relations hs, cfg_bam_kpi k
            WHERE s.service_id=k.service_id and hs.host_host_id=h.host_id and hs.service_service_id=s.service_id';
        $stmtKpiService = $dbconn->prepare($sqlKpiService);
        if (isset($filterName) && $filterName !== "") {
            $sqlKpiService .= ' AND CONCAT(h.host_name, " ", s.service_description) like :filter';
            $stmtKpiService = $dbconn->prepare($sqlKpiService);
            $stmtKpiService->bindParam(':filter', $filterName, \PDO::PARAM_STR);
        }
        if (isset($baId) && !is_null($baId)) {
            $sqlKpiService .= ' AND k.id_ba = :id';
            $stmtKpiService = $dbconn->prepare($sqlKpiService);
            $stmtKpiService->bindParam(':id', $baId, \PDO::PARAM_INT);
        }
        $stmtKpiService->execute();
        $resultKpiService = $stmtKpiService->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiMetaservice = 'SELECT k.kpi_id,ms.meta_id,ms.meta_name
            FROM cfg_meta_services ms,cfg_bam_kpi k
            WHERE ms.meta_id=k.meta_id';
        $stmtKpiMetaservice = $dbconn->prepare($sqlKpiMetaservice);
        if (isset($filterName) && $filterName !== "") {
            $sqlKpiMetaservice .= ' AND ms.meta_name like :filter';
            $stmtKpiMetaservice = $dbconn->prepare($sqlKpiMetaservice);
            $stmtKpiMetaservice->bindParam(':filter', $filterName, \PDO::PARAM_STR);
        }
        if (isset($baId) && !is_null($baId)) {
            $sqlKpiMetaservice .= ' AND k.id_ba = :id';
            $stmtKpiMetaservice = $dbconn->prepare($sqlKpiMetaservice);
            $stmtKpiMetaservice->bindParam(':id', $baId, \PDO::PARAM_INT);
        }
        $stmtKpiMetaservice->execute();
        $resultKpiMetaservice = $stmtKpiMetaservice->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBa = 'SELECT k.kpi_id,b.ba_id,b.name
            FROM cfg_bam b,cfg_bam_kpi k
            WHERE b.ba_id=k.id_indicator_ba';
        $stmtKpiBa = $dbconn->prepare($sqlKpiBa);
        if (isset($filterName) && $filterName !== "") {
            $sqlKpiBa .= ' AND b.name like :filter';
            $stmtKpiBa = $dbconn->prepare($sqlKpiBa);
            $stmtKpiBa->bindParam(':filter', $filterName, \PDO::PARAM_STR);
        }
        if (isset($baId) && !is_null($baId)) {
            $sqlKpiBa .= ' AND k.id_ba = :id';
            $stmtKpiBa = $dbconn->prepare($sqlKpiBa);
            $stmtKpiBa->bindParam(':id', $baId, \PDO::PARAM_INT);
        }        
        $stmtKpiBa->execute();
        $resultKpiBa = $stmtKpiBa->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBoolean = "SELECT k.kpi_id,b.boolean_id,b.name
            FROM cfg_bam_boolean b,cfg_bam_kpi k
            WHERE b.boolean_id=k.boolean_id";
        $stmtKpiBoolean = $dbconn->prepare($sqlKpiBoolean);
        if (isset($filterName) && $filterName !== "") {
            $sqlKpiBoolean .= ' AND b.name like :filter';
            $stmtKpiBoolean = $dbconn->prepare($sqlKpiBoolean);
            $stmtKpiBoolean->bindParam(':filter', $filterName, \PDO::PARAM_STR);
        }
        if (isset($baId) && !is_null($baId)) {
            $sqlKpiBoolean .= ' AND k.id_ba = :id';
            $stmtKpiBoolean = $dbconn->prepare($sqlKpiBoolean);
            $stmtKpiBoolean->bindParam(':id', $baId, \PDO::PARAM_INT);
        }
        $stmtKpiBoolean->execute();
        $resultKpiBoolean = $stmtKpiBoolean->fetchAll(\PDO::FETCH_ASSOC);

        $resultPki = array();
        foreach ($resultKpiService as $kpiObject) {
            $resultPki[] = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['host_name'].' '.$kpiObject['service_description']
            );
        }
        foreach ($resultKpiMetaservice as $kpiObject) {
            $resultPki[] = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['meta_id']
            );
        }
        foreach ($resultKpiBa as $kpiObject) {
            $resultPki[] = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['name']
            );
        }
        foreach ($resultKpiBoolean as $kpiObject) {
            $resultPki[] = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['name']
            );
        }

        return $resultPki;
    }

    /**
     *
     *
     * @return string
     */
    public static function getIndicatorName($id)
    {
        // Get datatabases connections
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        // Add object column
        // Can be service, metaservice or BA
        $sqlKpiService = 'SELECT k.kpi_id, h.host_name, s.service_id, s.service_description'
            . ' FROM cfg_hosts h, cfg_services s, cfg_hosts_services_relations hs, cfg_bam_kpi k'
            . ' WHERE s.service_id=k.service_id and hs.host_host_id=h.host_id and hs.service_service_id=s.service_id and k.kpi_id=:id';
        $stmtKpiService = $dbconn->prepare($sqlKpiService);
        $stmtKpiService->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmtKpiService->execute();
        $resultKpiService = $stmtKpiService->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiMetaservice = 'SELECT k.kpi_id,ms.meta_id'
            . ' FROM cfg_meta_services ms,cfg_bam_kpi k'
            . ' WHERE ms.meta_id=k.meta_id and k.kpi_id=:id';
        $stmtKpiMetaservice = $dbconn->prepare($sqlKpiMetaservice);
        $stmtKpiMetaservice->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmtKpiMetaservice->execute();
        $resultKpiMetaservice = $stmtKpiMetaservice->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBa = 'SELECT k.kpi_id,b.ba_id,b.name'
            . ' FROM cfg_bam b,cfg_bam_kpi k'
            . ' WHERE b.ba_id=k.id_indicator_ba and k.kpi_id=:id';
        $stmtKpiBa = $dbconn->prepare($sqlKpiBa);
        $stmtKpiBa->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmtKpiBa->execute();
        $resultKpiBa = $stmtKpiBa->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBoolean = 'SELECT k.kpi_id,b.boolean_id,b.name'
            . ' FROM cfg_bam_boolean b,cfg_bam_kpi k'
            . ' WHERE b.boolean_id=k.boolean_id and k.kpi_id=:id';
        $stmtKpiBoolean = $dbconn->prepare($sqlKpiBoolean);
        $stmtKpiBoolean->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmtKpiBoolean->execute();
        $resultKpiBoolean = $stmtKpiBoolean->fetchAll(\PDO::FETCH_ASSOC);

        $resultKpi = array();
        foreach ($resultKpiService as $kpiObject) {
            $resultKpi = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['host_name'].' '.$kpiObject['service_description']
            );
        }
        foreach ($resultKpiMetaservice as $kpiObject) {
            $resultKpi = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['meta_id']
            );
        }
        foreach ($resultKpiBa as $kpiObject) {
            $resultKpi = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['name']
            );
        }
        foreach ($resultKpiBoolean as $kpiObject) {
            $resultKpi = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['name']
            );
        }

        return $resultKpi;
    }

    /**
     * Delete an object
     *
     * @param array $ids | array of ids to delete
     */
    public static function delete($ids)
    {
        // Get datatabases connections
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $sqlKpiBoolean = "SELECT k.kpi_id, k.boolean_id "
            . "FROM cfg_bam_kpi k "
            . "WHERE k.kpi_type='3'";
        $stmtKpiBoolean = $dbconn->query($sqlKpiBoolean);
        $resultKpiBoolean = $stmtKpiBoolean->fetchAll(\PDO::FETCH_ASSOC);

        parent::delete($ids);

        foreach ($ids as $id) {
            foreach ($resultKpiBoolean as $kpiObject) {
                if ($kpiObject['kpi_id'] == $id) {
                    BooleanIndicator::delete($kpiObject['boolean_id']);
                }
            }
        }
    }
    /**
     * 
     * @param type $unicityParams
     * @param type $kpiType
     * @return type
     */
    public static function getIdFromUnicity($unicityParams, $kpiType)
    {
        $tables = array();
        $conditions = array();
        $objectId = 0;
        
        $db = Di::getDefault()->get('db_centreon');
        
        if ($kpiType == '0') {
            $unicityFields = array(        
                'fields' => array(
                    'serviceIndicator' => 'cfg_bam_kpi, kpi_id, service_id'
                ),
            );
        } else if ($kpiType == '2') {
            $unicityFields = array(        
                'fields' => array(
                    'baIndicator' => 'cfg_bam_kpi, kpi_id, id_indicator_ba'
                ),
            );
        } else if ($kpiType == '3') {
            $unicityFields = array(        
                'fields' => array(
                    'boolean' => 'cfg_bam_boolean, boolean_id, name'
                ),
            );
        }
        
        $sElement = "kpi_id";
        
        $query = 'SELECT ' . $sElement;
        
        // Checking unicity's params
        foreach ($unicityParams as $key => $unicityParam) {
            if (isset($unicityFields['fields'][$key])) {
                $fieldComponents = explode (',', $unicityFields['fields'][$key]);
                $tables[] = $fieldComponents[0];
                $conditions[] = $fieldComponents[2] . "='$unicityParam'";
            }
        }
        
        // Finalizing query
        $query .= ' FROM ' . implode(', ', $tables);
        if ($kpiType == '3' ) {
            $query .= ', cfg_bam_kpi';
        }
        $query .= ' WHERE ' . implode(' AND ', $conditions);
        if ($kpiType == '3' ) {
            $query .= ' AND cfg_bam_kpi.boolean_id = cfg_bam_boolean.boolean_id';
        }

        if (isset($unicityParams['id_ba']) && !empty($unicityParams['id_ba'])) {
            $query .= " AND id_ba = '".$unicityParams['id_ba']."'";
        }
        $query .= ' LIMIT 1';

        //echo $query;
        // Execute request
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            $objectId = $result[0][$sElement];
        }

        return $objectId;
    }
     
}
