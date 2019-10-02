<?php

namespace Centreon\Domain\Exception;

use Throwable;

class EntityNotFoundException extends \Exception
{
    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
