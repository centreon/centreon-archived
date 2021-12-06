Feature: Custom views
    As a Centreon user
    I want to share my custom views
    So that other users can benefit from it

    Background:
        Given I am logged in a Centreon server with some widgets

# user shared not locked views
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

    Scenario: Modify an unlocked shared view and applies changes
        Given a shared custom view
        And the user is using the shared view
        When the user modifies the custom view
        Then the changes are reflected on all users displaying the custom view
        #Then a warning is shown to the user who wants to apply the changes

    Scenario: Deletion of an unlocked shared view
        Given a shared custom view
        And the user is using the shared view
        When the owner removes the view
        Then the view remains visible for all users displaying the custom view
        And the view is removed for the owner

# contact groups shared not locked views
    Scenario: Modify a shared view with groups
        Given a shared custom view with a group
        When the user is using the shared view
        Then he can modify the content of the shared view

    Scenario: Remove an unlocked shared view with groups
        Given a shared custom view with a group
        And the user is using the shared view
        When he removes the shared view
        Then the view is not visible anymore
        And the user can use the shared view again

    Scenario: Modify an unlocked shared view with groups and applies changes
        Given a shared custom view with a group
        And the user is using the shared view
        When the user modifies the custom view
        Then the changes are reflected on all users displaying the custom view
        #Then a warning is shown to the user who wants to apply the changes

    Scenario: Deletion of an unlocked shared view with groups
        Given a shared custom view with a group
        And the user is using the shared view
        When the owner removes the view
        Then the view remains visible for all users displaying the custom view
        And the view is removed for the owner
