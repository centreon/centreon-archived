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
        $tmpPath = rtrim($tmpPath, '/') . '/broker/generate';
        
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
                if (!is_dir("{$tmpPath}/{$row['poller_id']}")) {
                    mkdir("{$tmpPath}/{$row['poller_id']}");
                }
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
