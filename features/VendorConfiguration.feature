Feature: Edit a vendor
    As a Centreon user
    I want to manipulate a vendor
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a vendor is configured

    Scenario: Change the properties of a vendor
        When I change the properties of a vendor
        Then the properties are updated

    Scenario: Duplicate one existing vendor
        When I duplicate a vendor
        Then the new vendor has the same properties

    Scenario: Delete one existing vendor
        When I delete a vendor
        Then the deleted object is not displayed in the list
