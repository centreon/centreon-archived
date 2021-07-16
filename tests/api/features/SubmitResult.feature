Feature:
  In order to monitor resources
  As a user
  I want to submit result to those resources

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Submit Result Resources
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    SERVICE;ADD;test;test_service_1;Ping-LAN;
    """
    And the configuration is generated and exported
    And I wait until service "test_service_1" from host "test" is monitored
    And I send a GET request to '/api/beta/monitoring/services?search={"$and":[{"host.name":"test"},{"service.description":"test_service_1"}]}'
    And I store response values in:
      | name      | path              |
      | hostId    | result[0].host.id |
      | serviceId | result[0].id      |

    When I send a POST request to '/api/beta/monitoring/resources/submit' with body:
    """
    {
      "resources": [
        {
          "type": "host",
          "id": <hostId>,
          "parent": null,
          "status": 2,
          "output": "Host DOWN",
          "performance_data": "nbproc: 0"
        },
        {
          "type": "service",
          "id": <serviceId>,
          "parent": {
            "id": <hostId>
          },
          "status": 2,
          "output": "Service CRITICAL",
          "performance_data": "nbproc: 0"
        }
      ]
    }
    """

    Then the response code should be 204
    And the content of file "/var/log/centreon-engine/centengine.log" should match "/PROCESS_HOST_CHECK_RESULT;test/"
    And the content of file "/var/log/centreon-engine/centengine.log" should match "/PROCESS_SERVICE_CHECK_RESULT;test;test_service_1/" (tries: 3)
