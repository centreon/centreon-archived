<?php

namespace Core\TimePeriod\Domain\Model;

class NewTimePeriod
{
    public function __construct(
        private string $name,
        private string $alias
    ) {}
}
