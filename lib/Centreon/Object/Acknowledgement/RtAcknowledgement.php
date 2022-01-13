<?php

/*
 * Copyright 2005-2020 CENTREON
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once "Centreon/Object/ObjectRt.php";

/**
 * Used for interacting with acknowledge objects
 *
 * @author Laurent Loic
 */
class Centreon_Object_RtAcknowledgement extends Centreon_ObjectRt
{
    protected $table = "acknowledgements";
    protected $primaryKey = "acknowledgement_id";
    protected $uniqueLabelField = "comment_data";

    /**
     * @param int[] $hostIds
     * @return array
     */
    public function getLastHostAcknowledgement($hostIds = array())
    {
        $hostFilter = '';
        if (!empty($hostIds)) {
            $hostFilter = "AND hosts.host_id IN (" . implode(",", $hostIds) . ")";
        }

        return $this->getResult(
            sprintf(
                'SELECT  ack.acknowledgement_id, hosts.name, ack.entry_time as entry_time,
                    ack.author, ack.comment_data, ack.sticky, ack.notify_contacts, ack.persistent_comment
                FROM acknowledgements ack
                INNER JOIN hosts
                    ON hosts.host_id = ack.host_id
                INNER JOIN
                    (SELECT MAX(ack.entry_time) AS entry_time, ack.host_id
                    FROM acknowledgements ack
                    INNER JOIN hosts
                        ON hosts.host_id = ack.host_id
                    WHERE hosts.acknowledged = 1
                    AND ack.service_id = 0
                    %s
                    GROUP BY ack.host_id
                    ) AS tmp
                    ON tmp.entry_time = ack.entry_time
                    AND tmp.host_id = ack.host_id
                    AND ack.service_id = 0
                ORDER BY ack.entry_time, hosts.name',
                $hostFilter
            )
        );
    }

    /**
     * @param string[] $svcList
     * @return array
     */
    public function getLastSvcAcknowledgement($svcList = array())
    {
        $serviceFilter = '';

        if (!empty($svcList)) {
            $serviceFilter = 'AND (';
            $filterTab = array();
            for ($i = 0; $i < count($svcList); $i += 2) {
                $hostname = $svcList[$i];
                $serviceDescription = $svcList[$i + 1];
                $filterTab[] = '(host.name = "'
                    . $hostname
                    . '" AND service.description = "'
                    . $serviceDescription
                    . '")';
            }
            $serviceFilter .= implode(' AND ', $filterTab) . ') ';
        }

        return $this->getResult(
            sprintf(
                'SELECT ack.acknowledgement_id, host.name, service.description, ack.entry_time,
                       ack.author, ack.comment_data , ack.sticky, ack.notify_contacts, ack.persistent_comment
                FROM acknowledgements ack
                INNER JOIN services service
                    ON service.service_id = ack.service_id
                INNER JOIN hosts host
                    ON host.host_id = service.host_id
                    AND host.host_id = ack.host_id
                INNER JOIN
                    (SELECT max(ack.entry_time) AS entry_time, host.host_id, service.service_id
                    FROM acknowledgements ack
                    INNER JOIN services service
                        ON service.service_id = ack.service_id
                    INNER JOIN hosts host
                        ON host.host_id = service.host_id
                        AND host.host_id = ack.host_id
                    WHERE service.acknowledged = 1
                    %s
                    GROUP BY host.name, service.description) AS tmp
                    ON tmp.entry_time = ack.entry_time
                    AND tmp.host_id = ack.host_id
                    AND tmp.service_id = ack.service_id
                ORDER BY ack.entry_time, host.name, service.description',
                $serviceFilter
            )
        );
    }

    /**
     * @param $serviceId
     * @return bool
     */
    public function svcIsAcknowledged($serviceId)
    {
        $query = "SELECT acknowledged FROM services WHERE service_id = ? ";
        if ($this->getResult($query, array($serviceId), 'fetch')['acknowledged'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $hostId
     * @return bool
     */
    public function hostIsAcknowledged($hostId)
    {
        $query = "SELECT acknowledged FROM hosts WHERE host_id = ? ";
        if ($this->getResult($query, array($hostId), 'fetch')['acknowledged'] == 1) {
            return true;
        } else {
            return false;
        }
    }
}
