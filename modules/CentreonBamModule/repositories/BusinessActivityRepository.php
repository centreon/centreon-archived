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

use CentreonMain\Repository\FormRepository;
use Centreon\Internal\Di;
use CentreonConfiguration\Models\VirtualHost;
use CentreonConfiguration\Models\VirtualService;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package CentreonBam
 * @subpackage Repository
 */
class BusinessActivityRepository extends FormRepository
{
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
        
        $stmt = $dbconn->query(
            "SELECT b.filename "
            . "FROM cfg_bam ba, cfg_binaries b "
            . "WHERE ba.name = '$name' "
            . "AND ba.icon_id = b.binary_id "
        );
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
            $finalRoute .= '<img src="'.$imgSrc.'" style="width:16px;height:16px;">';
        } elseif (is_null($baIconResult['filename'])/* && !is_null($tplResult['host_tpl_id'])*/) {
            $finalRoute .= "<i class='fa fa-university'></i>";
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
            . "WHERE k.id_ba='$id' ";
        $stmt = $dbconn->query($sql);
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
}
