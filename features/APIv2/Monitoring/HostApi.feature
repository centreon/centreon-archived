Feature: Check health of the Monitoring - Host API
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And I have a running instance of Centreon API
    And Get authentication token for username 'admin' and password 'centreon'

  Scenario: List all hosts without services
    When I make a GET request to '/monitoring/hosts?show_service=false'
    Then the response code should be 200

  Scenario: List all hosts with services
    When I make a GET request to '/monitoring/hosts?show_service=true'
    Then the response code should be 200

  Scenario: List hosts and search by host.name
    When I make a GET request to '/monitoring/hosts?show_service=false&search={"host.name":"esx-alger-01"}'
    Then the response code should be 200

  Scenario: List hosts by status (filter on status)
    When I make a GET request to '/monitoring/hosts?show_service=false&search={"host.state":1}'
    Then the response code should be 200

  Scenario: List hosts by hostgroup
    When I make a GET request to '/monitoring/hostgroups'
    Then the response code should be 200