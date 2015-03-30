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
 */

namespace CentreonBam\Repository;

use Centreon\Internal\Di;
use CentreonMain\Repository\FormRepository;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package CentreonBam
 * @subpackage Repository
 */
class IndicatorRepository extends FormRepository
{
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
    public static function createIndicator($givenParameters)
    {
        $parameters = array();
        foreach ($givenParameters as $k => $v) {
            $parameters[$k] = $v;
        }

        $parameters['drop_warning'] = (isset($parameters['drop_warning'])) ? $parameters['drop_warning'] : 0;
        $parameters['drop_critical'] = (isset($parameters['drop_critical'])) ? $parameters['drop_critical'] : 0;
        $parameters['drop_unknown'] = (isset($parameters['drop_unknown'])) ? $parameters['drop_unknown'] : 0;
        $parameters['boolean_impact'] = (isset($parameters['boolean_impact'])) ? $parameters['boolean_impact'] : 0;

        if ($parameters['kpi_type'] == 0) {
            self::createServiceIndicator($parameters);
        } else if ($parameters['kpi_type'] == 1) {
            self::createMetaserviceIndicator($parameters);
        } else if ($parameters['kpi_type'] == 2) {
            self::createBaIndicator($parameters);
        } else if ($parameters['kpi_type'] == 3) {
            self::createBooleanIndicator($parameters);
        }
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createServiceIndicator($parameters)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        list($serviceId, $hostId) = explode('_', $parameters['service_id']);

        $insertRequest = "INSERT INTO cfg_bam_kpi(kpi_type, host_id, service_id, drop_warning, drop_critical, drop_unknown)"
                . " VALUES('0', :host_id, :service_id, :drop_warning, :drop_critical, :drop_unknown)";
        $stmtInsert = $dbconn->prepare($insertRequest);
        $stmtInsert->bindParam(':host_id', $hostId, \PDO::PARAM_INT);
        $stmtInsert->bindParam(':service_id', $serviceId, \PDO::PARAM_INT);
        $stmtInsert->bindParam(':drop_warning', $parameters['drop_warning'], \PDO::PARAM_STR);
        $stmtInsert->bindParam(':drop_critical', $parameters['drop_critical'], \PDO::PARAM_STR);
        $stmtInsert->bindParam(':drop_unknown', $parameters['drop_unknown'], \PDO::PARAM_STR);
        $stmtInsert->execute();
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createMetaserviceIndicator($parameters)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $insertRequest = "INSERT INTO cfg_bam_kpi(kpi_type, meta_id, drop_warning, drop_critical, drop_unknown)"
                . " VALUES('1', :meta_id, :drop_warning, :drop_critical, :drop_unknown)";
        $stmtInsert = $dbconn->prepare($insertRequest);
        $stmtInsert->bindParam(':meta_id', $parameters['meta_id'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':drop_warning', $parameters['drop_warning'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':drop_critical', $parameters['drop_critical'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':drop_unknown', $parameters['drop_unknown'], \PDO::PARAM_INT);
        $stmtInsert->execute();
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createBaIndicator($parameters)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $insertRequest = "INSERT INTO cfg_bam_kpi(kpi_type, id_indicator_ba, drop_warning, drop_critical, drop_unknown)"
                . " VALUES('2', :id_indicator_ba, :drop_warning, :drop_critical, :drop_unknown)";
        $stmtInsert = $dbconn->prepare($insertRequest);
        $stmtInsert->bindParam(':id_indicator_ba', $parameters['id_indicator_ba'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':drop_warning', $parameters['drop_warning'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':drop_critical', $parameters['drop_critical'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':drop_unknown', $parameters['drop_unknown'], \PDO::PARAM_INT);
        $stmtInsert->execute();
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createBooleanIndicator($parameters)
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        $insertBooleanRequest = "INSERT INTO cfg_bam_boolean(name, expression, bool_state)"
                . " VALUES(:boolean_name, :boolean_expression, :bool_state)";
        $stmtBooleanInsert = $dbconn->prepare($insertBooleanRequest);
        $stmtBooleanInsert->bindParam(':boolean_name', $parameters['boolean_name'], \PDO::PARAM_INT);
        $stmtBooleanInsert->bindParam(':boolean_expression', $parameters['boolean_expression'], \PDO::PARAM_INT);
        $stmtBooleanInsert->bindParam(':bool_state', $parameters['bool_state'], \PDO::PARAM_INT);
        $stmtBooleanInsert->execute();
        $lastBooleanId = $dbconn->lastInsertId('cfg_bam_boolean','boolean_id');

        $insertIndicatorRequest = "INSERT INTO cfg_bam_kpi(kpi_type, boolean_id, drop_critical)"
            . " VALUES('3', :boolean_id, :drop_critical)";
        $stmtIndicatorInsert = $dbconn->prepare($insertIndicatorRequest);
        $stmtIndicatorInsert->bindParam(':boolean_id', $lastBooleanId, \PDO::PARAM_INT);
        $stmtIndicatorInsert->bindParam('drop_critical', $parameters['drop_critical'], \PDO::PARAM_INT);
        $stmtIndicatorInsert->execute();
    }

    /**
     * Generic update function
     *
     * @param array $givenParameters
     * @throws \Centreon\Internal\Exception
     */
    public static function updateBooleanIndicator($givenParameters, $origin = "", $route = "")
    {

        $class = static::$objectClass;
        $booleanId = $class::getParameters($givenParameters['object_id'], 'boolean_id');

        $relBooleanIndicator = '\CentreonBam\Models\BooleanIndicator';
        $updateValuesBoolean = array();
        $updateValuesBoolean['expression'] = $givenParameters['boolean_expression'];
        $updateValuesBoolean['name'] = $givenParameters['boolean_name'];
        $updateValuesBoolean['bool_state'] = $givenParameters['bool_state'];
        $relBooleanIndicator::update($booleanId['boolean_id'], $updateValuesBoolean);
    }

    /**
     *
     *
     * @return string
     */
    public static function getIndicatorsName()
    {
        // Get datatabases connections
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $sqlKpiService = 'SELECT k.kpi_id, h.host_name, s.service_id, s.service_description
            FROM cfg_hosts h, cfg_services s, cfg_hosts_services_relations hs, cfg_bam_kpi k
            WHERE s.service_id=k.service_id and hs.host_host_id=h.host_id and hs.service_service_id=s.service_id';
        $stmtKpiService = $dbconn->query($sqlKpiService);
        $resultKpiService = $stmtKpiService->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiMetaservice = 'SELECT k.kpi_id,ms.meta_id
            FROM cfg_meta_services ms,cfg_bam_kpi k
            WHERE ms.meta_id=k.meta_id';
        $stmtKpiMetaservice = $dbconn->query($sqlKpiMetaservice);
        $resultKpiMetaservice = $stmtKpiMetaservice->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBa = 'SELECT k.kpi_id,b.ba_id,b.name
            FROM cfg_bam b,cfg_bam_kpi k
            WHERE b.ba_id=k.id_indicator_ba';
        $stmtKpiBa = $dbconn->query($sqlKpiBa);
        $resultKpiBa = $stmtKpiBa->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBoolean = "SELECT k.kpi_id,b.boolean_id,b.name
            FROM cfg_bam_boolean b,cfg_bam_kpi k
            WHERE b.boolean_id=k.boolean_id";
        $stmtKpiBoolean = $dbconn->query($sqlKpiBoolean);
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
        $sqlKpiService = "SELECT k.kpi_id, h.host_name, s.service_id, s.service_description
            FROM cfg_hosts h, cfg_services s, cfg_hosts_services_relations hs, cfg_bam_kpi k
            WHERE s.service_id=k.service_id and hs.host_host_id=h.host_id and hs.service_service_id=s.service_id and k.kpi_id='$id'";
        $stmtKpiService = $dbconn->query($sqlKpiService);
        $resultKpiService = $stmtKpiService->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiMetaservice = "SELECT k.kpi_id,ms.meta_id
            FROM cfg_meta_services ms,cfg_bam_kpi k
            WHERE ms.meta_id=k.meta_id and k.kpi_id='$id'";
        $stmtKpiMetaservice = $dbconn->query($sqlKpiMetaservice);
        $resultKpiMetaservice = $stmtKpiMetaservice->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBa = "SELECT k.kpi_id,b.ba_id,b.name
            FROM cfg_bam b,cfg_bam_kpi k
            WHERE b.ba_id=k.id_indicator_ba and k.kpi_id='$id'";
        $stmtKpiBa = $dbconn->query($sqlKpiBa);
        $resultKpiBa = $stmtKpiBa->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBoolean = "SELECT k.kpi_id,b.boolean_id,b.name
            FROM cfg_bam_boolean b,cfg_bam_kpi k
            WHERE b.boolean_id=k.boolean_id and k.kpi_id='$id'";
        $stmtKpiBoolean = $dbconn->query($sqlKpiBoolean);
        $resultKpiBoolean = $stmtKpiBoolean->fetchAll(\PDO::FETCH_ASSOC);

        $resultPki = array();
        foreach ($resultKpiService as $kpiObject) {
            $resultPki = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['host_name'].' '.$kpiObject['service_description']
            );
        }
        foreach ($resultKpiMetaservice as $kpiObject) {
            $resultPki = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['meta_id']
            );
        }
        foreach ($resultKpiBa as $kpiObject) {
            $resultPki = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['name']
            );
        }
        foreach ($resultKpiBoolean as $kpiObject) {
            $resultPki = array(
                "id" => $kpiObject['kpi_id'],
                "text" => $kpiObject['name']
            );
        }

        return $resultPki;
    }
    
}
