Feature: Recovery notification delay
    As a Centreon user
    I want to discard recovery notifications of nodes that did not stay long enough in a non-OK state
    So that I am not polluted with non-important notifications

    Background:
        Given I am logged in a Centreon server

    Scenario: Host recovery notification disabled before delay
        Given a host configured with recovery notification delay
        And the host is not UP
        When the host recovers before the recovery notification delay
        Then no recovery notification is sent

    Scenario: Service recovery notification disabled before delay
        Given a service configured with recovery notification delay
        And the host is UP
        And the service is not OK
        When the service recovers before the recovery notification delay
        Then no recovery notification is sent

    Scenario: Host recovery notification enabled after delay
        Given a host configured with recovery notification delay
        And the host is not UP
        And the host recovers before the recovery notification delay
        When the host receives a new check result after the recovery notification delay
        Then a recovery notification is sent

    Scenario: Service recovery notification enabled after delay
        Given a service configured with recovery notification delay
        And the host is UP
        And the service is not OK
        And the service recovers before the recovery notification delay
        When the service receives a new check result after the recovery notification delay
        Then a recovery notification is sent

