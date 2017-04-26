Feature: InfluxDB
    As a Centreon user
    I want to save metrics and status in InfluxDB
    So that I can use InfluxDB data in other products

    Background:
        Given I am logged in a Centreon server with InfluxDB

    Scenario: InfluxDB data save
        Given Centreon Broker is configured to send data to an InfluxDB server
        And a service is monitored by the Centreon platform
        When new metric data is retrieved for the service
        Then it is saved in InfluxDB
