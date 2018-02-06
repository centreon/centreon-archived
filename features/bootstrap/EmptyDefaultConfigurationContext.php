<?php

use Centreon\Test\Behat\CentreonContext;

/**
 * Defines application features from the specific context.
 */
class EmptyDefaultConfigurationContext extends CentreonContext
{
    /**
     * @When I list the :arg1
     */
    public function iListThe($arg1)
    {
        switch ($arg1) {
            case 'host template':
                $p = '/main.php?p=60103';
                break;
            case 'service template':
                $p = '/main.php?p=60206';
                break;
            case 'command':
                $p = '/main.php?p=60801&type=2';
                break;
            default:
                throw new Exception('Page not know');
                break;
        }
        $this->visit($p);
    }

    /**
     * @Then no item is display
     */
    public function noItemIsDisplay()
    {
        $table = $this->assertFind('css', 'table.ListTable');
        $list = $table->findAll('css', 'tr');
        if (count($list) != 1) {
            throw new Exception('Some items are presents.');
        }
    }
}
