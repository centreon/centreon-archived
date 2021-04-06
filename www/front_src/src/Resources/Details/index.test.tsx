import * as React from 'react';

import { last, head, equals, reject, path, isNil } from 'ramda';
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
} from '@centreon/ui';
import copyToClipboard from '@centreon/ui/src/utils/copy';

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
  labelActive,
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
  labelResourceFlapping,
  labelNo,
  labelComment,
  labelConfigure,
  labelViewLogs,
  labelViewReport,
  labelHost,
  labelService,
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
} from '../translatedLabels';
import Context, { ResourceContext } from '../Context';
import useListing from '../Listing/useListing';
import { resourcesEndpoint } from '../api/endpoint';
import { buildResourcesEndpoint } from '../Listing/api/endpoint';
import { cancelTokenRequestParam } from '../testUtils';

import { last7Days, last31Days, lastDayPeriod } from './tabs/Graph/models';
import {
  graphTabId,
  timelineTabId,
  shortcutsTabId,
  servicesTabId,
} from './tabs';
import { TabId } from './tabs/models';
import { buildListTimelineEventsEndpoint } from './tabs/Timeline/api';
import useDetails from './useDetails';
import { getTypeIds } from './tabs/Timeline/Event';
import { DetailsUrlQueryParameters } from './models';

import Details from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('../icons/Downtime');
jest.mock('@centreon/ui/src/utils/copy', () => jest.fn());

const resourceServiceUuid = 'h1-s1';
const resourceServiceId = 1;
const resourceServiceType = 'service';

const resourceHostUuid = 'h1';
const resourceHostId = 1;
const resourceHostType = 'host';

const retrievedDetails = {
  uuid: resourceServiceUuid,
  id: resourceServiceId,
  type: resourceServiceType,
  name: 'Central',
  severity_level: 10,
  status: { name: 'Critical', severity_code: 1 },
  parent: {
    id: resourceHostId,
    type: resourceHostType,
    name: 'Centreon',
    status: { severity_code: 1 },
    links: {
      uris: {
        configuration: undefined,
        logs: undefined,
        reporting: undefined,
      },
    },
  },
  fqdn: 'central.centreon.com',
  alias: 'Central-Centreon',
  monitoring_server_name: 'Poller',
  acknowledged: false,
  checked: true,
  execution_time: 0.070906,
  last_check: '2020-05-18T16:00Z',
  last_status_change: '2020-04-18T15:00Z',
  last_update: '2020-03-18T16:30:00Z',
  information:
    'OK - 127.0.0.1 rta 0.100ms lost 0%\n OK - 127.0.0.1 rta 0.99ms lost 0%\n OK - 127.0.0.1 rta 0.98ms lost 0%\n OK - 127.0.0.1 rta 0.97ms lost 0%',
  timezone: 'Europe/Paris',
  active_checks: true,
  command_line: 'base_host_alive',
  last_notification: '2020-07-18T17:30:00Z',
  latency: 0.005,
  next_check: '2020-06-18T17:15:00Z',
  notification_number: 3,
  flapping: false,
  percent_state_change: 3.5,
  downtimes: [
    {
      start_time: '2020-01-18T17:57:59Z',
      end_time: '2020-01-18T18:57:59Z',
      comment: 'First downtime set by Admin',
    },
    {
      start_time: '2020-02-18T17:57:59Z',
      end_time: '2020-02-18T18:57:59Z',
      comment: 'Second downtime set by Admin',
    },
  ],
  acknowledgement: {
    author_name: 'Admin',
    entry_time: '2020-03-18T18:57:59Z',
    comment: 'Acknowledged by Admin',
  },
  performance_data:
    'rta=0.025ms;200.000;400.000;0; rtmax=0.061ms;;;; rtmin=0.015ms;;;; pl=0%;20;50;0;100',
  duration: '22m',
  tries: '3/3 (Hard)',
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
  groups: [{ id: 0, name: 'Linux-servers' }],
};

const retrievedPerformanceGraphData = {
  global: {
    title: 'Ping graph',
  },
  times: [
    '2020-06-19T07:30:00Z',
    '2020-06-20T06:55:00Z',
    '2020-06-23T06:55:00Z',
  ],
  metrics: [
    {
      data: [2, 0, 1],
      ds_data: {
        ds_color_line: '#fff',
        ds_filled: false,
        ds_color_area: 'transparent',
        ds_transparency: 80,
        ds_legend: 'Round-Trip-Time Average',
        ds_average: '1234',
        ds_min: null,
        ds_max: '2456',
      },
      metric: 'rta',
      unit: 'ms',
      legend: 'Round-Trip-Time Average (ms)',
    },
  ],
};

const retrievedTimeline = {
  result: [
    {
      type: 'event',
      id: 1,
      date: '2020-06-22T08:40:00Z',
      tries: 1,
      content: 'INITIAL HOST STATE: Centreon-Server;UP;HARD;1;',
      status: {
        severity_code: 5,
        name: 'UP',
      },
    },
    {
      type: 'event',
      id: 2,
      date: '2020-06-22T08:35:00Z',
      tries: 3,
      content: 'INITIAL HOST STATE: Centreon-Server;DOWN;HARD;3;',
      status: {
        severity_code: 1,
        name: 'DOWN',
      },
    },
    {
      type: 'notification',
      id: 3,
      date: '2020-06-21T07:40:00Z',
      content: 'My little notification',
      contact: {
        name: 'admin',
      },
    },
    {
      type: 'acknowledgement',
      id: 4,
      date: '2020-06-20T07:35:00Z',
      contact: {
        name: 'admin',
      },
      content: 'My little ack',
    },
    {
      type: 'downtime',
      id: 5,
      date: '2020-06-20T07:30:00Z',
      start_date: '2020-06-20T07:30:00Z',
      end_date: '2020-06-22T07:33:00Z',
      contact: {
        name: 'admin',
      },
      content: 'My little dt',
    },
    {
      type: 'downtime',
      id: 6,
      date: '2020-06-20T06:57:00Z',
      start_date: '2020-06-19T07:30:00Z',
      end_date: null,
      contact: {
        name: 'super_admin',
      },
      content: 'My little ongoing dt',
    },
    {
      type: 'comment',
      id: 7,
      date: '2020-06-20T06:55:00Z',
      start_date: '2020-06-20T07:30:00Z',
      end_date: '2020-06-22T07:33:00Z',
      contact: {
        name: 'admin',
      },
      content: 'My little comment',
    },
  ],
  meta: {
    page: 1,
    limit: 10,
    total: 5,
  },
};

const retrievedServices = {
  result: [
    {
      id: 3,
      name: 'Ping',
      status: {
        severity_code: 5,
        name: 'Ok',
      },
      information: 'OK - 127.0.0.1 rta 0ms lost 0%',
      duration: '22m',
      links: {
        endpoints: {
          performance_graph: 'ping-performance',
        },
      },
    },
    {
      id: 4,
      name: 'Disk',
      status: {
        severity_code: 6,
        name: 'Unknown',
      },
      information: 'No output',
      duration: '21m',
    },
  ],
  meta: {
    page: 1,
    limit: 10,
    total: 2,
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

interface Props {
  openTabId?: TabId;
}

const DetailsTest = ({ openTabId }: Props): JSX.Element => {
  const listingState = useListing();
  const detailState = useDetails();

  if (openTabId) {
    detailState.openDetailsTabId = openTabId;
  }

  context = {
    ...listingState,
    ...detailState,
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

describe(Details, () => {
  beforeEach(() => {
    mockDate.set(currentDateIsoString);
  });

  afterEach(() => {
    mockDate.reset();
    mockedAxios.get.mockReset();
    act(() => {
      context.setGraphTabParameters({
        selectedTimePeriodId: lastDayPeriod.id,
        selectedCustomTimePeriod: undefined,
      });
      context.clearSelectedResource();
    });
  });

  it('displays resource details information', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });

    const {
      getByText,
      queryByText,
      getAllByText,
      getAllByTitle,
    } = renderDetails();

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

    expect(getAllByTitle(labelActive)).toHaveLength(2);

    expect(getByText(labelCheckDuration)).toBeInTheDocument();
    expect(getByText('0.070906 s')).toBeInTheDocument();

    expect(getByText(labelLatency)).toBeInTheDocument();
    expect(getByText('0.005 s')).toBeInTheDocument();

    expect(getByText(labelResourceFlapping)).toBeInTheDocument();
    expect(getByText(labelNo)).toBeInTheDocument();

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
          expect(context.tabParameters.graph).toEqual({
            selectedTimePeriodId: periodId,
            graphOptions: {
              displayEvents: {
                id: 'displayEvents',
                label: 'Display events',
                value: true,
              },
              displayTooltips: {
                id: 'displayTooltips',
                label: 'Display metric values tooltip',
                value: false,
              },
            },
          });
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

    expect(commentAnnotations).toHaveLength(1);
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

  it('displays retrieved timeline events, grouped by date, and filtered by selected event types, when the Timeline tab is selected', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedTimeline });
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedTimeline });

    const {
      getByText,
      getAllByText,
      getAllByLabelText,
      baseElement,
    } = renderDetails({
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

    expect(getByText('06/22/2020')).toBeInTheDocument();

    expect(getByText('10:40 AM')).toBeInTheDocument();
    expect(getAllByLabelText('Event')).toHaveLength(3); // 2 events + 1 selected option
    expect(getByText('UP')).toBeInTheDocument();
    expect(getByText('Tries: 1')).toBeInTheDocument();
    expect(
      getByText('INITIAL HOST STATE: Centreon-Server;UP;HARD;1;'),
    ).toBeInTheDocument();

    expect(getByText('10:35 AM')).toBeInTheDocument();
    expect(getByText('DOWN')).toBeInTheDocument();
    expect(getByText('Tries: 3')).toBeInTheDocument();
    expect(
      getByText('INITIAL HOST STATE: Centreon-Server;DOWN;HARD;3;'),
    ).toBeInTheDocument();

    expect(getByText('06/21/2020')).toBeInTheDocument();

    expect(getByText('9:40 AM')).toBeInTheDocument();
    expect(getByText('My little notification'));

    expect(getByText('06/20/2020')).toBeInTheDocument();

    expect(getByText('9:35 AM')).toBeInTheDocument();
    expect(getByText('My little ack'));

    expect(
      getByText('From 06/20/2020 9:30 AM To 06/22/2020 9:33 AM'),
    ).toBeInTheDocument();
    expect(getByText('My little dt'));

    expect(getByText('From 06/19/2020 9:30 AM')).toBeInTheDocument();
    expect(getByText('My little ongoing dt'));

    expect(getByText('8:55 AM')).toBeInTheDocument();
    expect(getByText('My little comment'));

    const dateRegExp = /\d+\/\d+\/\d+$/;

    expect(
      getAllByText(dateRegExp)
        .map((element) => element.textContent)
        .filter((text) => text !== '06/23/2020'), // corresponds to one of the graph X Scale ticks
    ).toEqual(['06/22/2020', '06/21/2020', '06/20/2020']);

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

  it('displays the shortcut links when the shortcuts tab is selected', async () => {
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
        parent: {
          ...retrievedDetails.parent,
          links: {
            uris: {
              configuration: '/host/configuration',
              logs: '/host/logs',
              reporting: '/host/reporting',
            },
          },
        },
      },
    });

    const { getByText, getAllByText } = renderDetails({
      openTabId: shortcutsTabId,
    });

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    expect(getAllByText(labelConfigure)[0]).toHaveAttribute(
      'href',
      '/configuration',
    );
    expect(getAllByText(labelViewLogs)[0]).toHaveAttribute('href', '/logs');
    expect(getAllByText(labelViewReport)[0]).toHaveAttribute(
      'href',
      '/reporting',
    );

    expect(getByText(labelService)).toBeInTheDocument();
    expect(getByText(labelHost)).toBeInTheDocument();

    expect(getAllByText(labelConfigure)[1]).toHaveAttribute(
      'href',
      '/host/configuration',
    );
    expect(getAllByText(labelViewLogs)[1]).toHaveAttribute(
      'href',
      '/host/logs',
    );
    expect(getAllByText(labelViewReport)[1]).toHaveAttribute(
      'href',
      '/host/reporting',
    );
  });

  it('does not display parent shortcut links when the selected resource is a host and the shortcuts tab is selected', async () => {
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

    const { getByText, getAllByText, queryByText } = renderDetails({
      openTabId: shortcutsTabId,
    });

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    expect(getAllByText(labelConfigure)).toHaveLength(1);
    expect(getAllByText(labelViewLogs)).toHaveLength(1);
    expect(getAllByText(labelViewReport)).toHaveLength(1);

    expect(queryByText(labelService)).not.toBeInTheDocument();
    expect(getByText(labelHost)).toBeInTheDocument();
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
      uuid: 'h3-s2',
      id: 2,
      parentId: 3,
      parentType: 'host',
      type: 'service',
      tab: 'shortcuts',
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

      expect(context.openDetailsTabId).toEqual(shortcutsTabId);
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
        selectedTimePeriodId: last7Days.id,
      });
    });

    act(() => {
      context.setServicesTabParameters({
        graphTimePeriod: {
          selectedTimePeriodId: last31Days.id,
        },
        graphMode: true,
      });
    });

    const updatedDetailsFromQueryParameters = getUrlQueryParameters()
      .details as DetailsUrlQueryParameters;

    await waitFor(() => {
      expect(updatedDetailsFromQueryParameters).toEqual({
        uuid: 'h1',
        id: 1,
        tab: 'details',
        tabParameters: {
          graph: {
            selectedTimePeriodId: last7Days.id,
          },
          services: {
            graphMode: true,
            graphTimePeriod: {
              selectedTimePeriodId: last31Days.id,
            },
          },
        },
        type: 'host',
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
        limit: 30,
        page: 1,
        resourceTypes: ['service'],
        statuses: [],
        states: [],
        hostGroupIds: [],
        serviceGroupIds: [],
        monitoringServerIds: [],
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
        graphTimePeriod: {},
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

    const { getByLabelText, findByText, getAllByText } = renderDetails({
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

    userEvent.click(head(getAllByText(label1Day)) as HTMLElement);
    userEvent.click(last(getAllByText(label7Days)) as HTMLElement);

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(5);
    });

    expect(
      context.tabParameters?.services?.graphTimePeriod.selectedTimePeriodId,
    ).toEqual(last7Days.id);
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
      context.setGraphTabParameters({
        selectedTimePeriodId: undefined,
        selectedCustomTimePeriod: {
          start: startISOString,
          end: endISOString,
        },
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
      setSelectedServiceResource();
      context.setGraphTabParameters({
        selectedTimePeriodId: undefined,
        selectedCustomTimePeriod: {
          start: '2020-01-21T06:00:00.000Z',
          end: '2020-01-21T06:00:00.000Z',
        },
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
});
