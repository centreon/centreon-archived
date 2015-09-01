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

namespace CentreonRealtime\Events;

/**
 * Event object for external command
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonRealtime
 */
class ExternalCommand
{
    /**
     * The poller id where send the command
     *
     * @var int
     */
    private $pollerId;

    /**
     * The command
     *
     * @var string
     */
    private $command;

    /**
     * The type
     *
     * @var string
     */
    private $type;

    /**
     * Constructor
     *
     * @param int $pollerId The poller id
     * @param string $command The command
     */
    public function __construct($pollerId, $command, $type = 'engine')
    {
        $this->pollerId = $pollerId;
        $this->command = $command;
        $this->type = $type;
    }

    /**
     * Get the poller id where send the command
     * 
     * @return int
     */
    public function getPollerId()
    {
        return $this->pollerId;
    }

    /**
     * Get the command to send
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get the command type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
