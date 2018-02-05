Feature: Centreon broker Configuration
    As a Centreon user
    I want to configure broker

    Background:
        Given I am logged in a Centreon server

    Scenario: add custom output
        When I add a custom output
        Then the output is saved
