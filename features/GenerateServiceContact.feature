Feature: Generate service contact configuration
    As a Centreon admin
    I want to apply my service contacts and contact groups defined on the host
    To use these to replace the contacts and service contacts groups

    Background:
        Given a Centreon server
        And I am logged in

    Scenario: Configure checkbox Inherit only contacts and contacts group from host
        Given a one service associated on host
        And I am on Notifications tab
        When I check case yes
        Then a case Inherit contacts are disabled
        And the field contact service are disabled
        And the field contact group service are disabled