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

use Centreon\Internal\Exception;
use CentreonConfiguration\Events\RunTest;

/**
 * Factory for ConfigTest Engine
 *
 * @author Julien Mathis <jmathis@centreon.com>
 * @version 3.0.0
 */

class ConfigTestRepository extends ConfigRepositoryAbstract
{
    private $enginepath;
    
    /**
     * Method tests
     * @param int $pollerId
     */
    public function __construct($pollerId)
    {
        parent::__construct($pollerId);
        $this->enginepath = '/usr/sbin/centengine';
        $this->output[] = sprintf(_("Testing configuration files of poller %s"), $pollerId);
    }

    /**
     * Check configuration 
     *
     * @param int $poller_id
     * @return array
     */
    public function checkConfig()
    {
        try {
            $result = "";
            $event = $this->di->get('events');
            $eventObj = new RunTest($this->pollerId);
            $event->emit('centreon-configuration.run.test', array($eventObj));
            $this->output = array_merge($this->output, $eventObj->getOutput());
        } catch (Exception $e) {
            $this->output[] = $e->getMessage();
            $this->status = false;
        }
    }
}
