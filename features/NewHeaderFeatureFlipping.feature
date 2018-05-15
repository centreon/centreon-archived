Feature: New Header Feature Flipping
    As a Centreon User
    I want to choose to activate the new top counter feature
    To have the new top counter design

    Background:
        Given a Centreon server

    Scenario: Accept the new header feature
        Given I am logged in waiting new feature validation
        When I accept the new header feature
        Then I see the new header

    Scenario: Decline the new header feature
        Given I am logged in waiting new feature validation
        When I decline the new header feature
        Then I see the legacy header

    Scenario: Switch from the new header to legacy header
        Given I am logged in
        When I change the version of header in my profile to legacy
        Then I see the legacy header

    Scenario: Switch from the legacy header to new header
        Given I am logged in with new feature
        When I change the version of header in my profile to new
        Then I see the new header
