import { equals, reject, path } from 'ramda';
import axios from 'axios';
import mockDate from 'mockdate';
import userEvent from '@testing-library/user-event';
import { Provider } from 'jotai';
import { BrowserRouter } from 'react-router-dom';

import {
  render,
  waitFor,
  fireEvent,
  RenderResult,
  act,
  setUrlQueryParameters,
  getUrlQueryParameters,
  screen,
  getSearchQueryParameterValue,
} from '@centreon/ui';
import { refreshIntervalAtom, userAtom } from '@centreon/ui-context';

import {
  labelMore,
  labelFrom,
  labelTo,
  labelAt,
  labelStatusInformation,
  labelDowntimeDuration,
  labelAcknowledgedBy,
  labelTimezone,
  labelCurrentStatusDuration,
  labelLastStatusChange,
  labelNextCheck,
  labelCheckDuration,
  labelLatency,
  labelStatusChangePercentage,
  labelLastNotification,
  labelLastCheck,
  labelCurrentNotificationNumber,
  labelPerformanceData,
  label7Days,
  label1Day,
  label31Days,
  labelCopy,
  labelCommand,
  labelComment,
  labelConfigure,
  labelDetails,
  labelViewLogs,
  labelViewReport,
  labelCopyLink,
  labelServices,
  labelFqdn,
  labelAlias,
  labelGroups,
  labelAcknowledgement,
  labelDowntime,
  labelDisplayEvents,
  labelForward,
  labelBackward,
  labelEndDateGreaterThanStartDate,
  labelMin,
  labelMax,
  labelAvg,
  labelCompactTimePeriod,
  labelCheck,
  labelMonitoringServer,
  labelToday,
  labelYesterday,
  labelThisWeek,
  labelLastWeek,
  labelLastMonth,
  labelLastYear,
  labelBeforeLastYear,
  labelLastCheckWithOkStatus,
  labelGraph,
  labelNotificationStatus,
  labelCategories,
  labelExportToCSV,
} from '../translatedLabels';
import Context, { ResourceContext } from '../testUtils/Context';
import useListing from '../Listing/useListing';
import { buildResourcesEndpoint } from '../Listing/api/endpoint';
import { cancelTokenRequestParam } from '../testUtils';
import { defaultGraphOptions } from '../Graph/Performance/ExportableGraphWithTimeline/graphOptionsAtoms';
import useFilter from '../testUtils/useFilter';
import { CriteriaNames } from '../Filter/Criterias/models';
import { ResourceType } from '../models';
import useLoadDetails from '../testUtils/useLoadDetails';

import { CustomTimePeriodProperty } from './tabs/Graph/models';
import { buildListTimelineEventsEndpoint } from './tabs/Timeline/api';
import useDetails from './useDetails';
import { getTypeIds } from './tabs/Timeline/Event';
import { DetailsUrlQueryParameters } from './models';

import Details from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('../icons/Downtime');

Object.defineProperty(navigator, 'clipboard', {
  value: {
    writeText: () => Promise.resolve(),
  },
});

jest.spyOn(navigator.clipboard, 'writeText');

jest.mock('@visx/visx', () => {
  return {
    ...(jest.requireActual('@visx/visx') as jest.Mocked<unknown>),
    Responsive: {
      ParentSize: ({ children }): JSX.Element => children({ width: 500 }),
    },
  };
});

const resourceServiceUuid = 'h1-s1';
const resourceServiceId = 1;
const resourceServiceType = ResourceType.service;
const resourceHostId = 1;
const resourceHostType = 'host';
const groups = [
  {
    configuration_uri: '/centreon/main.php?p=60102&o=c&hg_id=53',
    id: 0,
    name: 'Linux-servers',
  },
];

const categories = [
  {
    configuration_uri: '/centreon/main.php?p=60102&o=c&hg_id=53',
    id: 0,
    name: 'Windows',
  },
];

const serviceDetailsUrlParameters = {
  id: 1,
  resourcesDetailsEndpoint:
    'api/latest/monitoring/resources/hosts/1/services/1',
  tab: 'details',
  type: 'service',
  uuid: 'h1-s1',
};

const serviceDetailsGraphUrlParameters = {
  id: 1,
  parentId: 1,
  parentType: 'host',
  tab: 'graph',
  type: 'service',
  uuid: 'h1-s1',
};

const serviceDetailsTimelineUrlParameters = {
  id: 1,
  parentId: 1,
  parentType: 'host',
  tab: 'timeline',
  type: 'service',
  uuid: 'h1-s1',
};

const hostDetailsServicesUrlParameters = {
  id: 1,
  parentId: 3,
  parentType: 'service',
  tab: 'services',
  type: 'host',
  uuid: 'h1',
};

const metaserviceDetailsMetricsUrlParameters = {
  id: 1,
  tab: 'metrics',
  type: 'metaservice',
  uuid: 'ms1',
};

const serviceDetailsNotificationUrlParameters = {
  id: 1,
  parentId: 1,
  parentType: 'host',
  tab: 'notification',
  type: 'service',
  uuid: 'h1-s1',
};

const retrievedNotificationContacts = {
  contact_groups: [
    {
      alias: 'admin admin',
      configuration_uri: '/centreon/main.php?p=60301&o=c&cg_id=1',
      name: 'admin',
    },
  ],
  contacts: [
    {
      alias: 'Guest Guest',
      configuration_uri: '/centreon/main.php?p=60301&o=c&contact_id=1',
      email: 'localhost@centreon.com',
      name: 'Guest',
    },
  ],
  is_notification_enabled: true,
};

const retrievedDetails = {
  acknowledged: false,
  acknowledgement: {
    author_name: 'Admin',
    comment: 'Acknowledged by Admin',
    entry_time: '2020-03-18T18:57:59Z',
    is_persistent: true,
    is_sticky: true,
  },
  active_checks: false,
  alias: 'Central-Centreon',
  categories,
  checked: true,
  command_line: 'base_host_alive',
  downtimes: [
    {
      author_name: 'admin',
      comment: 'First downtime set by Admin',
      end_time: '2020-01-18T18:57:59Z',
      entry_time: '2020-01-18T17:57:59Z',
      start_time: '2020-01-18T17:57:59Z',
    },
    {
      author_name: 'admin',
      comment: 'Second downtime set by Admin',
      end_time: '2020-02-18T18:57:59Z',
      entry_time: '2020-01-18T17:57:59Z',
      start_time: '2020-02-18T17:57:59Z',
    },
  ],
  duration: '22m',
  execution_time: 0.070906,
  flapping: true,
  fqdn: 'central.centreon.com',
  groups,
  id: resourceServiceId,
  information:
    'OK - 127.0.0.1 rta 0.100ms lost 0%\n OK - 127.0.0.1 rta 0.99ms lost 0%\n OK - 127.0.0.1 rta 0.98ms lost 0%\n OK - 127.0.0.1 rta 0.97ms lost 0%',
  last_check: '2020-05-18T16:00Z',
  last_notification: '2020-07-18T17:30:00Z',
  last_status_change: '2020-04-18T15:00Z',
  last_time_with_no_issue: '2021-09-23T15:49:50+02:00',
  last_update: '2020-03-18T16:30:00Z',
  latency: 0.005,
  links: {
    endpoints: {
      details: '/centreon/api/latest/monitoring/resources/hosts/1/services/1',
      notification_policy: 'notification_policy',
      performance_graph: 'performance_graph',
      timeline: 'timeline',
      timeline_download: 'timeline/download',
    },
    externals: {
      action_url: undefined,
      notes: undefined,
    },
    uris: {
      configuration: undefined,
      logs: undefined,
      reporting: undefined,
    },
  },
  monitoring_server_name: 'Poller',
  name: 'Central',
  next_check: '2020-06-18T17:15:00Z',
  notification_number: 3,
  parent: {
    id: resourceHostId,
    links: {
      endpoints: {
        performance_graph: 'performance_graph',
        timeline: 'timeline',
      },
      externals: {
        action_url: undefined,
        notes: undefined,
      },
      uris: {
        configuration: undefined,
        logs: undefined,
        reporting: undefined,
      },
    },
    name: 'Centreon',
    short_type: 'h',
    status: { name: 'S1', severity_code: 1 },
    type: resourceHostType,
    uuid: 'h1',
  },
  passive_checks: false,
  percent_state_change: 3.5,
  performance_data:
    'rta=0.025ms;200.000;400.000;0; rtmax=0.061ms;;;; rtmin=0.015ms;;;; pl=0%;20;50;0;100',
  status: { name: 'Critical', severity_code: 1 },
  timezone: 'Europe/Paris',
  tries: '3/3 (Hard)',
  type: resourceServiceType,
  uuid: resourceServiceUuid,
};

const retrievedPerformanceGraphData = {
  global: {
    title: 'Ping graph',
  },
  metrics: [
    {
      average_value: 1234,
      data: [2, 0, 1],
      ds_data: {
        ds_color_area: 'transparent',
        ds_color_line: '#fff',
        ds_filled: false,
        ds_legend: 'Round-Trip-Time Average',
        ds_transparency: 80,
      },
      legend: 'Round-Trip-Time Average (ms)',
      maximum_value: 2456,
      metric: 'rta',
      minimum_value: null,
      unit: 'ms',
    },
  ],
  times: [
    '2020-06-19T07:30:00Z',
    '2020-06-20T06:55:00Z',
    '2020-06-23T06:55:00Z',
  ],
};

const retrievedTimeline = {
  meta: {
    limit: 10,
    page: 1,
    total: 5,
  },
  result: [
    {
      content: 'INITIAL HOST STATE: Centreon-Server;UP;HARD;1;',
      date: '2020-01-21T08:40:00Z',
      id: 1,
      status: {
        name: 'UP',
        severity_code: 5,
      },
      tries: 1,
      type: 'event',
    },
    {
      content: 'INITIAL HOST STATE: Centreon-Server;DOWN;HARD;3;',
      date: '2020-01-21T08:35:00Z',
      id: 2,
      status: {
        name: 'DOWN',
        severity_code: 1,
      },
      tries: 3,
      type: 'event',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little notification',
      date: '2020-01-20T07:40:00Z',
      id: 3,
      type: 'notification',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little ack',
      date: '2020-01-19T07:35:00Z',
      id: 4,
      type: 'acknowledgement',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little dt',
      date: '2020-01-19T07:30:00Z',
      end_date: '2020-01-21T07:33:00Z',
      id: 5,
      start_date: '2020-01-19T07:30:00Z',
      type: 'downtime',
    },
    {
      contact: {
        name: 'super_admin',
      },
      content: 'My little ongoing dt',
      date: '2020-01-19T06:57:00Z',
      end_date: null,
      id: 6,
      start_date: '2020-01-19T07:30:00Z',
      type: 'downtime',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little comment',
      date: '2020-01-19T06:55:00Z',
      end_date: '2020-01-21T07:33:00Z',
      id: 7,
      start_date: '2020-01-19T07:30:00Z',
      type: 'comment',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little comment two',
      date: '2020-01-18T06:55:00Z',
      end_date: null,
      id: 8,
      start_date: null,
      type: 'comment',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little comment three',
      date: '2020-01-01T06:55:00Z',
      end_date: null,
      id: 9,
      start_date: null,
      type: 'comment',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little comment four',
      date: '2019-06-10T06:55:00Z',
      end_date: null,
      id: 10,
      start_date: null,
      type: 'comment',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little comment five',
      date: '2018-10-10T06:55:00Z',
      end_date: null,
      id: 11,
      start_date: null,
      type: 'comment',
    },
  ],
};

const retrievedServices = {
  meta: {
    limit: 10,
    page: 1,
    total: 2,
  },
  result: [
    {
      duration: '22m',
      id: 3,
      information: 'OK - 127.0.0.1 rta 0ms lost 0%',
      links: {
        endpoints: {
          performance_graph: 'ping-performance',
        },
        externals: {
          action: 'action',
        },
        uris: {
          configuration: 'configuration',
        },
      },
      name: 'Ping',
      short_type: 's',
      status: {
        name: 'Ok',
        severity_code: 5,
      },
      type: 'service',
      uuid: 'h1-s3',
    },
    {
      duration: '21m',
      id: 4,
      information: 'No output',
      links: {
        externals: {
          action: 'action',
        },
        uris: {
          configuration: 'configuration',
        },
      },
      name: 'Disk',
      short_type: 's',
      status: {
        name: 'Unknown',
        severity_code: 6,
      },
      type: 'service',
      uuid: 'h1-s4',
    },
  ],
};

const retrievedFilters = {
  data: {
    meta: {
      limit: 30,
      page: 1,
      total: 0,
    },
    result: [],
  },
};

const currentDateIsoString = '2020-01-21T06:00:00.000Z';
const start = '2020-01-20T06:00:00.000Z';

const mockedParametersDataTimeLineDownload = {
  conditions: [
    {
      field: 'date',
      values: {
        $gt: start,
        $lt: currentDateIsoString,
      },
    },
  ],
  lists: [
    {
      field: 'type',
      values: [
        'event',
        'notification',
        'comment',
        'acknowledgement',
        'downtime',
      ],
    },
  ],
};

let context: ResourceContext;

const DetailsTest = (): JSX.Element => {
  const listingState = useListing();
  const detailState = useLoadDetails();
  const filterState = useFilter();

  useDetails();

  context = {
    ...listingState,
    ...detailState,
    ...filterState,
  } as ResourceContext;

  return (
    <BrowserRouter>
      <Context.Provider value={context}>
        <Details />
      </Context.Provider>
    </BrowserRouter>
  );
};

const mockUser = {
  isExportButtonEnabled: true,
  locale: 'en',
  timezone: 'Europe/Paris',
};
const mockRefreshInterval = 60;

const DetailsWithJotai = (): JSX.Element => (
  <Provider
    initialValues={[
      [userAtom, mockUser],
      [refreshIntervalAtom, mockRefreshInterval],
    ]}
  >
    <DetailsTest />
  </Provider>
);

const renderDetails = (): RenderResult => render(<DetailsWithJotai />);

const mockedLocalStorageGetItem = jest.fn();
const mockedLocalStorageSetItem = jest.fn();
const mockedNavigate = jest.fn();

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useNavigate: (): jest.Mock => mockedNavigate,
}));

Storage.prototype.getItem = mockedLocalStorageGetItem;
Storage.prototype.setItem = mockedLocalStorageSetItem;

describe(Details, () => {
  beforeEach(() => {
    mockDate.set(currentDateIsoString);
    mockedAxios.get.mockResolvedValueOnce(retrievedFilters);
  });

  afterEach(() => {
    mockDate.reset();
    mockedAxios.get.mockReset();
    mockedLocalStorageSetItem.mockReset();
    mockedLocalStorageGetItem.mockReset();
  });

  it('displays resource details information', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsUrlParameters,
      },
    ]);

    const { getByText, queryByText, getAllByText, findByText } =
      renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        './api/latest/monitoring/resources/hosts/1/services/1' as string,
        expect.anything(),
      );
    });

    await waitFor(() => {
      expect(getByText('Critical')).toBeInTheDocument();
    });

    expect(getByText('Centreon')).toBeInTheDocument();

    const fqdnText = await findByText(labelFqdn);

    expect(fqdnText).toBeInTheDocument();
    expect(getByText('central.centreon.com')).toBeInTheDocument();
    expect(getByText(labelAlias)).toBeInTheDocument();
    expect(getByText('Central-Centreon')).toBeInTheDocument();
    expect(getByText(labelStatusInformation)).toBeInTheDocument();
    expect(getByText('OK - 127.0.0.1 rta 0.100ms lost 0%')).toBeInTheDocument();
    expect(getByText('OK - 127.0.0.1 rta 0.99ms lost 0%')).toBeInTheDocument();
    expect(getByText('OK - 127.0.0.1 rta 0.98ms lost 0%')).toBeInTheDocument();
    expect(
      queryByText('OK - 127.0.0.1 rta 0.97ms lost 0%'),
    ).not.toBeInTheDocument();

    fireEvent.click(getByText(labelMore));

    expect(getByText('OK - 127.0.0.1 rta 0.97ms lost 0%')).toBeInTheDocument();

    expect(getAllByText(labelComment)).toHaveLength(3);
    expect(getAllByText(labelDowntimeDuration)).toHaveLength(2);
    expect(getByText(`${labelFrom} 01/18/2020 6:57 PM`)).toBeInTheDocument();
    expect(getByText(`${labelTo} 01/18/2020 7:57 PM`)).toBeInTheDocument();
    expect(getByText(`${labelFrom} 02/18/2020 6:57 PM`)).toBeInTheDocument();
    expect(getByText(`${labelTo} 02/18/2020 7:57 PM`)).toBeInTheDocument();
    expect(getByText('First downtime set by Admin'));
    expect(getByText('Second downtime set by Admin'));

    expect(getByText(labelAcknowledgedBy)).toBeInTheDocument();
    expect(
      getByText(`Admin ${labelAt} 03/18/2020 7:57 PM`),
    ).toBeInTheDocument();
    expect(getByText('Acknowledged by Admin'));

    expect(getByText(labelTimezone)).toBeInTheDocument();
    expect(getByText('Europe/Paris')).toBeInTheDocument();

    expect(getByText(labelCurrentStatusDuration)).toBeInTheDocument();
    expect(getByText('22m - 3/3 (Hard)')).toBeInTheDocument();

    expect(getByText(labelLastStatusChange)).toBeInTheDocument();
    expect(getByText('04/18/2020 5:00 PM')).toBeInTheDocument();

    expect(getByText(labelLastCheck)).toBeInTheDocument();
    expect(getByText('05/18/2020 6:00 PM')).toBeInTheDocument();

    expect(getByText(labelNextCheck)).toBeInTheDocument();
    expect(getByText('06/18/2020 7:15 PM')).toBeInTheDocument();

    expect(getByText(labelCheckDuration)).toBeInTheDocument();
    expect(getByText('0.070906 s')).toBeInTheDocument();

    expect(getByText(labelLastCheckWithOkStatus)).toBeInTheDocument();
    expect(getByText('06/18/2020 7:15 PM')).toBeInTheDocument();

    expect(getByText(labelLatency)).toBeInTheDocument();
    expect(getByText('0.005 s')).toBeInTheDocument();

    expect(getByText(labelCheck)).toBeInTheDocument();

    expect(getByText(labelStatusChangePercentage)).toBeInTheDocument();
    expect(getByText('3.5%')).toBeInTheDocument();

    expect(getByText(labelLastNotification)).toBeInTheDocument();
    expect(getByText('07/18/2020 7:30 PM')).toBeInTheDocument();

    expect(getByText(labelCurrentNotificationNumber)).toBeInTheDocument();
    expect(getByText('3')).toBeInTheDocument();

    expect(getByText(labelGroups)).toBeInTheDocument();
    expect(getByText('Linux-servers')).toBeInTheDocument();
    expect(getByText(labelCategories)).toBeInTheDocument();
    expect(getByText('Windows')).toBeInTheDocument();

    expect(getByText(labelPerformanceData)).toBeInTheDocument();
    expect(
      getByText(
        'rta=0.025ms;200.000;400.000;0; rtmax=0.061ms;;;; rtmin=0.015ms;;;; pl=0%;20;50;0;100',
      ),
    ).toBeInTheDocument();

    expect(getByText(labelCommand)).toBeInTheDocument();
    expect(getByText('base_host_alive')).toBeInTheDocument();
  });

  it.each([
    [label1Day, '2020-01-20T06:00:00.000Z', 20],
    [label7Days, '2020-01-14T06:00:00.000Z', 100],
    [label31Days, '2019-12-21T06:00:00.000Z', 500],
  ])(
    `queries performance graphs and timelines with %p period when the Graph tab is selected and "Display events" option is activated`,
    async (period, startIsoString, timelineEventsLimit) => {
      mockedAxios.get
        .mockResolvedValueOnce({ data: retrievedDetails })
        .mockResolvedValueOnce({ data: retrievedPerformanceGraphData });

      mockedAxios.get
        .mockResolvedValueOnce({ data: retrievedTimeline })
        .mockResolvedValueOnce({ data: retrievedTimeline });

      setUrlQueryParameters([
        {
          name: 'details',
          value: serviceDetailsGraphUrlParameters,
        },
      ]);

      const { getByText, findByText } = renderDetails();

      await waitFor(() => {
        expect(getByText(period) as HTMLElement).toBeEnabled();
      });

      userEvent.click(getByText(period) as HTMLElement);

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          `${retrievedDetails.links.endpoints.performance_graph}?start=${startIsoString}&end=${currentDateIsoString}`,
          expect.anything(),
        );
      });

      await findByText(labelDisplayEvents);
      userEvent.click(getByText(labelDisplayEvents));

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          buildListTimelineEventsEndpoint({
            endpoint: retrievedDetails.links.endpoints.timeline,
            parameters: {
              limit: timelineEventsLimit,
              search: {
                conditions: [
                  {
                    field: 'date',
                    values: {
                      $gt: startIsoString,
                      $lt: currentDateIsoString,
                    },
                  },
                ],
              },
            },
          }),
          expect.anything(),
        );
      });
    },
  );

  it('displays event annotations when the corresponding switch is triggered and the Graph tab is clicked', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
      .mockResolvedValueOnce({
        data: retrievedTimeline,
      });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsGraphUrlParameters,
      },
    ]);

    const { findAllByLabelText, queryByLabelText, getByText, findByText } =
      renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    expect(queryByLabelText(labelComment)).toBeNull();
    expect(queryByLabelText(labelAcknowledgement)).toBeNull();
    expect(queryByLabelText(labelDowntime)).toBeNull();

    await findByText(labelDisplayEvents);

    userEvent.click(getByText(labelDisplayEvents));

    const commentAnnotations = await findAllByLabelText(labelComment);
    const acknowledgementAnnotations = await findAllByLabelText(
      labelAcknowledgement,
    );
    const downtimeAnnotations = await findAllByLabelText(labelDowntime);

    expect(commentAnnotations).toHaveLength(5);
    expect(acknowledgementAnnotations).toHaveLength(1);
    expect(downtimeAnnotations).toHaveLength(2);
  });

  it('copies the command line to clipboard when the copy button is clicked', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsUrlParameters,
      },
    ]);

    const { getByLabelText } = renderDetails();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    await waitFor(() => expect(getByLabelText(labelCopy)).toBeInTheDocument());

    fireEvent.click(getByLabelText(labelCopy));

    await waitFor(() =>
      expect(navigator.clipboard.writeText).toHaveBeenCalledWith(
        retrievedDetails.command_line,
      ),
    );
  });

  it('displays retrieved timeline events and filtered by selected event types, when the Timeline tab is selected', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedTimeline });
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedTimeline });

    setUrlQueryParameters([
      { name: 'details', value: serviceDetailsTimelineUrlParameters },
    ]);

    const { getByText, getAllByLabelText, baseElement } = renderDetails();

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        buildListTimelineEventsEndpoint({
          endpoint: retrievedDetails.links.endpoints.timeline,
          parameters: {
            limit: 30,
            page: 1,
            search: {
              conditions: [
                {
                  field: 'date',
                  values: {
                    $gt: '2020-01-20T06:00:00.000Z',
                    $lt: '2020-01-21T06:00:00.000Z',
                  },
                },
              ],
              lists: [
                {
                  field: 'type',
                  values: getTypeIds(),
                },
              ],
            },
          },
        }),
        expect.anything(),
      ),
    );

    await waitFor(() => expect(getByText(labelToday)).toBeInTheDocument());

    expect(getByText('Tuesday, January 21, 2020 9:40 AM')).toBeInTheDocument();
    expect(getAllByLabelText('Event')).toHaveLength(3); // 2 events + 1 selected option
    expect(getByText('UP')).toBeInTheDocument();
    expect(getByText('Tries: 1')).toBeInTheDocument();
    expect(
      getByText('INITIAL HOST STATE: Centreon-Server;UP;HARD;1;'),
    ).toBeInTheDocument();

    expect(getByText('Tuesday, January 21, 2020 9:35 AM')).toBeInTheDocument();
    expect(getByText('DOWN')).toBeInTheDocument();
    expect(getByText('Tries: 3')).toBeInTheDocument();
    expect(
      getByText('INITIAL HOST STATE: Centreon-Server;DOWN;HARD;3;'),
    ).toBeInTheDocument();

    expect(getByText(labelYesterday)).toBeInTheDocument();

    expect(getByText('Monday, January 20, 2020 8:40 AM')).toBeInTheDocument();
    expect(getByText('My little notification'));

    expect(getByText(labelThisWeek)).toBeInTheDocument();
    expect(getByText('January 19, 2020')).toBeInTheDocument();

    expect(getByText('Sunday, January 19, 2020 8:35 AM')).toBeInTheDocument();
    expect(getByText('My little ack'));

    expect(
      getByText(
        'From Sunday, January 19, 2020 8:30 AM To Tuesday, January 21, 2020 8:33 AM',
      ),
    ).toBeInTheDocument();
    expect(getByText('My little dt'));

    expect(
      getByText('From Sunday, January 19, 2020 8:30 AM'),
    ).toBeInTheDocument();
    expect(getByText('My little ongoing dt'));

    expect(getByText('Sunday, January 19, 2020 7:55 AM')).toBeInTheDocument();
    expect(getByText('My little comment'));

    expect(getByText(labelLastWeek)).toBeInTheDocument();
    expect(
      getByText('From January 12, 2020 to January 18, 2020'),
    ).toBeInTheDocument();

    expect(getByText('Saturday, January 18, 2020 7:55 AM')).toBeInTheDocument();
    expect(getByText('My little comment two'));

    expect(getByText(labelLastMonth)).toBeInTheDocument();
    expect(
      getByText('From December 15, 2019 to January 11, 2020'),
    ).toBeInTheDocument();

    expect(getByText('Wednesday, January 1, 2020 7:55 AM')).toBeInTheDocument();
    expect(getByText('My little comment three'));

    expect(getByText(labelLastYear)).toBeInTheDocument();
    expect(
      getByText('From December 16, 2018 to December 14, 2019'),
    ).toBeInTheDocument();

    expect(getByText('Monday, June 10, 2019 8:55 AM')).toBeInTheDocument();
    expect(getByText('My little comment four'));

    expect(getByText(labelBeforeLastYear)).toBeInTheDocument();
    expect(getByText('From December 15, 2018')).toBeInTheDocument();

    expect(
      getByText('Wednesday, October 10, 2018 8:55 AM'),
    ).toBeInTheDocument();
    expect(getByText('My little comment five'));

    const removeEventIcon = baseElement.querySelectorAll(
      'svg[class*="deleteIcon"]',
    )[0];

    fireEvent.click(removeEventIcon);

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        buildListTimelineEventsEndpoint({
          endpoint: retrievedDetails.links.endpoints.timeline,
          parameters: {
            limit: 30,
            page: 1,
            search: {
              conditions: [
                {
                  field: 'date',
                  values: {
                    $gt: '2020-01-20T06:00:00.000Z',
                    $lt: '2020-01-21T06:00:00.000Z',
                  },
                },
              ],
              lists: [
                {
                  field: 'type',
                  values: reject(equals('event'))(getTypeIds()),
                },
              ],
            },
          },
        }),
        expect.anything(),
      ),
    );
  });

  it('navigates to logs and report pages when the corresponding icons are clicked', async () => {
    mockedAxios.get.mockResolvedValueOnce({
      data: {
        ...retrievedDetails,
        links: {
          ...retrievedDetails.links,
          uris: {
            logs: 'logs',
            reporting: 'reporting',
          },
        },
      },
    });

    setUrlQueryParameters([
      { name: 'details', value: serviceDetailsUrlParameters },
    ]);

    const { getByLabelText, getByTestId } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    await waitFor(() =>
      expect(getByLabelText(labelViewLogs)).toBeInTheDocument(),
    );

    expect(getByLabelText(labelViewReport)).toBeInTheDocument();

    userEvent.click(getByTestId(labelViewLogs));

    expect(mockedNavigate).toHaveBeenCalledWith('/logs');

    userEvent.click(getByTestId(labelViewReport));

    expect(mockedNavigate).toHaveBeenCalledWith('/reporting');
  });

  it('sets the details according to the details URL query parameter when given', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: retrievedDetails,
      })
      .mockResolvedValue({
        data: retrievedPerformanceGraphData,
      });

    const retrievedServiceDetails = {
      id: 2,
      resourcesDetailsEndpoint:
        'api/latest/monitoring/resources/hosts/1/services/2',
      tab: 'details',
      tabParameters: {
        graph: {
          options: defaultGraphOptions,
        },
        services: {
          options: defaultGraphOptions,
        },
      },
      type: 'service',
      uuid: 'h3-s2',
    };

    setUrlQueryParameters([
      {
        name: 'details',
        value: retrievedServiceDetails,
      },
    ]);

    const { getByText } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        './api/latest/monitoring/resources/hosts/1/services/2' as string,
        expect.anything(),
      );
    });

    await waitFor(() => expect(getByText(labelDetails)).toBeInTheDocument());

    fireEvent.click(getByText(labelDetails));

    const tabFromUrlQueryParameters = path(
      ['details', 'tab'],
      getUrlQueryParameters(),
    );

    await waitFor(() => {
      expect(tabFromUrlQueryParameters).toEqual('details');
    });

    userEvent.click(getByText(labelGraph));

    userEvent.click(getByText(label7Days));

    const updatedDetailsFromQueryParameters = getUrlQueryParameters()
      .details as DetailsUrlQueryParameters;

    await waitFor(() => {
      expect(updatedDetailsFromQueryParameters).toEqual({
        customTimePeriod: {
          end: '2020-01-21T06:00:00.000Z',
          start: '2020-01-14T06:00:00.000Z',
        },
        id: 2,
        resourcesDetailsEndpoint:
          'api/latest/monitoring/resources/hosts/1/services/2',
        selectedTimePeriodId: 'last_7_days',
        tab: 'graph',
        tabParameters: {
          graph: {
            options: {
              displayEvents: {
                id: 'displayEvents',
                label: labelDisplayEvents,
                value: false,
              },
            },
          },
          services: {
            options: {
              displayEvents: {
                id: 'displayEvents',
                label: labelDisplayEvents,
                value: false,
              },
            },
          },
        },
        uuid: 'h3-s2',
      });
    });
  });

  it('copies the current URL when the copy resource link button is clicked', async () => {
    mockedAxios.get.mockResolvedValueOnce({
      data: retrievedDetails,
    });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsUrlParameters,
      },
    ]);

    const { getByLabelText } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    await waitFor(() =>
      expect(getByLabelText(labelCopyLink)).toBeInTheDocument(),
    );

    act(() => {
      fireEvent.click(
        getByLabelText(labelCopyLink).firstElementChild as HTMLElement,
      );
    });

    await waitFor(() => {
      expect(navigator.clipboard.writeText).toHaveBeenCalledWith(
        window.location.href,
      );
    });
  });

  it('displays the linked services when the services tab of a host is clicked', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          ...retrievedDetails,
          type: 'host',
        },
      })
      .mockResolvedValueOnce({
        data: retrievedServices,
      })
      .mockResolvedValueOnce({
        data: { ...retrievedDetails, type: 'service' },
      });

    setUrlQueryParameters([
      {
        name: 'details',
        value: hostDetailsServicesUrlParameters,
      },
    ]);
    const { getByText, queryByText } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(3);
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      buildResourcesEndpoint({
        hostCategories: [],
        hostGroups: [],
        hostSeverities: [],
        hostSeverityLevels: [],
        limit: 30,
        monitoringServers: [],
        page: 1,
        resourceTypes: ['service'],
        search: {
          conditions: [
            {
              field: 'h.name',
              values: {
                $eq: retrievedDetails.name,
              },
            },
          ],
        },
        serviceCategories: [],
        serviceGroups: [],
        serviceSeverities: [],
        serviceSeverityLevels: [],
        states: [],
        statusTypes: [],
        statuses: [],
      }),
      expect.anything(),
    );

    await waitFor(() => expect(getByText('Ok')).toBeInTheDocument());
    expect(getByText('Ping')).toBeInTheDocument();
    expect(getByText('OK - 127.0.0.1 rta 0ms lost 0%'));
    expect(getByText('22m')).toBeInTheDocument();

    expect(getByText('Disk')).toBeInTheDocument();
    expect(getByText('Unknown')).toBeInTheDocument();
    expect(getByText('No output'));
    expect(getByText('21m')).toBeInTheDocument();

    fireEvent.click(getByText('Ping'));

    const [pingService] = retrievedServices.result;

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `./api/latest/monitoring/resources/services/${pingService.id}`,
        expect.anything(),
      );
    });

    await waitFor(() => {
      expect(queryByText(labelServices)).toBeNull();
    });
  });

  it('displays the linked service graphs when the Graph tab of a host is clicked', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          ...retrievedDetails,
          type: 'host',
        },
      })
      .mockResolvedValueOnce({
        data: retrievedServices,
      })
      .mockResolvedValueOnce({
        data: retrievedPerformanceGraphData,
      })
      .mockResolvedValueOnce({
        data: retrievedPerformanceGraphData,
      });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsGraphUrlParameters,
      },
    ]);

    const { findByText, getByText } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    await findByText(retrievedPerformanceGraphData.global.title);

    userEvent.click(getByText(label7Days) as HTMLElement);

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        'ping-performance?start=2020-01-14T06:00:00.000Z&end=2020-01-21T06:00:00.000Z',
        cancelTokenRequestParam,
      );
    });
  });

  it('queries performance graphs with a custom timeperiod when the Graph tab is selected and a custom time period is selected', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValue({ data: retrievedPerformanceGraphData });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsGraphUrlParameters,
      },
    ]);
    renderDetails();

    const startISOString = '2020-01-19T06:00:00.000Z';
    const endISOString = '2020-01-21T06:00:00.000Z';

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-20T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
        cancelTokenRequestParam,
      );
    });

    act(() => {
      context.changeCustomTimePeriod?.({
        date: new Date(startISOString),
        property: CustomTimePeriodProperty.start,
      });
    });
    act(() => {
      context.changeCustomTimePeriod?.({
        date: new Date(endISOString),
        property: CustomTimePeriodProperty.end,
      });
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=${startISOString}&end=${endISOString}`,
        cancelTokenRequestParam,
      );
    });
  });

  it('displays the correct date time on pickers when the Graph tab is selected and a time period is selected', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsGraphUrlParameters,
      },
    ]);

    const { getByText } = renderDetails();

    const startISOString = '2020-01-20T06:00:00.000Z';
    const endISOString = '2020-01-21T06:00:00.000Z';

    act(() => {
      context.changeCustomTimePeriod?.({
        date: new Date(startISOString),
        property: CustomTimePeriodProperty.start,
      });
    });
    act(() => {
      context.changeCustomTimePeriod?.({
        date: new Date(endISOString),
        property: CustomTimePeriodProperty.end,
      });
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-20T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
        cancelTokenRequestParam,
      );
    });

    expect(getByText('01/20/2020 7:00 AM')).toBeInTheDocument();
    expect(getByText('01/21/2020 7:00 AM')).toBeInTheDocument();

    userEvent.click(getByText(label7Days) as HTMLElement);

    expect(getByText('01/14/2020 7:00 AM')).toBeInTheDocument();
    expect(getByText('01/21/2020 7:00 AM')).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-14T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
        cancelTokenRequestParam,
      );
    });
  });

  it('displays an error message when Graph tab is selected and the start date of the time period is the same as the end date', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData });

    setUrlQueryParameters([
      {
        name: 'details',
        value: {
          ...serviceDetailsGraphUrlParameters,
          customTimePeriod: {
            end: '2021-11-02T21:00:00.000Z',
            start: '2021-11-02T21:00:00.000Z',
            timelineLimit: 20,
            xAxisTickFormat: 'LT',
          },
        },
      },
    ]);

    const { getByLabelText, getByText } = renderDetails();

    userEvent.click(getByLabelText(labelCompactTimePeriod));

    await waitFor(() => {
      expect(getByText(labelEndDateGreaterThanStartDate)).toBeInTheDocument();
    });
  });

  it.each([
    [labelForward, '2020-01-20T18:00:00.000Z', '2020-01-21T18:00:00.000Z'],
    [labelBackward, '2020-01-19T18:00:00.000Z', '2020-01-20T18:00:00.000Z'],
  ])(
    `queries performance graphs with a custom timeperiod when the Graph tab is selected and the "%p" icon is clicked`,
    async (iconLabel, startISOString, endISOString) => {
      mockedAxios.get
        .mockResolvedValueOnce({ data: retrievedDetails })
        .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
        .mockResolvedValueOnce({ data: retrievedPerformanceGraphData });

      setUrlQueryParameters([
        {
          name: 'details',
          value: serviceDetailsGraphUrlParameters,
        },
      ]);

      const { getByLabelText } = renderDetails();

      act(() => {
        context.changeCustomTimePeriod?.({
          date: new Date('2020-01-20T06:00:00.000Z'),
          property: CustomTimePeriodProperty.start,
        });
      });

      act(() => {
        context.changeCustomTimePeriod?.({
          date: new Date('2020-01-21T06:00:00.000Z'),
          property: CustomTimePeriodProperty.end,
        });
      });

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-20T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
          cancelTokenRequestParam,
        );
      });

      await waitFor(() =>
        expect(getByLabelText(iconLabel)).toBeInTheDocument(),
      );

      userEvent.click(getByLabelText(iconLabel));

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          `${retrievedDetails.links.endpoints.performance_graph}?start=${startISOString}&end=${endISOString}`,
          cancelTokenRequestParam,
        );
      });
    },
  );

  it('displays retrieved metrics when the selected Resource is a meta service and the metrics tab is selected', async () => {
    const service = retrievedServices.result[0];

    const retrievedMetrics = {
      meta: {
        limit: 10,
        page: 1,
        total: 1,
      },
      result: [
        {
          id: 0,
          name: 'pl',
          resource: service,
          unit: '%',
          value: 3,
        },
      ],
    };

    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          ...retrievedDetails,
          type: 'metaservice',
        },
      })
      .mockResolvedValueOnce({
        data: retrievedMetrics,
      });

    setUrlQueryParameters([
      {
        name: 'details',
        value: metaserviceDetailsMetricsUrlParameters,
      },
    ]);

    const { getByText } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    await waitFor(() => expect(getByText('pl')).toBeInTheDocument());
    expect(getByText('3 (%)')).toBeInTheDocument();
    expect(getByText(service.name)).toBeInTheDocument();
  });

  it('displays Min, Max and Average values in the legend when the Graph tab is selected', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
      .mockResolvedValueOnce({ data: retrievedTimeline });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsGraphUrlParameters,
      },
    ]);

    const { getByLabelText, getByText } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-20T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => expect(getByLabelText(labelMin)).toBeInTheDocument());
    expect(getByText('N/A')).toBeInTheDocument();
    expect(getByLabelText(labelMax)).toBeInTheDocument();
    expect(getByText('2.46k')).toBeInTheDocument();
    expect(getByLabelText(labelAvg)).toBeInTheDocument();
    expect(getByText('1.23k')).toBeInTheDocument();
  });

  it('filters on a group when the corresponding chip is clicked and the Details tab is selected', async () => {
    mockedAxios.get.mockResolvedValueOnce({
      data: retrievedDetails,
    });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsUrlParameters,
      },
    ]);

    const { getByLabelText } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    await waitFor(() =>
      expect(getByLabelText('Linux-servers Chip')).toBeInTheDocument(),
    );

    userEvent.hover(getByLabelText('Linux-servers Chip'));
    userEvent.click(getByLabelText('Linux-servers Filter'));

    await waitFor(() => {
      expect(context.getCriteriaValue?.(CriteriaNames.serviceGroups)).toEqual([
        { id: 0, name: 'Linux-servers' },
      ]);
    });
  });

  it('displays the resource configuration link', async () => {
    mockedAxios.get.mockResolvedValueOnce({
      data: {
        ...retrievedDetails,
        links: {
          ...retrievedDetails.links,
          uris: {
            configuration: '/configuration',
            logs: '/logs',
            reporting: '/reporting',
          },
        },
      },
    });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsUrlParameters,
      },
    ]);

    const { getByText, getByLabelText } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        './api/latest/monitoring/resources/hosts/1/services/1',
        expect.anything(),
      );
    });

    await waitFor(() =>
      expect(getByText(retrievedDetails.name)).toBeInTheDocument(),
    );

    userEvent.hover(getByText(retrievedDetails.name));

    expect(
      getByLabelText(`${labelConfigure}_${retrievedDetails.name}`),
    ).toBeInTheDocument();

    expect(
      getByLabelText(`${labelConfigure}_${retrievedDetails.name}`),
    ).toHaveAttribute('href', '/configuration');
  });

  it('populates details tiles with values from localStorage if available', async () => {
    mockedAxios.get.mockResolvedValueOnce({
      data: retrievedDetails,
    });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsUrlParameters,
      },
    ]);

    const { getByText, queryByText } = renderDetails();

    mockedLocalStorageGetItem.mockReturnValue(
      JSON.stringify([labelMonitoringServer, labelStatusInformation]),
    );

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        './api/latest/monitoring/resources/hosts/1/services/1',
        expect.anything(),
      );
    });

    await waitFor(() =>
      expect(getByText(labelMonitoringServer)).toBeInTheDocument(),
    );
    expect(getByText(labelStatusInformation)).toBeInTheDocument();

    expect(queryByText(labelLastCheck)).toBeInTheDocument();
    expect(queryByText(labelCommand)).toBeInTheDocument();
  });

  it('queries the performance graphs with the time period selected in the "Timeline" tab when the "Graph" tab is selected and the "Timeline" tab was selected', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValueOnce({ data: retrievedTimeline })
      .mockResolvedValueOnce({ data: retrievedTimeline })
      .mockResolvedValueOnce(retrievedFilters)
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsTimelineUrlParameters,
      },
    ]);

    const { getByText } = renderDetails();

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        buildListTimelineEventsEndpoint({
          endpoint: retrievedDetails.links.endpoints.timeline,
          parameters: {
            limit: 30,
            page: 1,
            search: {
              conditions: [
                {
                  field: 'date',
                  values: {
                    $gt: '2020-01-20T06:00:00.000Z',
                    $lt: '2020-01-21T06:00:00.000Z',
                  },
                },
              ],
              lists: [
                {
                  field: 'type',
                  values: getTypeIds(),
                },
              ],
            },
          },
        }),
        cancelTokenRequestParam,
      ),
    );

    await waitFor(() =>
      expect(
        screen.getByText('INITIAL HOST STATE: Centreon-Server;UP;HARD;1;'),
      ).toBeInTheDocument(),
    );

    userEvent.click(getByText(label7Days) as HTMLElement);

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        buildListTimelineEventsEndpoint({
          endpoint: retrievedDetails.links.endpoints.timeline,
          parameters: {
            limit: 30,
            page: 1,
            search: {
              conditions: [
                {
                  field: 'date',
                  values: {
                    $gt: '2020-01-14T06:00:00.000Z',
                    $lt: '2020-01-21T06:00:00.000Z',
                  },
                },
              ],
              lists: [
                {
                  field: 'type',
                  values: getTypeIds(),
                },
              ],
            },
          },
        }),
        expect.anything(),
      ),
    );

    userEvent.click(getByText(labelGraph));

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-14T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
        cancelTokenRequestParam,
      );
    });
  });

  it('displays contacts and contact groups when the notification tab is clicked', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });
    mockedAxios.get.mockResolvedValueOnce({
      data: retrievedNotificationContacts,
    });

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsNotificationUrlParameters,
      },
    ]);

    const { getByText, getByTestId } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        retrievedDetails.links.endpoints.notification_policy,
        expect.anything(),
      );
    });

    await waitFor(() => {
      expect(getByText(labelNotificationStatus)).toBeInTheDocument();
    });

    expect(getByTestId('NotificationsActiveIcon')).toBeInTheDocument();

    retrievedNotificationContacts.contact_groups.forEach(({ name, alias }) => {
      expect(getByText(name)).toBeInTheDocument();
      expect(getByText(alias)).toBeInTheDocument();
    });

    retrievedNotificationContacts.contacts.forEach(({ name, alias, email }) => {
      expect(getByText(name)).toBeInTheDocument();
      expect(getByText(alias)).toBeInTheDocument();
      expect(getByText(email)).toBeInTheDocument();
    });
  });

  it('calls the download timeline endpoint when the Timeline tab is selected and the "Export to CSV button" is clicked', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedTimeline });

    const timelineDownloadEndpoint = path(
      ['links', 'endpoints', 'timeline_download'],
      retrievedDetails,
    );

    const parameters = getSearchQueryParameterValue(
      mockedParametersDataTimeLineDownload,
    );

    const mockedOpen = jest.fn();
    window.open = mockedOpen;

    setUrlQueryParameters([
      {
        name: 'details',
        value: serviceDetailsTimelineUrlParameters,
      },
    ]);

    const { getByTestId } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(3);
    });

    fireEvent.click(getByTestId(labelExportToCSV));

    expect(mockedOpen).toHaveBeenCalledWith(
      `${timelineDownloadEndpoint}?search=${JSON.stringify(parameters)}`,
      'noopener',
      'noreferrer',
    );
  });
});
