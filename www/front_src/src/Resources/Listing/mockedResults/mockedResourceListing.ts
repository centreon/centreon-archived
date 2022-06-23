import { ResourceListing } from '../../models';

export const mockedResourceListing: ResourceListing = {
  meta: {
    limit: 30,
    page: 1,
    search: {},
    sort_by: {
      last_status_change: 'DESC',
      status_severity_code: 'ASC',
    },
    total: 5,
  },
  result: [
    {
      acknowledged: false,
      active_checks: true,
      alias: null,
      chart_url: null,
      duration: '3h 5m',
      flapping: false,
      fqdn: null,
      icon: null,
      id: 19,
      in_downtime: false,
      information: '(No output returned from plugin)',
      last_check: '2m 52s',
      last_status_change: '2022-06-23T13:36:23+02:00',
      links: {
        endpoints: {
          acknowledgement:
            '/centreon/api/latest/monitoring/hosts/14/services/19/acknowledgements?limit=1',
          details:
            '/centreon/api/latest/monitoring/resources/hosts/14/services/19',
          downtime:
            '/centreon/api/latest/monitoring/hosts/14/services/19/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1655995310%7D,%22end_time%22:%7B%22%24gt%22:1655995310%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1655995310%7D%7D%7D%7D%5D%7D',
          notification_policy:
            '/centreon/api/latest/configuration/hosts/14/services/19/notification-policy',
          performance_graph: null,
          status_graph:
            '/centreon/api/latest/monitoring/hosts/14/services/19/metrics/status',
          timeline:
            '/centreon/api/latest/monitoring/hosts/14/services/19/timeline',
        },
        externals: {
          action_url: '',
          notes: {
            label: '',
            url: '',
          },
        },
        uris: {
          configuration: '/centreon/main.php?p=60201&o=c&service_id=19',
          logs: '/centreon/main.php?p=20301&svc=14_19',
          reporting:
            '/centreon/main.php?p=30702&period=yesterday&start=&end=&host_id=14&item=19',
        },
      },
      monitoring_server_name: 'Central',
      name: 'Disk-/',
      notification_enabled: false,
      parent: {
        alias: 'Monitoring Server',
        fqdn: '127.0.0.1',
        icon: null,
        id: 14,
        links: {
          endpoints: {
            acknowledgement:
              '/centreon/api/latest/monitoring/hosts/14/acknowledgements?limit=1',
            details: '/centreon/api/latest/monitoring/resources/hosts/14',
            downtime:
              '/centreon/api/latest/monitoring/hosts/14/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1655995310%7D,%22end_time%22:%7B%22%24gt%22:1655995310%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1655995310%7D%7D%7D%7D%5D%7D',
            notification_policy:
              '/centreon/api/latest/configuration/hosts/14/notification-policy',
            performance_graph: null,
            status_graph: null,
            timeline: '/centreon/api/latest/monitoring/hosts/14/timeline',
          },
          externals: {
            action_url: null,
            notes: null,
          },
          uris: {
            configuration: '/centreon/main.php?p=60101&o=c&host_id=14',
            logs: '/centreon/main.php?p=20301&h=14',
            reporting: '/centreon/main.php?p=307&host=14',
          },
        },
        name: 'nooooooooni',
        short_type: 'h',
        status: {
          code: 0,
          name: 'UP',
          severity_code: 5,
        },
        type: 'host',
        uuid: 'h14',
      },
      passive_checks: false,
      performance_data: '',
      severity: null,
      severity_level: null,
      short_type: 's',
      status: {
        code: 3,
        name: 'UNKNOWN',
        severity_code: 3,
      },
      tries: '3/3 (H)',
      type: 'service',
      uuid: 'h14-s19',
    },
    {
      acknowledged: false,
      active_checks: true,
      alias: null,
      chart_url: null,
      duration: '3h 6m',
      flapping: false,
      fqdn: null,
      icon: null,
      id: 24,
      in_downtime: false,
      information: '(No output returned from plugin)',
      last_check: '4m 7s',
      last_status_change: '2022-06-23T13:35:08+02:00',
      links: {
        endpoints: {
          acknowledgement:
            '/centreon/api/latest/monitoring/hosts/14/services/24/acknowledgements?limit=1',
          details:
            '/centreon/api/latest/monitoring/resources/hosts/14/services/24',
          downtime:
            '/centreon/api/latest/monitoring/hosts/14/services/24/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1655995310%7D,%22end_time%22:%7B%22%24gt%22:1655995310%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1655995310%7D%7D%7D%7D%5D%7D',
          notification_policy:
            '/centreon/api/latest/configuration/hosts/14/services/24/notification-policy',
          performance_graph: null,
          status_graph:
            '/centreon/api/latest/monitoring/hosts/14/services/24/metrics/status',
          timeline:
            '/centreon/api/latest/monitoring/hosts/14/services/24/timeline',
        },
        externals: {
          action_url: '',
          notes: {
            label: '',
            url: '',
          },
        },
        uris: {
          configuration: '/centreon/main.php?p=60201&o=c&service_id=24',
          logs: '/centreon/main.php?p=20301&svc=14_24',
          reporting:
            '/centreon/main.php?p=30702&period=yesterday&start=&end=&host_id=14&item=24',
        },
      },
      monitoring_server_name: 'Central',
      name: 'Load',
      notification_enabled: false,
      parent: {
        alias: 'Monitoring Server',
        fqdn: '127.0.0.1',
        icon: null,
        id: 14,
        links: {
          endpoints: {
            acknowledgement:
              '/centreon/api/latest/monitoring/hosts/14/acknowledgements?limit=1',
            details: '/centreon/api/latest/monitoring/resources/hosts/14',
            downtime:
              '/centreon/api/latest/monitoring/hosts/14/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1655995310%7D,%22end_time%22:%7B%22%24gt%22:1655995310%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1655995310%7D%7D%7D%7D%5D%7D',
            notification_policy:
              '/centreon/api/latest/configuration/hosts/14/notification-policy',
            performance_graph: null,
            status_graph: null,
            timeline: '/centreon/api/latest/monitoring/hosts/14/timeline',
          },
          externals: {
            action_url: null,
            notes: null,
          },
          uris: {
            configuration: '/centreon/main.php?p=60101&o=c&host_id=14',
            logs: '/centreon/main.php?p=20301&h=14',
            reporting: '/centreon/main.php?p=307&host=14',
          },
        },
        name: 'Centreon-Server',
        short_type: 'h',
        status: {
          code: 0,
          name: 'UP',
          severity_code: 5,
        },
        type: 'host',
        uuid: 'h14',
      },
      passive_checks: false,
      performance_data: '',
      severity_level: null,
      short_type: 's',
      status: {
        code: 3,
        name: 'UNKNOWN',
        severity_code: 3,
      },
      tries: '3/3 (H)',
      type: 'service',
      uuid: 'h14-s24',
    },
    {
      acknowledged: false,
      active_checks: true,
      alias: null,
      chart_url: null,
      duration: '3h 7m',
      flapping: false,
      fqdn: null,
      icon: null,
      id: 25,
      in_downtime: false,
      information: '(No output returned from plugin)',
      last_check: '22s',
      last_status_change: '2022-06-23T13:33:53+02:00',
      links: {
        endpoints: {
          acknowledgement:
            '/centreon/api/latest/monitoring/hosts/14/services/25/acknowledgements?limit=1',
          details:
            '/centreon/api/latest/monitoring/resources/hosts/14/services/25',
          downtime:
            '/centreon/api/latest/monitoring/hosts/14/services/25/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1655995310%7D,%22end_time%22:%7B%22%24gt%22:1655995310%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1655995310%7D%7D%7D%7D%5D%7D',
          notification_policy:
            '/centreon/api/latest/configuration/hosts/14/services/25/notification-policy',
          performance_graph: null,
          status_graph:
            '/centreon/api/latest/monitoring/hosts/14/services/25/metrics/status',
          timeline:
            '/centreon/api/latest/monitoring/hosts/14/services/25/timeline',
        },
        externals: {
          action_url: '',
          notes: {
            label: '',
            url: '',
          },
        },
        uris: {
          configuration: '/centreon/main.php?p=60201&o=c&service_id=25',
          logs: '/centreon/main.php?p=20301&svc=14_25',
          reporting:
            '/centreon/main.php?p=30702&period=yesterday&start=&end=&host_id=14&item=25',
        },
      },
      monitoring_server_name: 'Central',
      name: 'Memory',
      notification_enabled: false,
      parent: {
        alias: 'Monitoring Server',
        fqdn: '127.0.0.1',
        icon: null,
        id: 14,
        links: {
          endpoints: {
            acknowledgement:
              '/centreon/api/latest/monitoring/hosts/14/acknowledgements?limit=1',
            details: '/centreon/api/latest/monitoring/resources/hosts/14',
            downtime:
              '/centreon/api/latest/monitoring/hosts/14/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1655995310%7D,%22end_time%22:%7B%22%24gt%22:1655995310%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1655995310%7D%7D%7D%7D%5D%7D',
            notification_policy:
              '/centreon/api/latest/configuration/hosts/14/notification-policy',
            performance_graph: null,
            status_graph: null,
            timeline: '/centreon/api/latest/monitoring/hosts/14/timeline',
          },
          externals: {
            action_url: null,
            notes: null,
          },
          uris: {
            configuration: '/centreon/main.php?p=60101&o=c&host_id=14',
            logs: '/centreon/main.php?p=20301&h=14',
            reporting: '/centreon/main.php?p=307&host=14',
          },
        },
        name: 'Centreon-Server',
        short_type: 'h',
        status: {
          code: 0,
          name: 'UP',
          severity_code: 5,
        },
        type: 'host',
        uuid: 'h14',
      },
      passive_checks: false,
      performance_data: '',
      severity_level: null,
      short_type: 's',
      status: {
        code: 3,
        name: 'UNKNOWN',
        severity_code: 3,
      },
      tries: '3/3 (H)',
      type: 'service',
      uuid: 'h14-s25',
    },
    {
      acknowledged: false,
      active_checks: true,
      alias: 'Monitoring Server',
      chart_url: null,
      duration: '3h 9m',
      flapping: false,
      fqdn: '127.0.0.1',
      icon: null,
      id: 14,
      in_downtime: false,
      information: 'OK - 127.0.0.1 rta 0.118ms lost 0%',
      last_check: '22s',
      last_status_change: '2022-06-23T13:32:38+02:00',
      links: {
        endpoints: {
          acknowledgement:
            '/centreon/api/latest/monitoring/hosts/14/acknowledgements?limit=1',
          details: '/centreon/api/latest/monitoring/resources/hosts/14',
          downtime:
            '/centreon/api/latest/monitoring/hosts/14/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1655995310%7D,%22end_time%22:%7B%22%24gt%22:1655995310%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1655995310%7D%7D%7D%7D%5D%7D',
          notification_policy:
            '/centreon/api/latest/configuration/hosts/14/notification-policy',
          performance_graph: null,
          status_graph: null,
          timeline: '/centreon/api/latest/monitoring/hosts/14/timeline',
        },
        externals: {
          action_url: '',
          notes: {
            label: '',
            url: '',
          },
        },
        uris: {
          configuration: '/centreon/main.php?p=60101&o=c&host_id=14',
          logs: '/centreon/main.php?p=20301&h=14',
          reporting: '/centreon/main.php?p=307&host=14',
        },
      },
      monitoring_server_name: 'Central',
      name: 'nooona :p',
      notification_enabled: false,
      parent: null,
      passive_checks: false,
      performance_data:
        'rta=0.118ms;3000.000;5000.000;0; pl=0%;80;100;0;100 rtmax=0.118ms;;;; rtmin=0.118ms;;;; ',
      severity: {
        icon: {
          id: 0,
          name: 'crabs',
          url: 'https://img.icons8.com/color-glass/48/000000/crab.png',
        },
        id: 0,
        level: 3,
        name: 'severity test',
        type: 'low',
      },
      severity_level: 3,
      short_type: 'h',
      status: {
        code: 0,
        name: 'UP',
        severity_code: 5,
      },
      tries: '1/5 (H)',
      type: 'host',
      uuid: 'h14',
    },
    {
      acknowledged: false,
      active_checks: true,
      alias: null,
      chart_url: null,
      duration: '3h 9m',
      flapping: false,
      fqdn: null,
      icon: null,
      id: 26,
      in_downtime: false,
      information: 'OK - 127.0.0.1 rta 0.091ms lost 0%',
      last_check: '1m 37s',
      last_status_change: '2022-06-23T13:32:38+02:00',
      links: {
        endpoints: {
          acknowledgement:
            '/centreon/api/latest/monitoring/hosts/14/services/26/acknowledgements?limit=1',
          details:
            '/centreon/api/latest/monitoring/resources/hosts/14/services/26',
          downtime:
            '/centreon/api/latest/monitoring/hosts/14/services/26/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1655995310%7D,%22end_time%22:%7B%22%24gt%22:1655995310%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1655995310%7D%7D%7D%7D%5D%7D',
          notification_policy:
            '/centreon/api/latest/configuration/hosts/14/services/26/notification-policy',
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
        fqdn: '127.0.0.1',
        icon: null,
        id: 14,
        links: {
          endpoints: {
            acknowledgement:
              '/centreon/api/latest/monitoring/hosts/14/acknowledgements?limit=1',
            details: '/centreon/api/latest/monitoring/resources/hosts/14',
            downtime:
              '/centreon/api/latest/monitoring/hosts/14/downtimes?search=%7B%22%24and%22:%5B%7B%22start_time%22:%7B%22%24lt%22:1655995310%7D,%22end_time%22:%7B%22%24gt%22:1655995310%7D,%220%22:%7B%22%24or%22:%7B%22is_cancelled%22:%7B%22%24neq%22:1%7D,%22deletion_time%22:%7B%22%24gt%22:1655995310%7D%7D%7D%7D%5D%7D',
            notification_policy:
              '/centreon/api/latest/configuration/hosts/14/notification-policy',
            performance_graph: null,
            status_graph: null,
            timeline: '/centreon/api/latest/monitoring/hosts/14/timeline',
          },
          externals: {
            action_url: null,
            notes: null,
          },
          uris: {
            configuration: '/centreon/main.php?p=60101&o=c&host_id=14',
            logs: '/centreon/main.php?p=20301&h=14',
            reporting: '/centreon/main.php?p=307&host=14',
          },
        },
        name: 'nooona :p',
        short_type: 'h',
        status: {
          code: 0,
          name: 'UP',
          severity_code: 5,
        },
        type: 'host',
        uuid: 'h14',
      },
      passive_checks: false,
      performance_data:
        'rta=0.091ms;200.000;400.000;0; pl=0%;20;50;0;100 rtmax=0.126ms;;;; rtmin=0.050ms;;;; ',
      severity: {
        icon: {
          id: 0,
          name: 'crabs',
          url: 'https://img.icons8.com/color-glass/48/000000/crab.png',
        },
        id: 0,
        level: 3,
        name: 'severity test',
        type: 'low',
      },
      severity_level: 2,
      short_type: 's',
      status: {
        code: 0,
        name: 'OK',
        severity_code: 5,
      },
      tries: '1/3 (H)',
      type: 'service',
      uuid: 'h14-s26',
    },
  ],
};
