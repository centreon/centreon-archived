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

namespace CentreonConfiguration\Repository;
use CentreonConfiguration\Events\GenerateEngine;
use CentreonConfiguration\Events\GenerateBroker;

use Centreon\Internal\Exception;

/**
 * Factory for ConfigGenerate Engine
 *
 * @author Julien Mathis <jmathis@centreon.com>
 * @version 3.0.0
 */

class ConfigGenerateRepository extends ConfigRepositoryAbstract
{
    /**
     * Method tests
     * 
     * @param int $pollerId
     * @return type
     */
    public function __construct($pollerId)
    {
        parent::__construct($pollerId);
    }

    /**
     * Generate all configuration files
     *
     */
    public function generate()
    {
        try {
            $this->checkPollerInformations();
            $event = $this->di->get('events');

            /* Engine conf generation */
            $engineEvent = new GenerateEngine($this->pollerId);
            $event->emit('centreon-configuration.generate.engine', array($engineEvent));
            $this->output = array_merge($this->output, $engineEvent->getOutput());
        } catch (Exception $e) {
            $this->output[] = $e->getMessage();
            $this->status = false;
        }
    }

    /**
     * 
     * @return array
     */
    public function checkPollerInformations()
    {
        $dbconn = $this->di->get('db_centreon');
        $query = "SELECT * FROM cfg_pollers WHERE poller_id = ?";
        $stmt = $dbconn->prepare($query);
        $stmt->execute(array($this->pollerId));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!isset($row)) {
            $this->output[] = "Poller {$this->pollerId} is not defined or not enabled.";
        }
    }
}
