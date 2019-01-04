Feature: Language Selection
    As a Centreon Web user
    I want to change my language

    Background:
        Given I am logged in a Centreon server

    Scenario: Listing Language Select options
        Given I go to my account page
        When I select the language dropdown
        Then I can see unique human readable language options
