#features/CommandsApi.feature
@api
Feature: Check health of the Command APIs
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And I have a running instance of Centreon API

  @command
  Scenario: Healthcheck of Command APIs
    # List
    When I make a GET request to "/api/index.php?object=centreon_command&action=list"
    Then the response code should be 200
    And the response has a "result" property
    And the response has a "status" property
    And the property "result" has value
    """
    {
        "pagination":{
            "total":52,
            "offset":0,
            "limit":52
        },
        "entities":[
            {"id":96,"name":"check_centreon_cpu"},
            {"id":4,"name":"check_centreon_dummy"},
            {"id":5,"name":"check_centreon_load_average"},
            {"id":97,"name":"check_centreon_memory"},
            {"id":59,"name":"check_centreon_nb_connections"},
            {"id":34,"name":"check_centreon_nt"},
            {"id":6,"name":"check_centreon_ping"},
            {"id":7,"name":"check_centreon_process"},
            {"id":8,"name":"check_centreon_remote_storage"},
            {"id":95,"name":"check_centreon_snmp_proc_detailed"},
            {"id":94,"name":"check_centreon_snmp_value"},
            {"id":62,"name":"check_centreon_traffic"},
            {"id":10,"name":"check_centreon_traffic_limited"},
            {"id":44,"name":"check_centreon_uptime"},
            {"id":29,"name":"check_dhcp"},
            {"id":30,"name":"check_dig"},
            {"id":2,"name":"check_disk_smb"},
            {"id":3,"name":"check_distant_disk_space"},
            {"id":27,"name":"check_dns"},
            {"id":28,"name":"check_ftp"},
            {"id":1,"name":"check_host_alive"},
            {"id":11,"name":"check_hpjd"},
            {"id":12,"name":"check_http"},
            {"id":13,"name":"check_https"},
            {"id":76,"name":"check_load_average"},
            {"id":77,"name":"check_local_cpu_load"},
            {"id":15,"name":"check_local_disk"},
            {"id":78,"name":"check_local_disk_space"},
            {"id":16,"name":"check_local_load"},
            {"id":17,"name":"check_local_procs"},
            {"id":14,"name":"check_local_swap"},
            {"id":18,"name":"check_local_users"},
            {"id":79,"name":"check_maxq"},
            {"id":24,"name":"check_nntp"},
            {"id":20,"name":"check_nt_cpu"},
            {"id":21,"name":"check_nt_disk"},
            {"id":19,"name":"check_nt_memuse"},
            {"id":25,"name":"check_pop"},
            {"id":26,"name":"check_smtp"},
            {"id":31,"name":"check_snmp"},
            {"id":23,"name":"check_tcp"},
            {"id":32,"name":"check_telnet"},
            {"id":33,"name":"check_udp"},
            {"id":35,"name":"host-notify-by-email"},
            {"id":37,"name":"host-notify-by-epager"},
            {"id":89,"name":"host-notify-by-jabber"},
            {"id":41,"name":"process-service-perfdata"},
            {"id":36,"name":"service-notify-by-email"},
            {"id":38,"name":"service-notify-by-epager"},
            {"id":90,"name":"service-notify-by-jabber"},
            {"id":39,"name":"submit-host-check-result"},
            {"id":40,"name":"submit-service-check-result"}
        ]
    }
    """