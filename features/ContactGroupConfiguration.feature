Feature: ContactGroupConfiguration
    As a Centreon admin
    I want to modify an host
    To see if the modification is saved on the contact group page

    Background:
        Given I am logged in a Centreon server
        And a contact group is configured

    Scenario: Configure the contact group
        When I configure the name of a contact group
        And I configure the alias of a contact group
        And I configure the status of a contact group
        And I configure the comment of a contact group
        Then the name has changed on the contact groups page
        And the alias has changed on the contact groups page
        And the status has changed on the contact groups page
        And the comment has changed on the contact groups page
