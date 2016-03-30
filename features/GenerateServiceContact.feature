Feature: Generate service contact configuration
    As a Centreon admin
    I want to apply my service contacts and contact groups defined on the host
    To use these to replace the contacts and service contacts groups

    Background:
        Given a Centreon server
        And I am logged in

    Scenario: Configure checkbox Inherit only contacts/contacts group from host
        Given a one service associated on host
        And I am on Notifications tab
        When I check case yes
        Then I checkbox "Inherit contacts from host" are disabled

    Scenario: Configure inhertance contact
        Given a one service associated on host
        And I am on Notifications tab
        And a checkbox "Yes" in "Inherit only contacts/contacts group from host" is checked
        When I click on the button "save"
        Then I open service
        Then checkbox "Yes" in "Inherit contacts from host" is checked
