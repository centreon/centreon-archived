Feature: Check health of the Monitoring - Service API
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And Exchange user identity token for admin user

  Scenario: List services
    When I make a GET request to "/api/beta/monitoring/services"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{},"sort_by":{},"total":4}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":19,"check_attempt":1,"description":"Disk-/","display_name":"Disk-/","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":24,"check_attempt":2,"description":"Load","display_name":"Load","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":25,"check_attempt":1,"description":"Memory","display_name":"Memory","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":26,"check_attempt":1,"description":"Ping","display_name":"Ping","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"OK - 127.0.0.1 rta 0.031ms lost 0%\n","state":0,"state_type":1}]
    """

  Scenario: List services and search by service.name
    When I make a GET request to "/api/beta/monitoring/services?search={%22service.display_name%22:%22Ping%22}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"service.display_name":"Ping"}},"sort_by":{},"total":1}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":26,"check_attempt":1,"description":"Ping","display_name":"Ping","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"OK - 127.0.0.1 rta 0.031ms lost 0%\n","state":0,"state_type":1}]
    """

  Scenario: List services by status
    When I make a GET request to "/api/beta/monitoring/services?search={%22service.state%22:%221%22}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"service.state":"1"}},"sort_by":[],"total":0}
    """
    And the property "result" has value
    """
    []
    """
    When I make a GET request to "/api/beta/monitoring/services?search={%22service.state%22:%220%22}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"service.state":"0"}},"sort_by":[],"total":0}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":26,"check_attempt":1,"description":"Ping","display_name":"Ping","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":0},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"OK - 127.0.0.1 rta 0.065ms lost 0%\n","state":0,"state_type":1}]
    """

  Scenario: List services by servicegroup
    When I make a GET request to "/api/beta/monitoring/servicegroups"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{},"sort_by":{},"total":0}
    """
    And the property "result" has value
    """
    []
    """

  Scenario: List services of a host
    When I make a GET request to "/api/beta/monitoring/services?search={%22host.name%22:%22Centreon-Server%22}"
    Then the response code should be 200
    And the property "meta" has value
    """
    {"page":1,"limit":10,"search":{"$and":{"host.name":"Centreon-Server"}},"sort_by":{},"total":4}
    """
    And the property "result" has value matched to the pattern
    """
    [{"id":19,"check_attempt":1,"description":"Disk-/","display_name":"Disk-/","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":24,"check_attempt":2,"description":"Load","display_name":"Load","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":25,"check_attempt":1,"description":"Memory","display_name":"Memory","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"(No output returned from plugin)\n","state":3,"state_type":1},{"id":26,"check_attempt":1,"description":"Ping","display_name":"Ping","host":{"id":14,"name":"Centreon-Server","alias":"Monitoring Server","display_name":"Centreon-Server","state":1},"icon_image":"","icon_image_alt":"","last_check":"*","last_state_change":"*","max_check_attempts":3,"output":"OK - 127.0.0.1 rta 0.047ms lost 0%\n","state":0,"state_type":1}]
    """

  Scenario: List one service of a host
    When I make a GET request to "/api/beta/monitoring/hosts/14/services/19"
    Then the response code should be 200
    And the response matched to the pattern
    """
    {"id":19,"":1,"":"check_centreon_remote_storage!/!80!90","":5.0,"":"24x7","":0,"":"/usr/lib64/nagios/plugins/check_centreon_snmp_remote_storage -H 127.0.0.1 -n -d / -w 80 -c 90 -v 2c -C public","description":"Disk-/","display_name":"Disk-/","execution_time":0.200257,"icon_image":"","icon_image_alt":"","is_acknowledged":false,"is_active_check":true,"is_checked":true,"scheduled_downtime_depth":0,"last_check":"*","last_hard_state_change":"*","last_notification":null,"last_time_critical":null,"last_time_ok":null,"last_time_unknown":"*","last_time_warning":null,"last_update":"*","last_state_change":"*","latency":0.075,"max_check_attempts":3,"next_check":"*","output":"(No output returned from plugin)\n","performance_data":"","state":3,"state_type":1}
    """
