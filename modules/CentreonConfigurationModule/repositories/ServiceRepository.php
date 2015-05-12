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

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Host;
use Centreon\Internal\Utils\YesNoDefault;
use Centreon\Internal\Utils\HumanReadable;
use CentreonConfiguration\Repository\Repository;
use Centreon\Internal\Exception\Validator\MissingParameterException;
/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServiceRepository extends Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_services';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Service';
    
    /**
     *
     * @var type 
     */

    public static $unicityFields = array(
        'fields' => array(
            'host' => 'cfg_hosts,host_id,host_name',
            'service' => 'cfg_services,service_id,service_description',
        ),
        'joint' => 'cfg_hosts_services_relations',
        'jointCondition' => 'cfg_hosts_services_relations.host_host_id = '
        . 'cfg_hosts.host_id AND '
        . 'cfg_hosts_services_relations.service_service_id = '
        . 'cfg_services.service_id'
    );

    /**
     * 
     * @param int $interval
     * @return string
     */
    public static function formatNotificationOptions($interval)
    {
        return HumanReadable::convert($interval, 's', $units, null, true);
    }
    
    /**
     * 
     * @param int $service_id
     * @param string $field
     * @return type
     */
    public static function getMyServiceField($service_id, $field)
    {
        
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $tab = array();
        while (1) {
            $stmt = $dbconn->query(
                "SELECT "
                . "`".$field."`, "
                . "service_template_model_stm_id "
                . "FROM cfg_services "
                . "WHERE "
                . "service_id = '".$service_id."' LIMIT 1"
            );
            $row = $stmt->fetchAll();
            if ($row[0][$field]) {
                return $row[0][$field];
            } elseif ($row[0]['service_template_model_stm_id']) {
                if (isset($tab[$row[0]['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row[0]["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                break;
            }
        }
    }

    /**
     * 
     * @param int $service_template_id
     * @return array
     */
    public static function getMyServiceTemplateModels($service_template_id)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $tplArr = null;
        
        $stmt = $dbconn->query(
            "SELECT service_description FROM cfg_services WHERE service_id = '".$service_template_id."' LIMIT 1"
        );
        $row = $stmt->fetchAll();
        if (count($row) > 0) {
            $tplArr = array(
                'id' => $service_template_id,
                'description' => \html_entity_decode(self::db2str($row[0]["service_description"]), ENT_QUOTES, "UTF-8")
            );
        }
        return $tplArr;
    }
    
    /**
     * 
     * @param string $string
     * @return string
     */
    public static function db2str($string)
    {
        $string = str_replace('#BR#', "\\n", $string);
        $string = str_replace('#T#', "\\t", $string);
        $string = str_replace('#R#', "\\r", $string);
        $string = str_replace('#S#', "/", $string);
        $string = str_replace('#BS#', "\\", $string);
        return $string;
    }
    
    /**
     * 
     * @param int $service_id
     * @return type
     */
    public static function getMyServiceAlias($service_id)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $tab = array();
        while (1) {
            $stmt = $dbconn->query(
                "SELECT "
                . "service_alias, service_template_model_stm_id "
                . "FROM cfg_services "
                . "WHERE "
                . "service_id = '".$service_id."' LIMIT 1"
            );
            $row = $stmt->fetchRow();
            if ($row["service_alias"]) {
                return html_entity_decode(db2str($row["service_alias"]), ENT_QUOTES, "UTF-8");
            } elseif ($row["service_template_model_stm_id"]) {
                if (isset($tab[$row['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                break;
            }
        }
    }
    
    /**
     * 
     * @param int $service_id
     * @return string
     */
    public static function getIconImage($service_id)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        
        $finalRoute = "";
        
        while (1) {
            $stmt = $dbconn->query(
                "SELECT b.filename, s.service_template_model_stm_id "
                . "FROM cfg_services s, cfg_services_images_relations sir, cfg_binaries b "
                . "WHERE s.service_id = '$service_id' "
                . "AND s.service_id = sir.service_id AND sir.binary_id = b.binary_id"
            );
            $esiResult = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!is_null($esiResult['filename'])) {
                $filenameExploded = explode('.', $esiResult['filename']);
                $nbOfOccurence = count($filenameExploded);
                $fileFormat = $filenameExploded[$nbOfOccurence-1];
                $filenameLength = strlen($esiResult['filename']);
                $routeAttr = array(
                    'image' => substr($esiResult['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                    'format' => '.'.$fileFormat
                );
                $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
                $finalRoute .= '<img src="'.$imgSrc.'" style="width:16px;height:16px;">';
                break;
            } elseif (is_null($esiResult['filename']) && is_null($esiResult['service_template_model_stm_id'])) {
                $finalRoute .= "<i class='icon-service ico-16'></i>";
                break;
            }
            
            $service_id = $esiResult['service_template_model_stm_id'];
        }
        
        return $finalRoute;
    }
    
    /**
     * Get configuration data of a service
     * 
     * @param int $serviceId
     * @return array
     */
    public static function getConfigurationData($serviceId)
    {
        return Service::getParameters($serviceId, "*");
    }

    /**
     * Format data so that it can be displayed in tooltip
     *
     * @param array $data
     * @return array $checkdata
     */
    public static function formatDataForTooltip($data)
    {
        /* Check data */
        $checkdata = array();
        $checkdata[] = array(
            'label' => _('Command'),
            'value' => static::getObjectName('\CentreonConfiguration\Models\Command', $data['command_command_id'])
        );
        $checkdata[] = array(
            'label' => _('Time period'),
            'value' => static::getObjectName('\CentreonConfiguration\Models\Timeperiod', $data['timeperiod_tp_id'])
        );
        $checkdata[] = array(
            'label' => _('Max check attempts'),
            'value' => $data['service_max_check_attempts']
        );
        $checkdata[] = array(
            'label' => _('Check interval'),
            'value' => $data['service_normal_check_interval']
        );
        $checkdata[] = array(
            'label' => _('Retry check interval'),
            'value' => $data['service_retry_check_interval']
        );
        $checkdata[] = array(
            'label' => _('Active checks enabled'),
            'value' => YesNoDefault::toString($data['service_active_checks_enabled'])
        );
        $checkdata[] = array(
            'label' => _('Passive checks enabled'),
            'value' => $data['service_passive_checks_enabled']
        );

        return $checkdata;
    }

    /**
     * Get domain
     *
     * @return array | array(domain_id => name)
     */
    public static function getDomain($serviceId)
    {
        static $domains = null;

        if (is_null($domains)) {
            $domains = array();
            $db = Di::getDefault()->get('db_centreon');
            $sql = "SELECT d.domain_id, d.name, s.service_id
                FROM cfg_domains d, cfg_services s
                WHERE s.domain_id = d.domain_id";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $domains[$row['service_id']] = array($row['domain_id'] => $row['name']);
            }
        }
        if (isset($domains[$serviceId])) {
            return $domains[$serviceId];
        }
        return array();
    }

    /**
     * Returns array of services that are linked to a poller
     *
     * @param int $pollerId
     * @return array
     */
    public static function getServicesByPollerId($pollerId)
    {
        $db = Di::getDefault()->get('db_centreon');

        $sql = "SELECT s.service_id, s.service_description
            FROM cfg_services s, cfg_hosts_services_relations hsr, cfg_hosts h
            WHERE s.service_id = hsr.service_service_id 
            AND hsr.host_host_id = h.host_id
            AND h.poller_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($pollerId));
        $arr = array();
        if ($stmt->rowCount()) {
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $arr[$row['service_id']] = $row['service_description'];
            }
        }

        return $arr;
    }

    /**
     * Return the list of template
     *
     * @param int $svcId The service ID
     * @return array
     */
    public static function getListTemplates($svcId, $alreadyProcessed = array())
    {
        $svcTmpl = array();
        if (in_array($svcId, $alreadyProcessed)) {
            return $svcTmpl;
        } else {
            $alreadyProcessed[] = $svcId;
            // @todo improve performance
            $dbconn = Di::getDefault()->get('db_centreon');

            $query = "SELECT service_template_model_stm_id FROM cfg_services WHERE service_id = :id";
            $stmt = $dbconn->prepare($query);
            $stmt->bindParam(':id', $svcId, \PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount()) {
                $row = $stmt->fetch();
                $stmt->closeCursor();
                if ($row['service_template_model_stm_id'] !== NULL) {
                    $svcTmpl = array_merge($svcTmpl, self::getListTemplates($row['service_template_model_stm_id'], $alreadyProcessed));
                    $svcTmpl[] = $row['service_template_model_stm_id'];
                }
            }
            return $svcTmpl;
        }
    }
    
    /**
     * Return the list of template by host id
     *
     * @param int $iHostId The Host ID
     * @return array
     */
    public static function getListTemplatesByHostId($iHostId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $svcTmpl = array();
        $query = "SELECT service_service_id FROM cfg_hosts_services_relations WHERE host_host_id = :id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':id', $iHostId, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            $stmt->closeCursor();
            $svcTmpl[] = $row['service_service_id'];
        }
        return $svcTmpl;
    }
    
    /**
     * Return the list of template
     *
     * @param int $svcId The service ID
     * @return array
     */
    
    public static function getListServiceByIdTemplate($svcId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $svcTmpl = array();
        $query = "SELECT service_id FROM cfg_services WHERE service_register = '1' AND service_template_model_stm_id = :id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':id', $svcId, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            $stmt->closeCursor();
            $svcTmpl[] = $row['service_id'];
        }
        return $svcTmpl;
    }
}
