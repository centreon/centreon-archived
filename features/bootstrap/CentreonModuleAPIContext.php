<?php
/*
** Copyright 2016 Centreon
**
** All rights reserved.
*/

use Centreon\Test\Behat\CentreonAPIContext;

class CentreonModuleAPIContext extends CentreonAPIContext
{
    /**
     * @Given I have a non-installed module ready for installation
     */
    public function iHaveNonInstalledModuleReady()
    {
        $this->container->execute(
            'mkdir /usr/share/centreon/www/modules/centreon-test',
            'web',
            true
        );

        $this->container->copyToContainer(
            __DIR__ . '/../assets/centreon-test.conf.php',
            '/usr/share/centreon/www/modules/centreon-test/conf.php',
            'web'
        );
    }
}
