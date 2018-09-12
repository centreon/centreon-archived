<?php
namespace Centreon\Infrastructure\Provider;

use Pimple\ServiceProviderInterface;

interface AutoloadServiceProviderInterface extends ServiceProviderInterface
{

    public static function order(): int;
}
