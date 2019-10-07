Feature: Check health of the Monitoring - Service API
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And Exchange user identity token for admin user

  Scenario: List services
    When I make a GET request to "/api/latest/monitoring/services"
    Then the response code should be 200

  Scenario: List services and search by service.name
    When I make a GET request to "/api/latest/monitoring/services?search={%22service.display_name%22:%22Ping%22}"
    Then the response code should be 200

  Scenario: List services by status
    When I make a GET request to "/api/latest/monitoring/services?search={%22service.activate%22:%221%22}"
    Then the response code should be 200

  Scenario: List services by servicegroup
    When I make a GET request to "/api/latest/monitoring/servicegroups"
    Then the response code should be 200

  Scenario: List services of a host
    When I make a GET request to "/api/latest/monitoring/services?search={%22host.name%22:%22esx-alger-01%22}"
    Then the response code should be 200

  Scenario: List one service of a host
    When I make a GET request to "/api/latest/monitoring/hosts/104/services/835"
    Then the response code should be 200
