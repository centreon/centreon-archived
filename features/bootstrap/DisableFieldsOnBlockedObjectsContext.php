<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\HostTemplateEditPage;
use Centreon\Test\Behat\HostTemplateListPage;


class DisableFieldsOnBlockedObjectsContext extends CentreonContext
{

    /**
     * @Given a blocked object template
     */
    public function aBlockedObjectTemplate()
    {
        $newHostTemplate = new HostTemplateEditPage($this);
        $newHostTemplate->setProperties(array(
            'name' => 'myHostTemplate',
            'alias' => 'myAlias',
            'address' => '127.0.0.1',
            'macros' => array('macro1' => '001')
        ));

        $newHostTemplate->save();

        $centreonDb = $this->getCentreonDatabase();
        $centreonDb->query("UPDATE host SET host_locked = 1 WHERE host_name = 'myHostTemplate'");

        $hostTemplate = new HostTemplateListPage($this);
        $hostTemplate = $hostTemplate->getTemplate('myHostTemplate');

        if (!$hostTemplate['locked']) {
            throw new \Exception('the host template' . $hostTemplate . 'is not locked');
        };
    }

    /**
     * @When i open the form
     */
    public function iOpenTheForm()
    {

        $hostTemplate = new HostTemplateListPage($this);
        $editHostTemplate = $hostTemplate->edit('myHostTemplate');

        return $editHostTemplate;
    }

    /**
     * @Then the fields are frozen
     */
    public function theFieldsAreFrozen()
    {
        $hostTemplate = $this->iOpenTheForm();
        $macro = $this->getSession()->getPage()->find('css', '#macro li.clone_template span input[type="text"]');
        if ($macro->getAttribute('disabled') != 'disabled' && $macro->getAttribute('readonly') != 'readonly') {
            throw new \Exception('the macros are not disabled');
        }
    }
}