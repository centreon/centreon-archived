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

use CentreonConfiguration\Events\EngineProcess;
use CentreonConfiguration\Events\BrokerProcess;

/**
 * Factory for ConfigTest Engine
 *
 * @author Julien Mathis <jmathis@centreon.com>
 * @version 3.0.0
 */

class ConfigApplyRepository extends ConfigRepositoryAbstract
{
    /**
     * Used for reload/restart engine
     *
     * @param int $pollerId
     */
    public function __construct($pollerId)
    {
        parent::__construct($pollerId);
        $this->output[] = sprintf(_('Processing poller %s'), $pollerId);
    }

    /**
     * 
     * @param string $method
     * @return array
     */
    public function action($method)
    {
        try {
            $event = $this->di->get('events');

            /* Engine */
            $this->output[] = sprintf(_("Performing %s action on Engine..."), $method);
            $engineEvent = new EngineProcess($this->pollerId, $method);
            $event->emit("centreon-configuration.engine.process", array($engineEvent));
            $this->output = array_merge($this->output, $engineEvent->getOutput());

            // Check Engine action is OK before going on with Broker
            if ($engineEvent->getStatus()) {
                /* Broker */
                $this->output[] = sprintf(_("Performing %s action on Broker..."), $method);
                $brokerEvent = new BrokerProcess($this->pollerId, $method);
                $event->emit("centreon-configuration.broker.process", array($brokerEvent));
                $this->output = array_merge($this->output, $brokerEvent->getOutput());
                if (!$brokerEvent->getStatus())
                {
                    $this->status = false;
                }
            } else {
                $this->status = false;
            }
        } catch (Exception $e) {
            $this->output[] = $e->getMessage();
            $this->status = false;
        }
    }
}
