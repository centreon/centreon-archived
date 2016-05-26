Feature: Expire Ack
    As a Centreon user
    I want to configure expiration of acknowledgements
    To set a time limit to acknowledgements

    Background:
        Given a Centreon server
        And I am logged in

    Scenario: Check Host Acknowledgement Expiration
        Given a host configured with expirations
        And the host is in a critical state
        And the host is acknowledged
        When I wait the time limit set for expirations
        Then the host acknowledgement disappears

    Scenario: Check Service Acknowledgement Expiration
        Given a host configured with expirations
        And a service associated with this host
        And the service is in a critical state
        And the service is acknowledged
        When I wait the time limit set for expirations
        Then the service acknowledgement disappears
