Feature:
  In order to monitor resources
  As a user
  I want to schedule checks on those resources

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Check resources
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    SERVICE;ADD;test;test_service1;Ping-LAN;
    """
    And the configuration is generated and exported
    And I wait until service "test_service1" from host "test" is monitored
    And I send a GET request to '/api/v21.10/monitoring/services?search={"$and":[{"host.name":"test"},{"service.description":"test_service1"}]}'
    And I store response values in:
      | name      | path              |
      | hostId    | result[0].host.id |
      | serviceId | result[0].id      |

    When I send a POST request to '/api/v21.10/monitoring/resources/check' with body:
    """
    {
      "resources": [
        {
          "type": "host",
          "id": <hostId>,
          "parent": null
        },
        {
          "type": "service",
          "id": <serviceId>,
          "parent": {
            "id": <hostId>
          }
        }
      ]
    }
    """

    Then the response code should be 204
    And the content of file "/var/log/centreon-engine/centengine.log" should match "/SCHEDULE_FORCED_HOST_CHECK;test/"
    And the content of file "/var/log/centreon-engine/centengine.log" should match "/SCHEDULE_FORCED_SVC_CHECK;test;test_service1/" (tries: 3)
