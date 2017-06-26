Feature: HostDuplicationCheck
    As a Centreon admin user
    I want to duplicate a host
    To see if the Properties have changed

    Background:
        Given I am logged in a Centreon server

    Scenario: Duplicate a host and check the properties
        Given a host is created
        When I duplicate a host
        Then the host properties are updated
