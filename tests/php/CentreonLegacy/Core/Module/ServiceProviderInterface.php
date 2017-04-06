<?php
/**
 * Created by PhpStorm.
 * User: kduret
 * Date: 06/04/2017
 * Time: 10:17
 */

namespace CentreonLegacy\Core\Module;


interface ServiceProviderInterface
{
    public function register(\Pimple $container);

    public function terminate(\Pimple $container);
}