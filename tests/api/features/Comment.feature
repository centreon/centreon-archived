Feature:
  In order to identify issues on resources
  As a user
  I want to add a comment on those resources

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Add comment to resources
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    SERVICE;ADD;test;test_service_1;Ping-LAN;
    """
    And the configuration is generated and exported
    And I wait until service "test_service_1" from host "test" is monitored
    And I send a GET request to '/beta/monitoring/services?search={"$and":[{"host.name":"test"},{"service.description":"test_service_1"}]}'
    And I store response values in:
      | name      | path              |
      | hostId    | result[0].host.id |
      | serviceId | result[0].id      |

    When I send a POST request to '/beta/monitoring/resources/comments' with body:
    """
    {
      "resources": [
        {
          "type": "host",
          "id": <hostId>,
          "parent": null,
          "comment": "This happened because wire has been unplugged",
          "date": null
        },
        {
          "type": "service",
          "id": <serviceId>,
          "parent": {
            "id": <hostId>
          },
          "comment": "This happened because the ntpd service is stopped",
          "date": null
        }
      ]
    }
    """

    Then the response code should be 204
    And the content of file "/var/log/centreon-engine/centengine.log" should match "/ADD_HOST_COMMENT;test/"
    And the content of file "/var/log/centreon-engine/centengine.log" should match "/ADD_SVC_COMMENT;test;test_service_1/" (tries: 3)
