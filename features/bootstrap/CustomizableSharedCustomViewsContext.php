<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\CustomViewsPage;
use Centreon\Test\Behat\ContactConfigurationPage;


class CustomizableSharedCustomViewsContext extends CustomViewsContext
{
    /**
     *  Build a new context.
     */
    public function __construct()
    {
        parent::__construct();
    }
}
