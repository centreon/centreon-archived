Feature:
  In order to monitor resources
  As a user
  I want to submit result to those resources

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Acknowledge and disacknowledge resources
    Given I am logged in
    And the following CLAPI import data:
    """
    CMD;ADD;dummy_down;check;exit 2
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    HOST;SETPARAM;test;check_command;dummy_down
    """
    And the configuration is generated and exported
    And I wait until host "test" is monitored
    And I send a GET request to '/api/v10.10/monitoring/hosts?search={"host.name":"test"}'
    And I store response values in:
      | name   | path         |
      | hostId | result[0].id |
    And I send a POST request to '/api/beta/monitoring/hosts/<hostId>/check' with body:
    """
    {}
    """
    And I wait to get 1 result from "/api/beta/monitoring/hosts/<hostId>/timeline" (tries: 30)

    When I send a POST request to '/api/beta/monitoring/resources/acknowledge' with body:
    """
    {
      "acknowledgement": {
        "comment": "Acknowledged by admin",
        "is_notify_contacts": false,
        "with_services": false
      },
      "resources": [
        {
          "type": "host",
          "id": <hostId>
        }
      ]
    }
    """
    Then I wait to get 1 result from "/api/v21.10/monitoring/hosts/<hostId>/acknowledgements" (tries: 30)

    When I send a DELETE request to '/api/v21.10/monitoring/resources/acknowledgements' with body:
    """
    {
      "disacknowledgement": {
        "with_services": false
      },
      "resources": [
        {
          "type": "host",
          "id": <hostId>
        }
      ]
    }
    """
    Then I wait to get 0 result from "/api/v21.10/monitoring/hosts/<hostId>/acknowledgements" (tries: 30)
