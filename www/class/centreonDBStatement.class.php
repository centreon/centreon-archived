<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */


// file centreon.config.php may not exist in test environment
$configFile = realpath(dirname(__FILE__) . "/../../config/centreon.config.php");
if ($configFile !== false) {
    include_once $configFile;
}

require_once realpath(dirname(__FILE__) . "/centreonDB.class.php");

class CentreonDBStatement extends \PDOStatement
{
    /**
     * When data is retrieved from `numRows()` method, it is stored in `allFetched`
     * in order to be usable later without requesting one more time the database.
     *
     * @var mixed[]|null
     */
    public $allFetched;

    /**
     * @var CentreonLog
     */
    private $log;

    /**
     * Constructor
     *
     * @param CentreonLog $log
     */
    protected function __construct(CentreonLog $log = null)
    {
        $this->log = $log;
        $this->allFetched = null;
    }

    /**
     * This method overloads PDO `fetch` in order to use the possible already
     * loaded data available in `allFetched`.
     *
     * @inheritDoc
     */
    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        if (is_null($this->allFetched)) {
            return parent::fetch();
        } elseif (count($this->allFetched) <= 0) {
            return false;
        } else {
            return array_shift($this->allFetched);
        }
    }

    /**
     * This method wraps the Fetch method for legacy code compatibility
     * @return mixed
     */
    public function fetchRow()
    {
        return $this->fetch();
    }

    /**
     * Free resources.
     */
    public function free()
    {
        $this->closeCursor();
    }

    /**
     * Counts the number of rows returned by the query.\
     * This method fetches data if needed and store it in `allFetched`
     * in order to be usable later without requesting database again.
     *
     * @return int
     */
    public function numRows()
    {
        if (is_null($this->allFetched)) {
            $this->allFetched = $this->fetchAll();
        }
        return count($this->allFetched);
    }

    /**
     * This method wraps the PDO `execute` method and manages failures logging
     * @inheritDoc
     */
    public function execute($parameters = null)
    {
        $this->allFetched = null;

        try {
            $result = parent::execute($parameters);
        } catch (\PDOException $e) {
            if ($this->debug) {
                $string = str_replace("`", "", $this->queryString);
                $string = str_replace('*', "\*", $string);
                $this->log->insertLog(2, $e->getMessage() . " QUERY : " . $string . ", " . json_encode($parameters));
            }

            throw $e;
        }

        return $result;
    }
}
