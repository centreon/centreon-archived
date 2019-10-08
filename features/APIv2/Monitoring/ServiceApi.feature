Feature: Check health of the Monitoring - Service API
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And Exchange user identity token for admin user

  Scenario: List services
    When I make a GET request to "/api/latest/monitoring/services"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{},"sort_by":{},"total":4}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":19,"check_attempt":1,"description":"Disk-/","display_name":"Disk-/","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"2019-10-08T14:34:37+02:00","last_state_change":"2019-10-08T14:34:37+02:00","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":24,"check_attempt":2,"description":"Load","display_name":"Load","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"2019-10-08T14:39:22+02:00","last_state_change":"2019-10-08T14:34:22+02:00","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":25,"check_attempt":1,"description":"Memory","display_name":"Memory","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"2019-10-08T14:37:07+02:00","last_state_change":"2019-10-08T14:32:07+02:00","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":26,"check_attempt":1,"description":"Ping","display_name":"Ping","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"2019-10-08T14:35:52+02:00","last_state_change":"*","max_check_attempts":3,"output":"OK - 127.0.0.1 rta 0.031ms lost 0%\n","state":0,"state_type":1}]
    """

  Scenario: List services and search by service.name
    When I make a GET request to "/api/latest/monitoring/services?search={%22service.display_name%22:%22Ping%22}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"service.display_name":"Ping"}},"sort_by":{},"total":1}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":26,"check_attempt":1,"description":"Ping","display_name":"Ping","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"2019-10-08T14:35:52+02:00","last_state_change":"*","max_check_attempts":3,"output":"OK - 127.0.0.1 rta 0.031ms lost 0%\n","state":0,"state_type":1}]
    """

  Scenario: List services by status
    When I make a GET request to "/api/latest/monitoring/services?search={%22service.activate%22:%221%22}"
    Then the response code should be 200

  Scenario: List services by servicegroup
    When I make a GET request to "/api/latest/monitoring/servicegroups"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{},"sort_by":{},"total":0}
    """
    And the property "result" has value matched to the pattern
    """
    []
    """

  Scenario: List services of a host
    When I make a GET request to "/api/latest/monitoring/services?search={%22host.name%22:%22Centreon-Server%22}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"host.name":"Centreon-Server"}},"sort_by":{},"total":4}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":19,"check_attempt":1,"description":"Disk-/","display_name":"Disk-/","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"2019-10-08T14:39:37+02:00","last_state_change":"2019-10-08T14:34:37+02:00","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":24,"check_attempt":2,"description":"Load","display_name":"Load","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"2019-10-08T14:39:22+02:00","last_state_change":"2019-10-08T14:34:22+02:00","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":25,"check_attempt":1,"description":"Memory","display_name":"Memory","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"2019-10-08T14:42:07+02:00","last_state_change":"2019-10-08T14:32:07+02:00","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":26,"check_attempt":1,"description":"Ping","display_name":"Ping","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"OK - 127.0.0.1 rta 0.047ms lost 0%\n","state":0,"state_type":1}]
    """

  Scenario: List one service of a host
    When I make a GET request to "/api/latest/monitoring/hosts/19/services/26"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{},"sort_by":{},"total":0}
    """
    And the property "result" has value matched to the pattern
    """
    []
    """
