Feature:
  In order to update the monitoring servers configuration
  As a user
  I want to genereate and reload the configuration files

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Generate and move configuration for a monitoring server
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;host_test;Test host;127.0.0.1;generic-host;central;
    SERVICE;ADD;host_test;service_ping;Ping-LAN
    """

    When I want to generate the monitoring server configuration #1
    Then the response code should be 204

    When I want to reload the monitoring server configuration #1
    Then the response code should be 204

    And I wait until host "host_test" is monitored
    And I wait until service "service_ping" from host "host_test" is monitored
