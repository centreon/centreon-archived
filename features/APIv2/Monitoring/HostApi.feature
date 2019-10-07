Feature: Check health of the Monitoring - Host API
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And Exchange user identity token for admin user

  Scenario: List all hosts without services
    When I make a GET request to "/api/latest/monitoring/hosts?show_service=false"
    Then the response code should be 200

  Scenario: List all hosts with services
    When I make a GET request to "/api/latest/monitoring/hosts?show_service=true"
    Then the response code should be 200

  Scenario: List hosts and search by host.name
    When I make a GET request to "/api/latest/monitoring/hosts?show_service=false&search={%22host.name%22:%22esx-alger-01%22}"
    Then the response code should be 200

  Scenario: List hosts by status (filter on status)
    When I make a GET request to "/api/latest/monitoring/hosts?show_service=false&search={%22host.state%22:1}"
    Then the response code should be 200

  Scenario: List hosts by hostgroup
    When I make a GET request to "/api/latest/monitoring/hostgroups"
    Then the response code should be 200
