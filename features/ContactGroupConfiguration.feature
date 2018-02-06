Feature: ContactGroupConfiguration
    As a Centreon admin
    I want to modify an host
    To see if the modification is saved on the contact group page

    Background:
        Given I am logged in a Centreon server
        And a contact group is configured

    Scenario: Change the properties of a contact group
        When I update the contact group properties
        Then the contact group properties are updated

    Scenario: Duplicate one existing contact group
        When I duplicate a contact group
        Then the new contact group has the same properties

    Scenario: Delete one existing contact group
        When I delete a contact group
        Then the deleted contact group is not displayed in the list
