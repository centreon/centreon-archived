Feature: Acknowledgement Timeout
    As a Centreon user
    I want to configure expiration of acknowledgements
    To set a time limit to acknowledgements

    Background:
        Given I am logged in a Centreon server

    Scenario: Check Host Acknowledgement Expiration
        Given a host configured with acknowledgement expiration
        And the host is down
        And the host is acknowledged
        When I wait the time limit set for expiration
        Then the host acknowledgement disappears

    Scenario: Check Service Acknowledgement Expiration
        Given a service configured with acknowledgement expiration
        And the service is in a critical state
        And the service is acknowledged
        When I wait the time limit set for expiration
        Then the service acknowledgement disappears
