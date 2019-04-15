Feature: Meta-services acknowledgement
    As a Centreon Web user
    I want to acknowledge my monitoring objects
    So that I can filter my monitoring pages

    Background:
        Given I am logged in a Centreon server

    Scenario: Acknowlegde normal services
        Given a non-OK service
        When I acknowledge the service
        Then the service is marked as acknowledged

    Scenario: Acknowledge meta-services
        Given a non-OK meta-service
        When I acknowledge the meta-service
        Then the meta-service is marked as acknowledged
