Feature: Check health of the Monitoring - Host API
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And Exchange user identity token for admin user

  Scenario: List all hosts without services
    When I make a GET request to "/api/beta/monitoring/hosts?show_service=false"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{},"sort_by":{},"total":1}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":14,"poller_id":1,"name":"Centreon-Server","acknowledged":false,"address_ip":"127.0.0.1","alias":"Monitoring Server","check_attempt":1,"checked":false,"display_name":"Centreon-Server","execution_time":0,"icon_image":"","icon_image_alt":"","last_check":null,"last_hard_state_change":null,"last_state_change":null,"last_time_down":null,"last_time_unreachable":null,"last_time_up":null,"last_update":"*","max_check_attempts":5,"output":"","passive_checks":false,"state":4,"state_type":1,"timezone":""}]
    """

  Scenario: List all hosts with services
    When I make a GET request to "/api/beta/monitoring/hosts?show_service=true"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{},"sort_by":{},"total":1}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":14,"poller_id":1,"name":"Centreon-Server","acknowledged":false,"address_ip":"127.0.0.1","alias":"Monitoring Server","check_attempt":1,"checked":false,"display_name":"Centreon-Server","execution_time":0,"icon_image":"","icon_image_alt":"","last_check":null,"last_hard_state_change":null,"last_state_change":null,"last_time_down":null,"last_time_unreachable":null,"last_time_up":null,"last_update":"*","max_check_attempts":5,"output":"","passive_checks":false,"services":[{"id":19,"description":"Disk-\/","display_name":"Disk-\/","state":4},{"id":24,"description":"Load","display_name":"Load","state":4},{"id":25,"description":"Memory","display_name":"Memory","state":4},{"id":26,"description":"Ping","display_name":"Ping","state":4}],"state":4,"state_type":1,"timezone":""}]
    """

  Scenario: List hosts and search by host.name
    When I make a GET request to "/api/beta/monitoring/hosts?show_service=false&search={%22host.name%22:%22Centreon-Server%22}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"host.name":"Centreon-Server"}},"sort_by":{},"total":1}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":14,"poller_id":1,"name":"Centreon-Server","acknowledged":false,"address_ip":"127.0.0.1","alias":"Monitoring Server","check_attempt":1,"checked":true,"display_name":"Centreon-Server","execution_time":0.141715,"icon_image":"","icon_image_alt":"","last_check":"*","last_hard_state_change":null,"last_state_change":null,"last_time_down":null,"last_time_unreachable":null,"last_time_up":"*","last_update":"*","max_check_attempts":5,"output":"OK - 127.0.0.1 rta 0.070ms lost 0%\n","passive_checks":false,"state":0,"state_type":1,"timezone":""}]
    """
    When I make a GET request to "/api/beta/monitoring/hosts?show_service=false&search={%22host.name%22:%22MissingOne%22}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"host.name":"MissingOne"}},"sort_by":{},"total":0}
    """
    And the property "result" has value
    """
    []
    """

  Scenario: List hosts by status (filter on status)
    When I make a GET request to "/api/beta/monitoring/hosts?show_service=false&search={%22host.state%22:1}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"host.state":1}},"sort_by":[],"total":0}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":14,"poller_id":1,"name":"Centreon-Server","acknowledged":false,"address_ip":"127.0.0.1","alias":"Monitoring Server","check_attempt":1,"checked":true,"display_name":"Centreon-Server","execution_time":0.0,"icon_image":"","icon_image_alt":"","last_check":"*","last_hard_state_change":"*","last_state_change":"*","last_time_down":"*","last_time_unreachable":null,"last_time_up":"*","last_update":"*","max_check_attempts":5,"output":"OK - 127.0.0.1 rta 0.074ms lost 0%\n","passive_checks":false,"state":1,"state_type":0,"timezone":""}]
    """
    When I make a GET request to "/api/beta/monitoring/hosts?show_service=false&search={%22host.state%22:0}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"host.state":0}},"sort_by":{},"total":0}
    """
    And the property "result" has value
    """
    []
    """

  Scenario: List hosts by hostgroup
    When I make a GET request to "/api/beta/monitoring/hostgroups"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":[],"sort_by":[],"total":1}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":53,"hosts":[{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","services":[{"id":19,"description":"Disk-/","display_name":"Disk-/","state":4},{"id":24,"description":"Load","display_name":"Load","state":3},{"id":25,"description":"Memory","display_name":"Memory","state":3},{"id":26,"description":"Ping","display_name":"Ping","state":0}]
    """
