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

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Models\Host;
use CentreonConfiguration\Models\Command;
use CentreonConfiguration\Models\Timeperiod;
use Centreon\Internal\Utils\YesNoDefault;
use CentreonConfiguration\Repository\Repository;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class HostRepository extends Repository
{
    /**
     * List of column for inheritance
     * @var array
     */
    protected static $inheritanceColumns = array(
        'command_command_id',
        'command_command_id_arg1',
        'timeperiod_tp_id',
        'timeperiod_tp_id2',
        'command_command_id2',
        'command_command_id_arg2',
        'host_max_check_attempts',
        'host_check_interval',
        'host_retry_check_interval',
        'host_active_checks_enabled',
        'host_passive_checks_enabled',
        'host_checks_enabled',
        'initial_state',
        'host_obsess_over_host',
        'host_check_freshness',
        'host_event_handler_enabled',
        'host_low_flap_threshold',
        'host_high_flap_threshold',
        'host_flap_detection_enabled',
        'flap_detection_options',
        'host_process_perf_data',
        'host_retain_status_information',
        'host_retain_nonstatus_information',
        'host_notification_interval',
        'host_notification_options',
        'host_notifications_enabled',
        'contact_additive_inheritance',
        'cg_additive_inheritance',
        'host_first_notification_delay',
        'host_stalking_options',
        'host_snmp_community',
        'host_snmp_version',
        'host_tags'
    );

    /**
     * 
     * @param string $host_name
     * @return string
     */
    public static function getIconImage($host_name)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        
        $finalRoute = "";
        $templates = array();
        $alreadyProcessed = false;
        $hostIdTab = Host::getIdByParameter('host_name', array($host_name));
        if (count($hostIdTab) == 0) {
            $finalRoute = "<i class='fa fa-hdd-o'></i>";
        } else {
            $hostId = $hostIdTab[0];
        }

        while (empty($finalRoute)) {
            $stmt = $dbconn->query(
                "SELECT b.filename "
                . "FROM cfg_hosts h, cfg_hosts_images_relations hir, cfg_binaries b "
                . "WHERE h.host_id = '$hostId' "
                . "AND h.host_id = hir.host_id "
                . "AND hir.binary_id = b.binary_id"
            );
            $ehiResult = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!is_null($ehiResult['filename'])) {
                $filenameExploded = explode('.', $ehiResult['filename']);
                $nbOfOccurence = count($filenameExploded);
                $fileFormat = $filenameExploded[$nbOfOccurence-1];
                $filenameLength = strlen($ehiResult['filename']);
                $routeAttr = array(
                    'image' => substr($ehiResult['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                    'format' => '.'.$fileFormat
                );
                $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
                $finalRoute = '<img src="'.$imgSrc.'" style="width:16px;height:16px;">';
            } else {
                if (count($templates) == 0 && !$alreadyProcessed) {
                    $templates = static::getTemplateChain($hostId);
                    $alreadyProcessed = true;
                } else if (count($templates) == 0 && $alreadyProcessed) {
                    $finalRoute = "<i class='fa fa-hdd-o'></i>";
                }
                $currentHost = array_shift($templates);
                $hostId = $currentHost['id'];
            }
        }

        return $finalRoute;    
    }

    /**
     * Get configuration data of a host
     * 
     * @param int $hostId
     * @return array
     */
    public static function getConfigurationData($hostId)
    {
        return Host::getParameters($hostId, "*");
    }

    /**
     * Format data so that it can be displayed in tooltip
     *
     * @param array $data
     * @return array array($checkdata, $notifdata)
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
            'value' => $data['host_max_check_attempts']
        );
        $checkdata[] = array(
            'label' => _('Check interval'),
            'value' => $data['host_check_interval']
        );
        $checkdata[] = array(
            'label' => _('Retry check interval'),
            'value' => $data['host_retry_check_interval']
        );
        $checkdata[] = array(
            'label' => _('Active checks enabled'),
            'value' => YesNoDefault::toString($data['host_active_checks_enabled'])
        );
        $checkdata[] = array(
            'label' => _('Passive checks enabled'),
            'value' => $data['host_passive_checks_enabled']
        );

        /* Notification data */
        $notifdata = array();
        $notifdata[] = array(
            'label' => _('Notification enabled'),
            'value' => YesNoDefault::toString($data['host_notifications_enabled'])
        );
        $notifdata[] = array(
            'label' => _('Notification interval'),
            'value' => $data['host_notification_interval']
        );
        $notifdata[] = array(
            'label' => _('Time period'),
            'value' => static::getObjectName('\CentreonConfiguration\Models\Timeperiod', $data['timeperiod_tp_id2'])
        );
        $notifdata[] = array(
            'label' => _('Options'),
            'value' => $data['host_notification_options']
        );
        $notifdata[] = array(
            'label' => _('First notification delay'),
            'value' => $data['host_first_notification_delay']
        );
        $notifdata[] = array(
            'label' => _('Contacts'),
            'value' => ''
        );
        $notifdata[] = array(
            'label' => _('Contact groups'),
            'value' => ''
        );
        return array($checkdata, $notifdata);
    }

    /**
     * Get the value from template
     *
     * @param int $hostId The host template Id
     * @return array
     */
    public static function getInheritanceValues($hostId)
    {
        $values = array();
        $templates = static::getTemplateChain($hostId);
        foreach ($templates as $template) {
            $inheritanceValues = HostTemplateRepository::getInheritanceValues($template['id']);
            $tmplValues = Host::getParameters($template['id'], self::$inheritanceColumns);
            $tmplValues = array_filter($tmplValues, function($value) {
                return !is_null($value);
            });
            $tmplValues = array_merge($inheritanceValues, $tmplValues);
            $values = array_merge($tmplValues, $values);
        }
        return $values;
    }

    /**
     * Get template list (id, text)
     *
     * @param int $hostId The host template Id
     * @return array
     */
    public static function getTemplateChain($hostId)
    {
        $templates = static::getRelations(
            '\CentreonConfiguration\Models\Relation\Host\Hosttemplate',
            $hostId
        );
        return $templates;
    }
    
    /**
     * Returns array of services that are linked to a poller
     *
     * @param int $pollerId
     * @return array
     */
    public static function getHostsByPollerId($pollerId)
    {
        $db = Di::getDefault()->get('db_centreon');

        $sql = "SELECT h.host_id, h.host_name
            FROM cfg_hosts h
            WHERE h.poller_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($pollerId));
        $arr = array();
        if ($stmt->rowCount()) {
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $arr[$row['host_id']] = $row['host_name'];
            }
        }

        return $arr;
    }
}
