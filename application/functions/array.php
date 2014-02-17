<?php

/**
 * 
 * @param array $array
 * @return type
 */
function arrayDepth(array $array)
{
    $max_depth = 1;

    foreach ($array as $value) {
        if (is_array($value)) {
            $depth = arrayDepth($value) + 1;

            if ($depth > $max_depth) {
                $max_depth = $depth;
            }
        }
    }

    return $max_depth;
}

/**
 * Inserts values after specific key.
 *
 * @param array $array
 * @param sting/integer $position
 * @param array $values
 * @throws Exception
 */
function insertAfter(array &$array, $position, array $values)
{
    // enforce existing position
    if (!isset($array[$position]))
    {
        throw new Exception(strtr('Array position does not exist (:1)', array(':1' => $position)));
    }

    // offset
    $offset = 0;

    // loop through array
    foreach ($array as $key => $value)
    {
        // increase offset
        ++$offset;

        // break if key has been found
        if ($key == $position)
        {
            break;
        }
    }

    $array = array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, null, true);

    return $array;
}
