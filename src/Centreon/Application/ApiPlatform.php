<?php


namespace Centreon\Application;


class ApiPlatform
{
    /**
     * @var float
     */
    private $version;

    /**
     * @return float
     */
    public function getVersion(): float
    {
        return $this->version;
    }

    /**
     * @param float $version
     * @return ApiPlatform
     */
    public function setVersion(float $version): ApiPlatform
    {
        $this->version = $version;
        return $this;
    }
}