<?php

namespace Centreon\Application\Validation;

interface CentreonValidatorInterface
{
    /**
     * Returns array of dependencies for the validator class
     *
     * @return array An array of dependencies
     */
    public static function dependencies();
}