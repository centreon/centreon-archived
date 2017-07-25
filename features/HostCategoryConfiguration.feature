Feature: HostCategoryConfiguration
    As a Centreon admin
    I want to manipulate an host
    To see if all simples manipulations work

    Background:
        Given I am logged in a Centreon server
        And a host category is configured

    Scenario:
        When I change the properties of a host category
        Then the properties are updated

    Scenario:
        When I duplicate a host category
        Then the new host category has the same properties

    Scenario:
        When I delete a host category
        Then the deleted host is not displayed in the host list
