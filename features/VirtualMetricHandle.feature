Feature: Virtual Metric Handle
    As a Centreon user
    I want to use virtual metric
    To calculate specific values I need to check

    Background:
        Given I am logged in a Centreon server with configured metrics

    Scenario: Create a virtual metric
        When I add a virtual metric
        Then all properties are saved

    Scenario: Duplicate a virtual metric
        Given an existing virtual metric
        When I duplicate a virtual metric
        Then all properties are copied except the name

    Scenario: Delete a virtual metric
        Given an existing virtual metric
        When I delete a virtual metric
        Then the virtual metric disappears from the Virtual metrics list
