<?php

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
