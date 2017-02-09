Feature: Customizable shared custom views
    As a Centreon user
    I want to share my custom views
    So that other Centreon users can enjoy it

    Background:
        Given I am logged in a Centreon server with some widgets 
        
    Scenario: Modify a shared view
        Given a shared custom view
        When the user is using the shared view
        Then he can modify the content of the shared view

    Scenario: Remove an unlocked shared view
        Given a shared custom view
        And the user is using the shared view
        When he removes the shared view
        Then the view is not visible anymore
        And the user can use the shared view again
        
    #Scenario: Modify an unlocked shared view and applies changes
        #Given a shared custom view
        #And the user is using the shared view
        #When he modifies the view
        #Then a warning is shown to the user who wants to apply the changes
        #And the changes are reflected on all users displaying the custom view

    #Scenario: Deletion of an unlocked shared view
        #Given a Centreon user sharing his unlocked custom view to one or many users
        #And that view is used by other users
        #When the owner delete the view
        #Then the view remains shared to all users who used it
        #And the view is removed for the owner
        