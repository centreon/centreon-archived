<?php
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\MetaServiceConfigurationPage;

/**
 * Defines application features from the specific context.
 */
class ShareCustomViewsContext extends CentreonContext
{

    public function __construct()
    {

    }

    /**
     * @Given a user sharing a view in read only to one or more other users
     */
    public function aUserSharingAViewInReadOnlyToOneOrMoreOtherUsers()
    {
        throw new PendingException('todo');
    }

    /**
     * @Given a shared view in read only with a user
     */
    public function aSharedViewInReadOnlyWithAUser()
    {
        throw new PendingException('todo');
    }

    /**
     * @Given having been installed by users
     */
    public function havingBeenInstalledByUsers()
    {
        throw new PendingException('todo');
    }

    /**
     * @When the user want to add a new view
     */
    public function theUserWantToAddANewView()
    {
        throw new PendingException('todo');
    }

    /**
     * @When I remove the view
     */
    public function iRemoveTheView()
    {
        throw new PendingException('todo');
    }

    /**
     * @When the owner modifies this one
     */
    public function theOwnerModifiesThisOne()
    {
        throw new PendingException('todo');
    }

    /**
     * @When the owner remove the view
     */
    public function theOwnerRemoveTheView()
    {
        throw new PendingException('todo');
    }

    /**
     * @Then he can select this view shared with him without be able to modify his contents
     */
    public function heCanSelectThisViewSharedWithHimWithoutBeAbleToModifyHisContents()
    {
        throw new PendingException('todo');
    }

    /**
     * @Then this one is not visible any more
     */
    public function thisOneIsNotVisibleAnyMore()
    {
        throw new PendingException('todo');
    }

    /**
     * @Then I must be able to display later as long as this one is always shared with me
     */
    public function iMustBeAbleToDisplayLaterAsLongAsThisOneIsAlwaysSharedWithMe()
    {
        throw new PendingException('todo');
    }

    /**
     * @Then the impact is reflected on all the users displaying this one
     */
    public function theImpactIsReflectedOnAllTheUsersDisplayingThisOne()
    {
        throw new PendingException('todo');
    }

    /**
     * @Then this view is removed for all the users using it
     */
    public function thisViewIsRemovedForAllTheUsersUsingIt()
    {
        throw new PendingException('todo');
    }






}
