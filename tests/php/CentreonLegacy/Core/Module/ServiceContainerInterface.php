<?php
/**
 * Created by PhpStorm.
 * User: kduret
 * Date: 06/04/2017
 * Time: 10:17
 */

namespace CentreonLegacy\Core\Module;


interface ServiceContainerInterface
{
    public function registerProvider(ServiceProviderInterface $serviceProvider);

    public function terminate();
}