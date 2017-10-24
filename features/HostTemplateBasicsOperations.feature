Feature: HostTemplateBasicsOperations
    As a Centreon admin
    I want to manipulate a host template
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a host template is configured

    Scenario: I test the modification of a host template properties
        When I change the properties of a host template
        Then the properties are updated

    Scenario: I test the duplication of a host template
        When I duplicate a host template
        Then the new host template has the same properties

    Scenario: I test the deletion of a host template
        When I delete a host template
        Then the deleted host is not displayed in the host list
