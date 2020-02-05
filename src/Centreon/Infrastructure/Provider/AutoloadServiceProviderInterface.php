<?php
namespace Centreon\Infrastructure\Provider;

use Pimple\ServiceProviderInterface;

interface AutoloadServiceProviderInterface extends ServiceProviderInterface
{
    /**
     * Set priority to load service provider
     *
     * @return integer
     */
    public static function order(): int;
}
