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
        And he cannot modify the content of the shared view

    Scenario: Remove public share
        Given a user sharing publicly a custom view
        And another user is using this shared view
        When this other user removes the shared view
        Then the view is not visible anymore
        And the user can use it again

    Scenario: Share read-only custom view with users
        Given a user sharing a view in read only to one or more other users
        When the user want to add a new view
        Then he can select this view shared with him without be able to modify his contents

    Scenario: Remove read-only custom view shared with users
        Given a shared view in read only with a user
        When I remove the view
        Then the view is not visible anymore
        And I must be able to display later as long as this one is always shared with me

    Scenario: Update a read only custom view shared with users
        Given a user sharing a view in read only to one or more other users
        When the owner modifies this one
        Then the impact is reflected on all the users displaying this one

    Scenario: Delete a shared custom view
        Given a user sharing a view in read only to one or more other users
        And having been installed by users
        When the owner remove the view
        Then this view is removed for all the users using it
