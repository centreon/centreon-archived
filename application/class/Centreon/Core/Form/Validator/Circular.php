<?php

namespace Centreon\Core\Form\Validator;

class Circular implements Ivalidator
{
    /**
     * 
     */
    public static function validate($value, $objectName, $id = null)
    {
        if (!is_null($id)) {
            $value = $id . ',' . $value;
        }
        $result = self::recursiveCircular(
            $objectName, 
            explode(',', $value)
        );
        return $result;
    }

    /**
     * Recursive method
     *
     * @param string $object
     * @param array $values
     * @return bool
     */
    protected function recursiveCircular($object, $values)
    {
        static $stored = array();

        foreach ($values as $value) {
            if (isset($stored[$value])) {
                return false;
            }
            $stored[$value] = true;
            $relations = $object::getTargetIdFromSourceId(
                $object::getSecondKey(), 
                $object::getFirstKey(), 
                $value
            );
            if (count($relations)) {
                $res = $this->recursiveCircular($object, $relations);
                if ($res === false) {
                    return false;
                }
            }
        }
        return true;
    }
}
