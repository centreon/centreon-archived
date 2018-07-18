Feature: Top Counter Profile Menu
    As a Centreon User
    I want to use the top counter profile menu
    To manage my profile

    Background:
        Given a Centreon server
        And I am logged in with new feature

    Scenario: Go to my profile edit form
        When I click to edit profile link
        Then I see my profile edit form

    Scenario: Logout
        When I click to logout link
        Then I see the login page
