<?php
namespace CentreonRemote\Application\Clapi;

use Pimple\Container;
use Centreon\Infrastructure\Service\CentreonClapiServiceInterface;

class CentreonRemoteServer implements CentreonClapiServiceInterface
{

    public function __construct(Container $di)
    {
        
    }

    public static function getName() : string
    {
        return (new \ReflectionClass(__CLASS__))->getShortName();
    }

    public function test($args): int
    {
        print_r($args);
        echo "OK\n";

        return 200;
    }
}
