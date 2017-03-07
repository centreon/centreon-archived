Feature: Print configuration warnings
    As a Centreon user
    I want to know configuration issues
    So that I can fix them

    Background:
        Given I am logged in a Centreon server

    Scenario: Notifications enabled on service without notification period
        Given a service with notifications enabled
        And the service has no notification period
        When the configuration is exported
        Then a warning message is printed
