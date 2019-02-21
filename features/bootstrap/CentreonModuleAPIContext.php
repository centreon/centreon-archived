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
        //to be added later if we decide to use another module for the test, for now centreon-license-manager is used
    }
}
