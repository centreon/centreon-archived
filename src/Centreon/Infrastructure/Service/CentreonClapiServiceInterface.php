<?php
namespace Centreon\Infrastructure\Service;

use Pimple\Container;

interface CentreonClapiServiceInterface
{

    public function __construct(Container $di);

    public static function getName(): string;
}
