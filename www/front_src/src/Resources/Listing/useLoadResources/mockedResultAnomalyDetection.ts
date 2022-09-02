export const mockedResutListingAD = {
  meta: {
    limit: 30,
    page: 1,
    search: {},
    sort_by: {
      last_status_change: 'DESC',
      status_severity_code: 'ASC',
    },
    total: 4,
  },
  result: [
    {
      acknowledged: false,
      active_checks: true,
      alias: null,
      duration: '25m 27s',
      chart_url: null,
      flapping: null,
      fqdn: null,
      icon: null,
      id: 26,
      in_downtime: false,
      information: 'OK - 127.0.0.1 rta 0.116ms lost 0%',
      last_check: '27s',
      last_status_change: '2022-09-02T13:30:46+02:00',
      links: {
        endpoints: {
          acknowledgement:
            '/centreon/api/latest/monitoring/hosts/14/services/26/acknowledgements?limit=1',
          details:
            '/centreon/api/latest/monitoring/resources/hosts/14/services/26',
          downtime:
            '/centreon/api/latest/monitoring/hosts/14/services/26/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1662119773%7D,%22end_time%22:%7B%22%24gt%22:1662119773%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1662119773%7D%7D%7D%7D%5D%7D',
          notification_policy: null,
          performance_graph:
            '/centreon/api/latest/monitoring/hosts/14/services/26/metrics/performance',
          status_graph:
            '/centreon/api/latest/monitoring/hosts/14/services/26/metrics/status',
          timeline:
            '/centreon/api/latest/monitoring/hosts/14/services/26/timeline',
        },
        externals: {
          action_url: '',
          notes: {
            label: '',
            url: '',
          },
        },
        uris: {
          configuration: '/centreon/main.php?p=60201&o=c&service_id=26',
          logs: '/centreon/main.php?p=20301&svc=14_26',
          reporting:
            '/centreon/main.php?p=30702&period=yesterday&start=&end=&host_id=14&item=26',
        },
      },
      monitoring_server_name: 'Central',
      name: 'Ping',
      notification_enabled: false,
      parent: {
        alias: 'Monitoring Server',
        id: 14,
        fqdn: '127.0.0.1',
        name: 'Centreon-Server',
        icon: null,
        short_type: 'h',
        links: {
          endpoints: {
            details: null,
            acknowledgement: null,
            status_graph: null,
            downtime: null,
            timeline: null,
            notification_policy: null,
            performance_graph: null,
          },
          externals: {
            action_url: null,
            notes: null,
          },
          uris: {
            configuration: null,
            logs: null,
            reporting: null,
          },
        },
        uuid: 'h14',
        status: {
          code: 0,
          name: 'UP',
          severity_code: 5,
        },
        type: 'host',
      },
      passive_checks: false,
      uuid: 'h14-s26',
      performance_data: null,
      severity: null,
      short_type: 's',
      status: {
        code: 0,
        name: 'OK',
        severity_code: 5,
      },
      type: 'service',
      tries: '1/3 (H)',
    },
  ],
};
