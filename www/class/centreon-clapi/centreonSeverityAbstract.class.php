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
 * For more information : command@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonObject.class.php";

abstract class CentreonSeverityAbstract extends CentreonObject
{
    const ORDER_UNIQUENAME        = 0;
    const ORDER_ALIAS             = 1;

    /**
     * Set severity
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function setseverity($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 3) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            $level = (int)$params[1];
            $iconId = CentreonUtils::getImageId($params[2]);
            if (is_null($iconId)) {
                throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[2]);
            }
            $updateParams = array(
                'level' => $level,
                'icon_id' => $iconId
            );
            parent::setparam($objectId, $updateParams);
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[self::ORDER_UNIQUENAME]);
        }
    }

    /**
     * Unset severity
     *
     * @param string $parameters
     * @throws CentreonClapiException
     */
    public function unsetseverity($parameters)
    {
        $params = explode($this->delim, $parameters);
        if (count($params) < 1) {
            throw new CentreonClapiException(self::MISSINGPARAMETER);
        }
        if (($objectId = $this->getObjectId($params[self::ORDER_UNIQUENAME])) != 0) {
            parent::setparam($objectId, array('level' => null, 'icon_id' => null));
        } else {
            throw new CentreonClapiException(self::OBJECT_NOT_FOUND.":".$params[self::ORDER_UNIQUENAME]);
        }
    }
}
