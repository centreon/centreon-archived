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

namespace  CentreonConfiguration\Repository;

use Centreon\Internal\Di;
use Centreon\Internal\Exception;

/**
 * TODO
 *
 * @author Julien Mathis <jmathis@centreon.com>
 * @version 3.0.0
 */

abstract class ConfigRepositoryAbstract
{
    /**
     *
     * @var type
     */
    protected $di;

    /**
     *
     * @var array
     */
    protected $output;
    
    /**
     *
     * @var int
     */
    protected $pollerId;

    /**
     *
     * @var bool
     */
    protected $status;

    /**
     *
     * @param int $pollerId
     * @return type
     */
    public function __construct($pollerId)
    {
        $this->di = Di::getDefault();
        $this->output = array();
        $this->pollerId = $pollerId;
        $this->status = true;
    }

    /**
     * Get output, converts array as string
     *
     * @param string $glue
     * @return string
     */
    public function getOutput($glue = "\n")
    {
        return $glue . implode($glue, $this->output) . $glue;
    }

    /**
     * Get status
     *
     * @return int 1 = successful, 0 = error occured
     */
    public function getStatus()
    {
        if ($this->status) {
            return 1;
        }
        return 0;
    }
}
