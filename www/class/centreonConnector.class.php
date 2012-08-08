<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

/*
 *  Class that contains various methods for managing connectors
 * 
 * Usage example:
 * 
 * <?php
 * require_once "/etc/centreon/centreon.conf.php";
 * require_once $centreon_path . 'www/class/centreonConnector.class.php';
 * require_once $centreon_path . 'www/class/centreonDB.class.php';
 * 
 * $connector = new CentreonConnector(new CentreonDB);
 * 
 * //$connector->create(array(
 * //    'name' => 'jackyse',
 * //    'description' => 'some jacky',
 * //    'command_line' => 'ls -la',
 * //    'enabled' => true
 * //        ), true);
 * 
 * //$connector->update(10, array(
 * //    'name' => 'soapy',
 * //    'description' => 'Lorem ipsum',
 * //    'enabled' => true,
 * //    'command_line' => 'ls -laph --color'
 * //));
 * 
 * //$connector->delete(10);
 * 
 * //$connector->read(7);
 */

class CentreonConnector
{
    /**
     * The database connection
     * @var CentreonDB 
     */
    protected $dbConnection;

    /**
     * Constructor
     *
     * @param CentreonDB $dbConnection
     * @return void
     */
    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Adds a connector to the database
     * 
     * @param array $connector
     * @param boolean $returnId
     * @return CentreonConnector|integer
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function create(array $connector, $returnId = false)
    {
        /**
         * Checking data
         */
        if (!isset($connector['name'])) {
            throw new InvalidArgumentException('No name for the connector set');
        }

        if (empty($connector['name'])) {
            throw new InvalidArgumentException('Empty name for the connector');
        }

        if (!array_key_exists('description', $connector)) {
            $connector['description'] = null;
        }

        if (!array_key_exists('command_line', $connector)) {
            $connector['command_line'] = null;
        }

        if (!array_key_exists('enabled', $connector)) {
            $connector['enabled'] = true;
        }

        /**
         * Inserting into database
         */
        $success = $this->dbConnection->query('INSERT INTO `connector` (
                                        `name`,
                                        `description`,
                                        `command_line`,
                                        `enabled`,
                                        `created`,
                                        `modified`
                                    ) VALUES (?, ?, ?, ?, ?, ?)', array(
            $connector['name'],
            $connector['description'],
            $connector['command_line'],
            $connector['enabled'],
            $now = time(),
            $now
                ));
        if (PEAR::isError($success)) {
            throw new RuntimeException('Cannot insert connector; Check the database schema');
        }

        /**
         * in case last inserted id needed
         */
        if ($returnId) {
            $lastIdQueryResult = $this->dbConnection->query('SELECT `id` FROM `connector` WHERE `name` = ? LIMIT 1', array($connector['name']));
            if (PEAR::isError($lastIdQueryResult)) {
                throw new RuntimeException('Cannot get last insert ID');
            }
            $lastId = $lastIdQueryResult->fetchRow();
            if (!isset($lastId['id'])) {
                throw new RuntimeException('Field id for connector not selected in query or connector not inserted');
            }
            return $lastId['id'];
        }
        return $this;
    }

    /**
     * Reads the connector
     * 
     * @param int $id
     * @return array
     */
    public function read($id)
    {
        if (!is_int($id)) {
            throw new InvalidArgumentException('Id is not integer');
        }
        $result = $this->dbConnection->query('SELECT
                                                `id`,
                                                `name`,
                                                `description`,
                                                `command_line`,
                                                `enabled`,
                                                `created`,
                                                `modified`
                                             FROM
                                                `connector`
                                             WHERE
                                                `id` = ?
                                             LIMIT
                                                1', array($id));
        if (PEAR::isError($result)) {
            throw new RuntimeException('Cannot select connector');
        }
        
        return $result->fetchRow();
    }

    /**
     * Updates connector
     * 
     * @param int $id
     * 
     * @return boolean
     */
    public function update($id, $connector = array())
    {
        if (!is_array($connector)) {
            throw new InvalidArgumentException('Data is not an array');
        }

        if (!is_int($id)) {
            throw new InvalidArgumentException('Id is not integer');
        }

        if (count($connector) === 0) {
            return $this;
        }

        $data = array();

        if (isset($connector['name'])) {
            $data['name'] = $connector['name'];
        }
        if (isset($connector['description'])) {
            $data['description'] = $connector['description'];
        }
        if (isset($connector['command_line'])) {
            $data['command_line'] = $connector['command_line'];
        }
        if (isset($connector['enabled'])) {
            $data['enabled'] = $connector['enabled'];
        }
        if (count($data) !== 0) {
            $sqlParts = array();
            $sqlParts[] = '`modified` =  ?';
            foreach ($data as $fieldName => $fieldValue) {
                $sqlParts[] = "`$fieldName` = ?";
            }
            $sqlParts = implode(', ', $sqlParts);
            $updateResult = $this->dbConnection->query("UPDATE  `connector` SET $sqlParts WHERE  `connector`.`id` = ?", array_merge(array(time()), array_values($data), array($id)));
            if (PEAR::isError($updateResult)) {
                throw new RuntimeException('Cannot update connector');
            }
        }

        return $this;
    }

    /**
     * Deletes connector
     * 
     * @param int $id
     * @return boolean
     */
    public function delete($id)
    {
        if (!is_int($id)) {
            throw new InvalidArgumentException('Id should be integer');
        }
        $deleteResult = $this->dbConnection->query('DELETE FROM `connector` WHERE `id` = ?', array($id));
        if (PEAR::isError($deleteResult)) {
            throw new RuntimeException('Cannot delete connector');
        }
        return $this;
    }

    /**
     * Gets list of connectors
     * 
     * @param int $page
     * @param int $perPage
     * @param boolean $onlyEnabled
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function getList($page = 0, $perPage = 30, $onlyEnabled = true)
    {
        /**
         * Checking parameters
         */
        if (!is_int($page)) {
            throw new InvalidArgumentException('Page number should be integer');
        }
        if (!is_int($perPage)) {
            throw new InvalidArgumentException('Per page parameter should be integer');
        }

        /**
         * Calculating offset
         */
        $offset = $page * $perPage;
        if ($onlyEnabled) {
            $connectorsResult = $this->dbConnection->query("SELECT
                                                                `id`,
                                                                `name`,
                                                                `description`,
                                                                `command_line`,
                                                                `enabled`,
                                                                `created`,
                                                                `modified`
                                                             FROM
                                                                `connector`
                                                             WHERE
                                                                `enabled` = 1
                                                             LIMIT
                                                                $perPage
                                                             OFFSET
                                                                $offset");
        } else {
            $connectorsResult = $this->dbConnection->query("SELECT
                                                                `id`,
                                                                `name`,
                                                                `description`,
                                                                `command_line`,
                                                                `enabled`,
                                                                `created`,
                                                                `modified`
                                                             FROM
                                                                `connector`
                                                             LIMIT
                                                                $perPage
                                                             OFFSET
                                                                $offset");
        }

        if (PEAR::isError($connectorsResult)) {
            throw new RuntimeException('Cannot select connectors');
        }
        $connectors = array();
        while ($connector = $connectorsResult->fetchRow()) {
            $connectors[] = $connector;
        }
        return $connectors;
    }

}
