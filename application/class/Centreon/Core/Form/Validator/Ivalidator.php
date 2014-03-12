<?php

namespace Centreon\Core\Form\Validator;

interface Ivalidator
{
    /**
     * Validate a value
     *
     * @param mixed $value
     * @param string $objectName
     * @param int $id
     * @return bool
     */
    public static function validate($value, $objectName = "", $id = null);
}
