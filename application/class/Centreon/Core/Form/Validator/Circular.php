<?php

namespace Centreon\Core\Form\Validator;

class Circular implements Ivalidator
{
    /**
     * 
     */
    public static function validate($value, $objectName = '', $id = null, $fieldname = '')
    {
        $controller = '\\Controllers\\Configuration\\' . ucfirst($objectName) . 'Controller';
        $result = true;
        $resultError = 'Redondance circulaire détectée';
        
        $object = $controller::$relationMap[$fieldname];
        $objectStack = explode(',', trim($value));
        $enlistedObject = array();
        
        while (count($objectStack) > 0) {
            $currentObject = array_pop($objectStack);
            if (!is_null($id)) {
                if ($currentObject == $id) {
                    $result = false;
                    break;
                }
            }
            $enlistedObject[$currentObject] = true;
            $relations = $object::getTargetIdFromSourceId(
                $object::getSecondKey(),
                $object::getFirstKey(),
                $currentObject
            );
            foreach($relations as $relation) {
                $objectStack[] = $relation;
            }
        }
        
        return array(
            'success' => $result,
            'error' => $resultError
        );
    }
}
