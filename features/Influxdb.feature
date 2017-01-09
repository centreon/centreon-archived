Feature: Influxdb
    As a Centreon user
    I want to save metrics and status in Influxdb
    So that I can use Influxdb data in other products

    Background:
        Given I am logged in a Centreon server with Influxdb

    Scenario: Influxdb data save
        Given an Influxdb output is properly configured
        And a passive service is configured
        And Broker and Engine are restarted
        When new metric data is discovered by the engine for the service
        Then it is saved in Influxdb
