Feature:
    In order to monitor services
    As a user
    I want to get service information using api

    Background:
        Given a running instance of Centreon Web API
        And the endpoints are described in Centreon Web API documentation

    Scenario: Service listing and details
        Given I am logged in
        And the following CLAPI import data:
        """
        HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
        SERVICE;ADD;test;test_service1;Ping-LAN;
        SERVICE;ADD;test;test_service2;Ping-LAN;
        """
        And the configuration is generated and exported
        And I wait until host "test" is monitored
        And I wait until service "test_service1" from host "test" is monitored

        When I send a GET request to '/api/beta/monitoring/services?search={"host.name":"test"}'
        Then the response code should be "200"
        And the JSON node "result" should have "2" elements

        When I send a GET request to '/api/beta/monitoring/services?search={"$and":[{"host.name":"test"},{"service.description":"test_service1"}]}'
        Then the response code should be "200"
        And the response should be formatted like JSON format "standard/listing.json"
        And the response should be formatted like JSON format "monitoring/service/listing.json"

        When I send a request to have the details of service "test_service1" from host "test"
        Then the response code should be "200"
        And the response should be formatted like JSON format "monitoring/service/details.json"
        And the JSON node "description" should be equal to the string "test_service1"
