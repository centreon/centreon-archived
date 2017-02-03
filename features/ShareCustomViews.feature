#features/ShareCustomViews.feature

Feature: ShareCustomViews
    As a Centreon user
    I want to share my custom views
    So that the other Centreon users can profit

    Background:
        Given I am logged in a Centreon server

    Scenario: add a read only
        Given a user sharing a view in read only to one or more other users
        When the user want to add a new view
        Then he can select this view shared with him without be able to modify his contents

    Scenario: delete a read only
        Given a shared view in read only with a user
        When I remove the view
        Then this one is not visible any more
        And I must be able to display later as long as this one is always shared with me

    Scenario: update a read only
        Given a user sharing a view in read only to one or more other users
        When the owner modifies this one
        Then the impact is reflected on all the users displaying this one

    Scenario: delete a shared view
        Given a user sharing a view in read only to one or more other users
        And having been installed by users
        When the owner remove the view
        Then this view is removed for all the users using it












