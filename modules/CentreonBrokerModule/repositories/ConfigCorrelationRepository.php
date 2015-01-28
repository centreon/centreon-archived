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

namespace CentreonBroker\Repository;

use Centreon\Internal\Di;
use Centreon\Internal\Exception;

/**
 * Factory for ConfigTest Engine
 *
 * @author Julien Mathis <jmathis@centreon.com>
 * @version 3.0.0
 */
class ConfigCorrelationRepository
{
    /**
     * 
     * @param int $pollerId
     * @throws \Centreon\Internal\Exception;
     */
    public function generate($pollerId)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        /* Get tmp path */
        $config = Di::getDefault()->get('config');
        $tmpPath = rtrim($config->get('global', 'centreon_generate_tmp_dir'));
        if (!isset($tmpPath)) {
            throw new Exception('Temporary path not set');
        }
        $tmpPath = rtrim($tmpPath, '/') . '/broker';
        
        $xml = new \XMLWriter();

        /* Create directories if they don't exist */
        if (!is_dir($tmpPath)) {
            mkdir($tmpPath);
        }
        if (!is_dir("{$tmpPath}/{$pollerId}")) {
            mkdir("{$tmpPath}/{$pollerId}");
        }
        $correlationFile = "{$tmpPath}/{$pollerId}/correlation_{$pollerId}.xml";
        if (false === @$xml->openURI($correlationFile)) {
            throw new Exception(sprintf('Error while opening %s', $correlationFile));
        }

        $xml->startDocument('1.0', 'UTF-8');
        
        /* Start Element conf */
        $xml->startElement('conf');

        /* Declare Host */
        $query = "SELECT h.host_id, h.poller_id 
            FROM cfg_hosts h 
            ORDER BY h.host_id";
        $stmt = $dbconn->query($query);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $xml->startElement('host');
            $xml->writeAttribute('id', $row["host_id"]);
            $xml->writeAttribute('instance_id', $row["poller_id"]);
            $xml->endElement();
        }
        
        /* Declare Service */
        $query = "SELECT s.service_id, h.host_id, h.poller_id
            FROM cfg_hosts h, cfg_services s, cfg_hosts_services_relations ns
            WHERE h.host_id = ns.host_host_id
            AND s.service_id = ns.service_service_id ";
        $stmt = $dbconn->query($query);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $xml->startElement('service');
            $xml->writeAttribute('id', $row["service_id"]);
            $xml->writeAttribute('host', $row["host_id"]);
            $xml->writeAttribute('instance_id', $row["poller_id"]);
            $xml->endElement();
        }
        
        /* End conf Element */
        $xml->endElement();
        $xml->endDocument();

        static::generateInclusionFile($tmpPath);
    }

    /**
     * Generate inclusion file
     *
     * @param string $tmpPath Temporary path
     */
    protected static function generateInclusionFile($tmpPath)
    {
        static $generated = false;

        /* We'll generate only once and for all */
        if (false === $generated) {
            $generated = true;

            /* Retrieve active pollers */
            $db = Di::getDefault()->get('db_centreon');
            $sql = "SELECT poller.poller_id, directory_config
                FROM cfg_pollers poller, cfg_centreonbroker_paths paths 
                WHERE poller.poller_id = paths.poller_id 
                AND poller.enable = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $pollers = $rows;

            foreach ($rows as $row) {
                /* @todo: replace hardcoded "correlation.xml" with value from template */
                $correlationFile = rtrim($tmpPath, '/') . '/' . $row['poller_id'] . '/correlation.xml';

                $xml = new \XMLWriter();
                if (false === @$xml->openURI($correlationFile)) {
                    throw new Exception(sprintf('Error while opening %s', $correlationFile));
                }
                $xml->startDocument('1.0', 'UTF-8');
                $xml->startElement('conf');

                /* includes */
                foreach ($pollers as $poller) {
                    $tmpFile = rtrim($tmpPath, '/') . '/' . $row['poller_id']. '/correlation_' . $row['poller_id'] . '.xml';
                    $file = rtrim($row['directory_config'], '/') . "/correlation_" . $row['poller_id'] . ".xml";
                    if (is_file($tmpFile)) {
                        $xml->writeElement('include', $file);
                    }
                }

                $xml->endElement();
                $xml->endDocument();
            }
        }
    }
}
