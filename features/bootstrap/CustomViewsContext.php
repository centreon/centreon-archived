<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\CustomViewsPage;

class CustomViewsContext extends CentreonContext
{
    private $customViewName;

    /**
     *  Build a new context.
     */
    public function __construct()
    {
        $this->customViewName = 'AcceptanceTestCustomView';
    }

    /**
     *  @Given I am logged in a Centreon server with some widgets
     */
    public function iAmLoggedInCentreonWithWidgets()
    {
        $this->launchCentreonWebContainer('web_widgets');
        $this->iAmLoggedIn();
    }

    /**
     *  @Given a publicly shared custom view
     */
    public function aPubliclySharedCustomView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->createNewView($this->customViewName, 2, true);
        $page->addWidget('First widget', 'Host Monitoring');
        $page->addWidget('Second widget', 'Service Monitoring');
        $page->shareView('guest');
    }

    /**
     *  @Given a user is using the shared view
     *  @Given the user is using the shared view
     */
    public function aUserIsUsingThisSharedView()
    {
        /* $page = new ContactConfigurationBranch($this); */

    }

    /**
     *  @Given a custom view shared in read only with a user
     */
    public function aCustomViewSharedInReadOnlyWithAUser()
    {
        // XXX
    }

    /**
     *  @When a user wishes to add a new custom view
     *  @When the user wishes to add a new custom view
     */
    public function anotherUserWishesToAddANewCustomView()
    {
        // Nothing to do here, the view creation will be made
        // with a single call, in the next step.
    }

    /**
     *  @When he removes the shared view
     */
    public function thisOtherUserRemovesTheSharedView()
    {
        // XXX
    }

    /**
     *  @When the owner modifies the custom view
     */
    public function theOwnerModifiesTheCustomView()
    {
        // XXX
    }

    /**
     *  @When the owner removes the view
     */
    public function theOwnerRemovesTheView()
    {
        // XXX
    }

    /**
     *  @Then he can add the public view
     */
    public function heCanAddThePublicView()
    {
        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->loadView($this->customViewName);
    }

    /**
     *  @Then he can add the shared view
     */
    public function heCanAddTheSharedView()
    {

        $this->parameters['centreon_user'] = $this->user ;
        $this->iAmLoggedIn();

        $page = new CustomViewsPage($this);
        $page->showEditBar(true);
        $page->loadView(null, $this->customViewName);
    }

    /**
     *  @Then he cannot modify the content of the shared view
     */
    public function heCannotModifyTheContentOfTheSharedView()
    {
        if(!$this->assertFind('css', '.editView btnAction')->getAttribute('aria-disabled')){
            throw new Exception('The user can edit the view');
        };
    }

    /**
     *  @Then the view is not visible anymore
     */
    public function theViewIsNotVisibleAnymore()
    {
        // XXX
    }

    /**
     *  @Then the user can use it again
     */
    public function theUserCanUseItAgain()
    {
        // XXX
    }

    /**
     *  @Then the changes are reflected on all users displaying the custom view
     */
    public function theChangesAreReflectedOnAllUsersDisplayingTheCustomView()
    {
        // XXX
    }

    /**
     *  @Then the view is removed for all users displaying the custom view
     */
    public function theViewIsRemovedForAllUsersDisplayingTheCustomView()
    {
        // XXX
    }
}
