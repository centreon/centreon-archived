Feature: Custom views
    As a Centreon user
    I want to share my custom views
    So that other users can benefit from it

    Background:
        Given I am logged in a Centreon server with some widgets

    Scenario: Create public share
        Given a user sharing publicly a custom view
        When another user wishes to add a new custom view
        Then he can add the shared view
        And cannot modify the content of the shared view

    Scenario: Remove public share
        Given a user sharing publicly a custom view
        And another user is using this shared view
        When this other user is not using the shared view anymore
        Then the view is not visible anymore
        And the user can use it again
