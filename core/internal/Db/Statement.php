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
 */

namespace Centreon\Internal\Db;

/**
 * Class for manage database connection
 *
 * @see http://www.php.net/manual/en/class.pdostatement.php PDO Statement
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Statement extends \PDOStatement
{
    /**
     * @var \PDO The database connection
     */
    protected $connection;

    /**
     * Consturctor
     *
     * @param $connection \PDO The database connection
     */
    protected function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Execute a prepare statement
     *
     * @see http://www.php.net/manual/en/pdostatement.execute.php PDO Statement
     * @param $parameters array The input parameters
     * @return bool
     */
    public function execute($parameters = array())
    {
        // @Todo emit event before
        if (count($parameters) === 0) {
            $return = parent::execute();
        } else {
            $return = parent::execute($parameters);
        }
        // @Todo emit event after
        return $return;
    }

    /**
     * Fetch a line from SQL cursor
     * 
     * Alias to the method fetch
     *
     * @deprecated
     * @return mixed
     */
    public function fetchRow()
    {
        return $this->fetch();
    }
}
