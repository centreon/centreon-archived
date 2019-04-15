Feature: Recovery notification delay
    As a Centreon user
    I want to discard notifications of nodes that did not stay long enough in a non-OK state
    So that I am not polluted with non-important notifications

    Background:
        Given I am logged in a Centreon server

    Scenario: Host first notification disabled before delay
        Given a host configured with first notification delay
        And the host is not UP
        When the host is still not UP before the first notification delay
        Then no notification is sent

    Scenario: Service first notification disabled before delay
        Given a service configured with first notification delay
        And the host is UP
        And the service is not OK
        When the service is still not OK before the first notification delay
        Then no notification is sent

    Scenario: Host first notification enabled after delay
        Given a host configured with first notification delay
        And the host is not UP
        When the host is still not UP after the first notification delay
        Then a notification is sent
        When the host is UP
        Then a notification is sent
        When the host is not UP
        And the host is still not UP before the first notification delay
        Then no notification is sent

    Scenario: Service first notification enabled after delay
        Given a service configured with first notification delay
        And the host is UP
        And the service is not OK
        When the service is still not OK after the first notification delay
        Then a notification is sent
        When the service is OK
        Then a notification is sent
        When the service is not OK
        And the service is still not OK before the first notification delay
        Then no notification is sent
