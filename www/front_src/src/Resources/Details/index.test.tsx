import * as React from 'react';

import { equals, reject, path, isNil } from 'ramda';
import axios from 'axios';
import mockDate from 'mockdate';
import {
  render,
  waitFor,
  fireEvent,
  RenderResult,
  act,
} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import {
  ThemeProvider,
  setUrlQueryParameters,
  getUrlQueryParameters,
  copyToClipboard,
} from '@centreon/ui';

import {
  labelMore,
  labelFrom,
  labelTo,
  labelAt,
  labelStatusInformation,
  labelDowntimeDuration,
  labelAcknowledgedBy,
  labelTimezone,
  labelCurrentStateDuration,
  labelLastStateChange,
  labelNextCheck,
  labelCheckDuration,
  labelLatency,
  labelPercentStateChange,
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
  labelViewLogs,
  labelViewReport,
  labelDetails,
  labelCopyLink,
  labelServices,
  labelFqdn,
  labelAlias,
  labelGroups,
  labelAcknowledgement,
  labelSwitchToGraph,
  labelDowntime,
  labelDisplayEvents,
  labelForward,
  labelBackward,
  labelEndDateGreaterThanStartDate,
  labelGraphOptions,
  labelMin,
  labelMax,
  labelAvg,
  labelCompactTimePeriod,
  labelCheck,
  labelShortcuts,
  labelMonitoringServer,
  labelToday,
  labelYesterday,
  labelThisWeek,
  labelLastWeek,
  labelLastMonth,
  labelLastYear,
  labelBeforeLastYear,
} from '../translatedLabels';
import Context, { ResourceContext } from '../Context';
import useListing from '../Listing/useListing';
import { resourcesEndpoint } from '../api/endpoint';
import { buildResourcesEndpoint } from '../Listing/api/endpoint';
import { cancelTokenRequestParam } from '../testUtils';
import { defaultGraphOptions } from '../Graph/Performance/ExportableGraphWithTimeline/useGraphOptions';
import useFilter from '../Filter/useFilter';
import { CriteriaNames } from '../Filter/Criterias/models';

import {
  last7Days,
  last31Days,
  lastDayPeriod,
  CustomTimePeriodProperty,
} from './tabs/Graph/models';
import { graphTabId, timelineTabId, servicesTabId, metricsTabId } from './tabs';
import { TabId } from './tabs/models';
import { buildListTimelineEventsEndpoint } from './tabs/Timeline/api';
import useDetails from './useDetails';
import { getTypeIds } from './tabs/Timeline/Event';
import { DetailsUrlQueryParameters } from './models';

import Details from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('../icons/Downtime');
jest.mock(
  '@centreon/centreon-frontend/packages/centreon-ui/src/utils/copy',
  () => jest.fn(),
);

const resourceServiceUuid = 'h1-s1';
const resourceServiceId = 1;
const resourceServiceType = 'service';
const metaServiceResourceType = 'metaservice';

const resourceHostUuid = 'h1';
const resourceHostId = 1;
const resourceHostType = 'host';

const retrievedDetails = {
  acknowledged: false,
  acknowledgement: {
    author_name: 'Admin',
    comment: 'Acknowledged by Admin',
    entry_time: '2020-03-18T18:57:59Z',
  },
  active_checks: false,
  alias: 'Central-Centreon',
  checked: true,
  command_line: 'base_host_alive',
  downtimes: [
    {
      comment: 'First downtime set by Admin',
      end_time: '2020-01-18T18:57:59Z',
      start_time: '2020-01-18T17:57:59Z',
    },
    {
      comment: 'Second downtime set by Admin',
      end_time: '2020-02-18T18:57:59Z',
      start_time: '2020-02-18T17:57:59Z',
    },
  ],
  duration: '22m',
  execution_time: 0.070906,
  flapping: true,
  fqdn: 'central.centreon.com',
  groups: [{ id: 0, name: 'Linux-servers' }],
  id: resourceServiceId,
  information:
    'OK - 127.0.0.1 rta 0.100ms lost 0%\n OK - 127.0.0.1 rta 0.99ms lost 0%\n OK - 127.0.0.1 rta 0.98ms lost 0%\n OK - 127.0.0.1 rta 0.97ms lost 0%',
  last_check: '2020-05-18T16:00Z',
  last_notification: '2020-07-18T17:30:00Z',
  last_status_change: '2020-04-18T15:00Z',
  last_update: '2020-03-18T16:30:00Z',
  latency: 0.005,
  links: {
    endpoints: {
      performance_graph: 'performance_graph',
      timeline: 'timeline',
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
      uris: {
        configuration: undefined,
        logs: undefined,
        reporting: undefined,
      },
    },
    name: 'Centreon',
    status: { severity_code: 1 },
    type: resourceHostType,
  },
  passive_checks: false,
  percent_state_change: 3.5,
  performance_data:
    'rta=0.025ms;200.000;400.000;0; rtmax=0.061ms;;;; rtmin=0.015ms;;;; pl=0%;20;50;0;100',
  severity_level: 10,
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

let context: ResourceContext;

const setSelectedServiceResource = () => {
  context.setSelectedResourceUuid(resourceServiceUuid);
  context.setSelectedResourceId(resourceServiceId);
  context.setSelectedResourceType(resourceServiceType);
  context.setSelectedResourceParentId(resourceHostId);
  context.setSelectedResourceParentType(resourceHostType);
};

const setSelectedHostResource = () => {
  context.setSelectedResourceUuid(resourceHostUuid);
  context.setSelectedResourceId(resourceHostId);
  context.setSelectedResourceType(resourceHostType);
  context.setSelectedResourceParentId(undefined);
  context.setSelectedResourceParentType(undefined);
};

const setSelectedMetaServiceResource = () => {
  context.setSelectedResourceUuid(resourceServiceUuid);
  context.setSelectedResourceId(resourceServiceId);
  context.setSelectedResourceType(metaServiceResourceType);
  context.setSelectedResourceParentId(undefined);
  context.setSelectedResourceParentType(undefined);
};

interface Props {
  openTabId?: TabId;
}

const DetailsTest = ({ openTabId }: Props): JSX.Element => {
  const listingState = useListing();
  const detailState = useDetails();
  const filterState = useFilter();

  if (openTabId) {
    detailState.openDetailsTabId = openTabId;
  }

  context = {
    ...listingState,
    ...detailState,
    ...filterState,
  } as ResourceContext;

  return (
    <ThemeProvider>
      <Context.Provider value={context}>
        <Details />
      </Context.Provider>
    </ThemeProvider>
  );
};

interface RenderDetailsProps {
  openTabId?: TabId;
}

const renderDetails = (
  { openTabId }: RenderDetailsProps = { openTabId: undefined },
): RenderResult => render(<DetailsTest openTabId={openTabId} />);

const mockedLocalStorageGetItem = jest.fn();
const mockedLocalStorageSetItem = jest.fn();

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
    act(() => {
      context.clearSelectedResource();
    });
    act(() => {
      context.changeSelectedTimePeriod(lastDayPeriod.id);
    });
  });

  it('displays resource details information', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });

    const { getByText, queryByText, getAllByText } = renderDetails();

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        context.getSelectedResourceDetailsEndpoint() as string,
        expect.anything(),
      );
    });

    expect(getByText('10')).toBeInTheDocument();
    expect(getByText('CRITICAL')).toBeInTheDocument();
    expect(getByText('Centreon')).toBeInTheDocument();

    expect(getByText(labelFqdn)).toBeInTheDocument();
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

    expect(getByText(labelCurrentStateDuration)).toBeInTheDocument();
    expect(getByText('22m - 3/3 (Hard)')).toBeInTheDocument();

    expect(getByText(labelLastStateChange)).toBeInTheDocument();
    expect(getByText('04/18/2020 5:00 PM')).toBeInTheDocument();

    expect(getByText(labelLastCheck)).toBeInTheDocument();
    expect(getByText('05/18/2020 6:00 PM')).toBeInTheDocument();

    expect(getByText(labelNextCheck)).toBeInTheDocument();
    expect(getByText('06/18/2020 7:15 PM')).toBeInTheDocument();

    expect(getByText(labelCheckDuration)).toBeInTheDocument();
    expect(getByText('0.070906 s')).toBeInTheDocument();

    expect(getByText(labelLatency)).toBeInTheDocument();
    expect(getByText('0.005 s')).toBeInTheDocument();

    expect(getByText(labelCheck)).toBeInTheDocument();

    expect(getByText(labelPercentStateChange)).toBeInTheDocument();
    expect(getByText('3.5%')).toBeInTheDocument();

    expect(getByText(labelLastNotification)).toBeInTheDocument();
    expect(getByText('07/18/2020 7:30 PM')).toBeInTheDocument();

    expect(getByText(labelCurrentNotificationNumber)).toBeInTheDocument();
    expect(getByText('3')).toBeInTheDocument();

    expect(getByText(labelGroups)).toBeInTheDocument();
    expect(getByText('Linux-servers')).toBeInTheDocument();

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
    [label1Day, '2020-01-20T06:00:00.000Z', 20, undefined],
    [label7Days, '2020-01-14T06:00:00.000Z', 100, last7Days.id],
    [label31Days, '2019-12-21T06:00:00.000Z', 500, last31Days.id],
  ])(
    `queries performance graphs and timelines with %p period when the Graph tab is selected and "Display events" option is activated`,
    async (period, startIsoString, timelineEventsLimit, periodId) => {
      mockedAxios.get
        .mockResolvedValueOnce({ data: retrievedDetails })
        .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
        .mockResolvedValueOnce({ data: retrievedTimeline })
        .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
        .mockResolvedValueOnce({ data: retrievedTimeline });

      const { getByText, getByLabelText, findByText } = renderDetails({
        openTabId: graphTabId,
      });

      act(() => {
        setSelectedServiceResource();
      });

      userEvent.click(getByText(period) as HTMLElement);

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          `${retrievedDetails.links.endpoints.performance_graph}?start=${startIsoString}&end=${currentDateIsoString}`,
          expect.anything(),
        );
      });

      userEvent.click(getByLabelText(labelGraphOptions).firstChild as Element);
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

        if (!isNil(periodId)) {
          expect(context.selectedTimePeriod?.id).toEqual(periodId);
        }
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

    const {
      findAllByLabelText,
      queryByLabelText,
      getByLabelText,
      getByText,
      findByText,
    } = renderDetails({
      openTabId: graphTabId,
    });

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    expect(queryByLabelText(labelComment)).toBeNull();
    expect(queryByLabelText(labelAcknowledgement)).toBeNull();
    expect(queryByLabelText(labelDowntime)).toBeNull();

    userEvent.click(getByLabelText(labelGraphOptions).firstChild as Element);

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

    const { getByTitle } = renderDetails();

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    fireEvent.click(getByTitle(labelCopy));

    await waitFor(() =>
      expect(copyToClipboard).toHaveBeenCalledWith(
        retrievedDetails.command_line,
      ),
    );
  });
  it('displays retrieved timeline events and filtered by selected event types, when the Timeline tab is selected', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedTimeline });
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedTimeline });

    const { getByText, getAllByLabelText, baseElement, debug, getByLabelText } =
      renderDetails({
        openTabId: timelineTabId,
      });

    act(() => {
      setSelectedServiceResource();
    });

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

    debug(getByLabelText('test'), 100000);

    expect(getByText(labelToday)).toBeInTheDocument();

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

  it('displays the shortcut links when the More icon is clicked', async () => {
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

    const { getByLabelText, getAllByLabelText } = renderDetails();

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    userEvent.click(getByLabelText(labelShortcuts).firstChild as HTMLElement);

    expect(getAllByLabelText(labelConfigure)[0]).toHaveAttribute(
      'href',
      '/configuration',
    );
    expect(getAllByLabelText(labelViewLogs)[0]).toHaveAttribute(
      'href',
      '/logs',
    );
    expect(getAllByLabelText(labelViewReport)[0]).toHaveAttribute(
      'href',
      '/reporting',
    );
  });

  it('sets the details according to the details URL query parameter when given', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: retrievedDetails,
      })
      .mockResolvedValueOnce({
        data: retrievedDetails,
      });

    const retrievedServiceDetails = {
      id: 2,
      parentId: 3,
      parentType: 'host',
      tab: 'details',
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
        `${resourcesEndpoint}/${retrievedServiceDetails.parentType}s/${retrievedServiceDetails.parentId}/${retrievedServiceDetails.type}s/${retrievedServiceDetails.id}`,
        expect.anything(),
      );
    });

    fireEvent.click(getByText(labelDetails));

    const tabFromUrlQueryParameters = path(
      ['details', 'tab'],
      getUrlQueryParameters(),
    );

    await waitFor(() => {
      expect(tabFromUrlQueryParameters).toEqual('details');
    });

    act(() => {
      setSelectedHostResource();
      context.setGraphTabParameters({
        options: defaultGraphOptions,
      });
    });

    act(() => {
      context.setServicesTabParameters({
        graphMode: true,
        options: defaultGraphOptions,
      });
    });

    act(() => {
      context.changeSelectedTimePeriod(last7Days.id);
    });

    const updatedDetailsFromQueryParameters = getUrlQueryParameters()
      .details as DetailsUrlQueryParameters;

    await waitFor(() => {
      expect(updatedDetailsFromQueryParameters).toEqual({
        customTimePeriod: {
          end: '2020-01-21T06:00:00.000Z',
          start: '2020-01-14T06:00:00.000Z',
        },
        id: 1,
        selectedTimePeriodId: 'last_7_days',
        tab: 'details',
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
            graphMode: true,
            options: {
              displayEvents: {
                id: 'displayEvents',
                label: labelDisplayEvents,
                value: false,
              },
            },
          },
        },
        type: 'host',
        uuid: 'h1',
      });

      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${resourcesEndpoint}/${updatedDetailsFromQueryParameters.type}s/${updatedDetailsFromQueryParameters.id}`,
        expect.anything(),
      );
    });
  });

  it('copies the current URL when the copy resource link button is clicked', async () => {
    mockedAxios.get.mockResolvedValueOnce({
      data: retrievedDetails,
    });

    const { getByLabelText } = renderDetails();

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    act(() => {
      fireEvent.click(
        getByLabelText(labelCopyLink).firstElementChild as HTMLElement,
      );
    });

    await waitFor(() => {
      expect(copyToClipboard).toHaveBeenCalledWith(window.location.href);
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

    const { getByText, queryByText } = renderDetails({
      openTabId: servicesTabId,
    });

    act(() => {
      setSelectedHostResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      buildResourcesEndpoint({
        hostGroupIds: [],
        limit: 30,
        monitoringServerIds: [],
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
        serviceGroupIds: [],
        states: [],
        statuses: [],
      }),
      expect.anything(),
    );

    expect(getByText('OK')).toBeInTheDocument();
    expect(getByText('Ping')).toBeInTheDocument();
    expect(getByText('OK - 127.0.0.1 rta 0ms lost 0%'));
    expect(getByText('22m')).toBeInTheDocument();

    expect(getByText('Disk')).toBeInTheDocument();
    expect(getByText('UNKNOWN')).toBeInTheDocument();
    expect(getByText('No output'));
    expect(getByText('21m')).toBeInTheDocument();

    fireEvent.click(getByText('Ping'));

    const [pingService] = retrievedServices.result;

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    expect(context.selectedResourceId).toBe(pingService.id);

    await waitFor(() => {
      expect(queryByText(labelServices)).toBeNull();
    });

    act(() => {
      context.setServicesTabParameters({
        graphMode: false,
        options: defaultGraphOptions,
      });
    });
  });

  it('displays the linked service graphs when the service tab of a host is clicked and the graph mode is activated', async () => {
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
        data: retrievedServices,
      })
      .mockResolvedValueOnce({
        data: retrievedPerformanceGraphData,
      })
      .mockResolvedValueOnce({
        data: retrievedPerformanceGraphData,
      });

    const { getByLabelText, findByText, getByText } = renderDetails({
      openTabId: servicesTabId,
    });

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    fireEvent.click(
      getByLabelText(labelSwitchToGraph).firstElementChild as HTMLElement,
    );

    await findByText(retrievedPerformanceGraphData.global.title);

    expect(context.tabParameters?.services?.graphMode).toEqual(true);

    userEvent.click(getByText(label7Days).parentElement as HTMLElement);

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
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData });

    renderDetails({
      openTabId: graphTabId,
    });

    const startISOString = '2020-01-19T06:00:00.000Z';
    const endISOString = '2020-01-21T06:00:00.000Z';

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-20T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
        cancelTokenRequestParam,
      );
    });

    act(() => {
      context.changeCustomTimePeriod({
        date: new Date(startISOString),
        property: CustomTimePeriodProperty.start,
      });
    });
    act(() => {
      context.changeCustomTimePeriod({
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

    const { getByText } = renderDetails({
      openTabId: graphTabId,
    });

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-20T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
        cancelTokenRequestParam,
      );
    });

    expect(getByText('01/20/2020 7:00 AM')).toBeInTheDocument();
    expect(getByText('01/21/2020 7:00 AM')).toBeInTheDocument();

    userEvent.click(getByText(label7Days).parentElement as HTMLElement);

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

    const { getByLabelText, getByText } = renderDetails({
      openTabId: graphTabId,
    });

    act(() => {
      context.changeCustomTimePeriod({
        date: new Date('2020-01-21T06:00:00.000Z'),
        property: CustomTimePeriodProperty.start,
      });
    });
    act(() => {
      context.changeCustomTimePeriod({
        date: new Date('2020-01-21T06:00:00.000Z'),
        property: CustomTimePeriodProperty.end,
      });
    });

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

      const { getByLabelText } = renderDetails({
        openTabId: graphTabId,
      });

      act(() => {
        setSelectedServiceResource();
      });

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-20T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
          cancelTokenRequestParam,
        );
      });

      userEvent.click(getByLabelText(iconLabel));

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          `${retrievedDetails.links.endpoints.performance_graph}?start=${startISOString}&end=${endISOString}`,
          cancelTokenRequestParam,
        );
      });
    },
  );

  it('display retrieved metrics when the selected Resource is a meta service and the metrics tab is selected', async () => {
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

    const { getByText } = renderDetails({
      openTabId: metricsTabId,
    });

    act(() => {
      setSelectedMetaServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    expect(getByText('pl')).toBeInTheDocument();
    expect(getByText('3 (%)')).toBeInTheDocument();
    expect(getByText(service.name)).toBeInTheDocument();
  });

  it('displays Min, Max and Average values in the legend when the Graph tab is selected', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
      .mockResolvedValueOnce({ data: retrievedTimeline });

    const { getByLabelText, getByText } = renderDetails({
      openTabId: graphTabId,
    });

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-20T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
        cancelTokenRequestParam,
      );
    });

    expect(getByLabelText(labelMin)).toBeInTheDocument();
    expect(getByText('N/A')).toBeInTheDocument();
    expect(getByLabelText(labelMax)).toBeInTheDocument();
    expect(getByText('2.46k')).toBeInTheDocument();
    expect(getByLabelText(labelAvg)).toBeInTheDocument();
    expect(getByText('1.23k')).toBeInTheDocument();
  });

  it('Filters on a group when the corresponding chip is clicked and the Details tab is selected', async () => {
    mockedAxios.get.mockResolvedValueOnce({
      data: retrievedDetails,
    });

    const { getByText } = renderDetails();

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    userEvent.click(getByText('Linux-servers').parentElement as HTMLElement);

    await waitFor(() => {
      expect(context.getCriteriaValue(CriteriaNames.serviceGroups)).toEqual([
        { id: 0, name: 'Linux-servers' },
      ]);
    });
  });

  it('displays the resource configuration link when the resource name is hovered', async () => {
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

    const { getByText, getByLabelText } = renderDetails();

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        context.getSelectedResourceDetailsEndpoint() as string,
        expect.anything(),
      );
    });

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

    const { getByText, queryByText } = renderDetails();

    act(() => {
      setSelectedServiceResource();
    });

    mockedLocalStorageGetItem.mockReturnValue(
      JSON.stringify([labelMonitoringServer, labelStatusInformation]),
    );

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        context.getSelectedResourceDetailsEndpoint() as string,
        expect.anything(),
      );
    });

    expect(getByText(labelMonitoringServer)).toBeInTheDocument();
    expect(getByText(labelStatusInformation)).toBeInTheDocument();

    expect(queryByText(labelLastCheck)).not.toBeInTheDocument();
    expect(queryByText(labelCommand)).not.toBeInTheDocument();
  });

  it('queries the performance graphs with the time period selected in the "Timeline" tab when the "Graph" tab is selected and the "Timeline" tab was selected', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValueOnce({ data: retrievedTimeline })
      .mockResolvedValueOnce({ data: retrievedTimeline })
      .mockResolvedValueOnce(retrievedFilters)
      .mockResolvedValueOnce({ data: retrievedDetails })
      .mockResolvedValueOnce({ data: retrievedPerformanceGraphData })
      .mockResolvedValueOnce({ data: retrievedTimeline });

    const { getByText } = renderDetails({
      openTabId: timelineTabId,
    });

    act(() => {
      setSelectedServiceResource();
    });

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

    userEvent.click(getByText(label7Days).parentElement as HTMLElement);

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

    expect(context.selectedTimePeriod?.id).toEqual(last7Days.id);

    act(() => {
      context.clearSelectedResource();
    });

    renderDetails({
      openTabId: graphTabId,
    });

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${retrievedDetails.links.endpoints.performance_graph}?start=2020-01-14T06:00:00.000Z&end=2020-01-21T06:00:00.000Z`,
        cancelTokenRequestParam,
      );
    });
  });
});
