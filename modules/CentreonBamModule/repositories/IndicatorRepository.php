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

namespace CentreonBam\Repository;

use Centreon\Internal\Di;
use Centreon\Repository\FormRepository;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package CentreonBam
 * @subpackage Repository
 */
class IndicatorRepository extends FormRepository
{
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

        if ($parameters['kpi_object'] == 0) {
            self::createNormalIndicator($parameters);
        } else if ($parameters['kpi_object'] == 1) {
            self::createBooleanIndicator($parameters);
        }
    }

    /**
     *
     * @param string $parameters
     * @return string
     */
    public static function createNormalIndicator($parameters)
    {
        if ($parameters['kpi_type'] == 0) {
            self::createServiceIndicator($parameters);
        } else if ($parameters['kpi_type'] == 1) {
            self::createMetaserviceIndicator($parameters);
        } else if ($parameters['kpi_type'] == 2) {
            self::createBaIndicator($parameters);
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

        $insertRequest = "INSERT INTO cfg_bam_boolean(name, impact, expression, bool_state)"
                . " VALUES(:boolean_name, :boolean_impact, :boolean_expression, :bool_state)";
        $stmtInsert = $dbconn->prepare($insertRequest);
        $stmtInsert->bindParam(':boolean_name', $parameters['boolean_name'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':boolean_impact', $parameters['boolean_impact'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':boolean_expression', $parameters['boolean_expression'], \PDO::PARAM_INT);
        $stmtInsert->bindParam(':bool_state', $parameters['bool_state'], \PDO::PARAM_INT);
        $stmtInsert->execute();
    }

    /**
     *
     *
     * @return string
     */
    public static function getNormalIndicatorsName()
    {
        // Get datatabases connections
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        // Add object column
        // Can be service, metaservice or BA
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

        return $resultPki;
    }

    /**
     *
     *
     * @return string
     */
    public static function getNormalIndicatorName($id)
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

        return $resultPki;
    }
    
}
