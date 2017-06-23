Feature: HostDuplicationCheck
    As a Centreon admin user
    I want to duplicate a host
    To see if the Properties have changed

    Background:
        Given I am logged in a Centreon server
        And a host is created

    Scenario: Duplicate a host and check the properties
        When I duplicate a host
        Then the host was correctly duplicated
        And their alias are the same
        And their address are the same
        And their status are the same
