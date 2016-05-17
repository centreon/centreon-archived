Feature: Expire Ack
    As a Centreon user
    I want to configure expiration of acknowledgements
    To set a time limit to acknowledgements

    Background:
        Given a Centreon server
        And I am logged in

    Scenario: Check Host Acknowledgement Expiration
        Given a host configured with expirations
        And in a critical state
        And acknowledged
        When I wait the time limit set for expirations
        Then the acknowledgement disappears

    Scenario: Check Service Acknowledgement Expiration
        Given a service with a host configured with expirations
        And in a critical state
        And acknowledged
        When I wait the time limit set for expirations
        Then the acknowledgement disappears
