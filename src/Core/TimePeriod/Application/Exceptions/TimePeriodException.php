<?php

namespace Core\TimePeriod\Application\Exceptions;

class TimePeriodException extends \Exception
{
    /**
     * @param \Throwable $ex
     * @return self
     */
    public static function errorWhileSearchingForTimePeriods(\Throwable $ex): self
    {
        return new self(
            _('Error while searching for the time periods'),
            0,
            $ex
        );
    }
}
