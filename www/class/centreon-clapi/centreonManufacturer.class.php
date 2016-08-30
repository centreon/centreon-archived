<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";
require_once "centreonUtils.class.php";
require_once "Centreon/Object/Manufacturer/Manufacturer.php";

/**
 *
 * @author sylvestre
 */
class CentreonManufacturer extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_ALIAS             = 1;
    const FILE_NOT_FOUND          = "Could not find file";

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->object = new \Centreon_Object_Manufacturer();
        $this->params = array();
        $this->insertParams = array('name', 'alias');
        $this->action = "VENDOR";
        $this->nbOfCompulsoryParams = count($this->insertParams);
    }

    /**
     * Add action
     *
     * @param string $parameters
     * @return void
     */
    public function add($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < $this->nbOfCompulsoryParams) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $addParams = array();
        $addParams[$this->object->getUniqueLabelField()] = $params[self::ORDER_UNIQUENAME];
        $addParams['alias'] = $params[self::ORDER_ALIAS];
        $this->params = array_merge($this->params, $addParams);
        $this->checkParameters();
        parent::add();
    }

    /**
     * Show
     *
     * @return void
     */
    public function show($parameters = null)
    {
        $filters = array();
        if (isset($parameters)) {
            $filters = array($this->object->getUniqueLabelField() => "%".$parameters."%");
        }
        $params = array("id", "name", "alias");
        parent::show($params, $filters);
    }

    /**
     * Set parameter
     *
     * @param string $parameters
     * @return void
     */
    public function setparam($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < self::NB_UPDATE_PARAMS) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $values = array($params[1] => $params[2]);
        parent::setparam($this->getId($params[0]), $values);
    }

    /**
     * Will generate traps from a mib file
     *
     * @param string $parameters
     * @return void
     */
    public function generatetraps($parameters = null)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 2) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        $vendorId = $this->getId($params[0]);
        $mibFile = $params[1];
        $tmpMibFile = "/tmp/".basename($mibFile);
        if (!is_file($mibFile)) {
            throw new CentreonClapiException(self::FILE_NOT_FOUND.": ".$mibFile);
        }
        copy($mibFile, $tmpMibFile);
        $centreonDir = CentreonUtils::getCentreonDir();
        passthru("export MIBS=ALL && $centreonDir/bin/snmpttconvertmib --in=$tmpMibFile --out=$tmpMibFile.conf");
        passthru("$centreonDir/bin/centFillTrapDB -f $tmpMibFile.conf -m $vendorId");
        unlink($tmpMibFile);
        unlink($tmpMibFile.".conf");
    }

    /**
     * Get manufacturer name from id
     *
     * @param int $id
     * @return string
     */
    public function getName($id)
    {
        $this->object->setCache(true);
        $name = $this->object->getParameters($id, array($this->object->getUniqueLabelField()));
        $this->object->setCache(false);
        return $name[$this->object->getUniqueLabelField()];
    }

    /**
     * Get id from name
     *
     * @param string $name
     * @return int
     */
    public function getId($name)
    {
        $this->object->setCache(true);
        $ids = $this->object->getIdByParameter($this->object->getUniqueLabelField(), array($name));
        $this->object->setCache(false);
        if (!count($ids)) {
            throw new CentreonClapiException("Unknown instance");
        }
        return $ids[0];
    }
}
