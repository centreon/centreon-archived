Feature: Custom views
    As a Centreon user
    I want to share my custom views
    So that other users can benefit from it

    Background:
        Given I am logged in a Centreon server with some widgets

# public views
    Scenario: Share public custom view
        Given a publicly shared custom view
        When a user wishes to add a new custom view
        Then he can add the public view
        And he cannot modify the content of the shared view

    Scenario: Remove public share
        Given a publicly shared custom view
        And a user is using the public view
        When he removes the shared view
        Then the view is not visible anymore
        And the user can use the public view again

    Scenario: Remove public share by owner
        Given a publicly shared custom view
        And a user is using the public view
        When the owner removes the view
        Then the view is not visible anymore for the user