Feature: Custom views
    As a Centreon user
    I want to share my custom views
    So that other users can benefit from it

    Background:
        Given I am logged in a Centreon server with some widgets

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
        And the user can use it again

#    Scenario: Share read-only custom view with users
#        Given a custom view shared in read only with a user
#        When the user wishes to add a new custom view
#        Then he can add the shared view
#        And he cannot modify the content of the shared view

#    Scenario: Remove read-only custom view shared with users
#        Given a custom view shared in read only with a user
#        And the user is using the shared view
#        When he removes the shared view
#        Then the view is not visible anymore
#        And the user can use it again

#    Scenario: Update a read only custom view shared with users
#        Given a custom view shared in read only with a user
#        When the owner modifies the custom view
#        Then the changes are reflected on all users displaying the custom view

#    Scenario: Delete a shared custom view
#        Given a custom view shared in read only with a user
#        And the user is using the shared view
#        When the owner removes the view
#        Then the view is removed for all users displaying the custom view
