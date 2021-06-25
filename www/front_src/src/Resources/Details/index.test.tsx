import * as React from 'react';

import { last, head, equals, reject, path } from 'ramda';
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
  buildListingEndpoint,
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
  labelActive,
  labelCheckDuration,
  labelLatency,
  labelPercentStateChange,
  labelLastNotification,
  labelLastCheck,
  labelCurrentNotificationNumber,
  labelPerformanceData,
  labelLast7Days,
  labelLast24h,
  labelLast31Days,
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
} from '../translatedLabels';
import Context, { ResourceContext } from '../Context';
import { cancelTokenRequestParam } from '../testUtils';
import useListing from '../Listing/useListing';
import { resourcesEndpoint, monitoringEndpoint } from '../api/endpoint';

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
jest.mock(
  '@centreon/centreon-frontend/packages/centreon-ui/src/utils/copy',
  () => jest.fn(),
);

const resourceServiceUuid = 'h1-s1';
const resourceServiceId = 1;
const resourceServiceType = 'service';

const resourceHostUuid = 'h1';
const resourceHostId = 1;
const resourceHostType = 'host';

const retrievedDetails = {
  acknowledged: false,
  acknowledgement: {
    author_name: 'Admin',
    comment: 'Acknowledged by Admin',
    entry_time: '2020-03-18T19:57:59',
  },
  active_checks: true,
  alias: 'Central-Centreon',
  checked: true,
  command_line: 'base_host_alive',
  downtimes: [
    {
      comment: 'First downtime set by Admin',
      end_time: '2020-01-18T19:57:59',
      start_time: '2020-01-18T18:57:59',
    },
    {
      comment: 'Second downtime set by Admin',
      end_time: '2020-02-18T19:57:59',
      start_time: '2020-02-18T18:57:59',
    },
  ],
  duration: '22m',
  execution_time: 0.070906,
  flapping: false,
  fqdn: 'central.centreon.com',
  id: resourceServiceId,
  information:
    'OK - 127.0.0.1 rta 0.100ms lost 0%\n OK - 127.0.0.1 rta 0.99ms lost 0%\n OK - 127.0.0.1 rta 0.98ms lost 0%\n OK - 127.0.0.1 rta 0.97ms lost 0%',
  last_check: '2020-05-18T18:00',
  last_notification: '2020-07-18T19:30',
  last_status_change: '2020-04-18T17:00',
  last_update: '2020-03-18T19:30',
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
  name: 'Central',
  next_check: '2020-06-18T19:15',
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
  percent_state_change: 3.5,
  performance_data:
    'rta=0.025ms;200.000;400.000;0; rtmax=0.061ms;;;; rtmin=0.015ms;;;; pl=0%;20;50;0;100',
  poller_name: 'Poller',
  severity_level: 10,
  status: { name: 'Critical', severity_code: 1 },
  timezone: 'Europe/Paris',
  tries: '3/3 (Hard)',
  type: resourceServiceType,
  uuid: resourceServiceUuid,
};

const performanceGraphData = {
  global: {},
  metrics: [],
  times: [],
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
      date: '2020-06-22T10:40:00',
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
      date: '2020-06-22T10:35:00',
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
      date: '2020-06-21T09:40:00',
      id: 3,
      type: 'notification',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little ack',
      date: '2020-06-20T09:35:00Z',
      id: 4,
      type: 'acknowledgement',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little dt',
      date: '2020-06-20T09:30:00',
      end_date: '2020-06-22T09:33:00',
      id: 5,
      start_date: '2020-06-20T09:30:00',
      type: 'downtime',
    },
    {
      contact: {
        name: 'super_admin',
      },
      content: 'My little ongoing dt',
      date: '2020-06-20T08:57:00',
      end_date: null,
      id: 6,
      start_date: '2020-06-19T09:30:00',
      type: 'downtime',
    },
    {
      contact: {
        name: 'admin',
      },
      content: 'My little comment',
      date: '2020-06-20T08:55:00',
      end_date: '2020-06-22T09:33:00',
      id: 7,
      start_date: '2020-06-20T09:30:00',
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
      display_name: 'Ping',
      duration: '22m',
      id: 3,
      output: 'OK - 127.0.0.1 rta 0ms lost 0%',
      status: {
        name: 'Ok',
        severity_code: 5,
      },
    },
    {
      display_name: 'Disk',
      duration: '21m',
      id: 4,
      output: 'No output',
      status: {
        name: 'Unknown',
        severity_code: 6,
      },
    },
  ],
};

const currentDateIsoString = '2020-06-20T20:00:00.000Z';

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
  });

  it('displays resource details information', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });

    const { getByText, queryByText, getAllByText } = renderDetails();

    act(() => {
      setSelectedServiceResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        context.getSelectedResourceDetailsEndpoint(),
        cancelTokenRequestParam,
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
    expect(getByText(`${labelFrom} 01/18/2020 18:57`)).toBeInTheDocument();
    expect(getByText(`${labelTo} 01/18/2020 19:57`)).toBeInTheDocument();
    expect(getByText(`${labelFrom} 02/18/2020 18:57`)).toBeInTheDocument();
    expect(getByText(`${labelTo} 02/18/2020 19:57`)).toBeInTheDocument();
    expect(getByText('First downtime set by Admin'));
    expect(getByText('Second downtime set by Admin'));

    expect(getByText(labelAcknowledgedBy)).toBeInTheDocument();
    expect(getByText(`Admin ${labelAt} 03/18/2020 19:57`)).toBeInTheDocument();
    expect(getByText('Acknowledged by Admin'));

    expect(getByText(labelTimezone)).toBeInTheDocument();
    expect(getByText('Europe/Paris')).toBeInTheDocument();

    expect(getByText(labelCurrentStateDuration)).toBeInTheDocument();
    expect(getByText('22m')).toBeInTheDocument();
    expect(getByText('3/3 (Hard)')).toBeInTheDocument();

    expect(getByText(labelLastStateChange)).toBeInTheDocument();
    expect(getByText('04/18/2020')).toBeInTheDocument();
    expect(getByText('17:00')).toBeInTheDocument();

    expect(getByText(labelLastCheck)).toBeInTheDocument();
    expect(getByText('05/18/2020')).toBeInTheDocument();
    expect(getByText('18:00')).toBeInTheDocument();

    expect(getByText(labelNextCheck)).toBeInTheDocument();
    expect(getByText('06/18/2020')).toBeInTheDocument();
    expect(getByText('19:15')).toBeInTheDocument();

    expect(getAllByText(labelActive)).toHaveLength(2);

    expect(getByText(labelCheckDuration)).toBeInTheDocument();
    expect(getByText('0.070906 s')).toBeInTheDocument();

    expect(getByText(labelLatency)).toBeInTheDocument();
    expect(getByText('0.005 s')).toBeInTheDocument();

    expect(getByText(labelResourceFlapping)).toBeInTheDocument();
    expect(getByText(labelNo)).toBeInTheDocument();

    expect(getByText(labelPercentStateChange)).toBeInTheDocument();
    expect(getByText('3.5%')).toBeInTheDocument();

    expect(getByText(labelLastNotification)).toBeInTheDocument();
    expect(getByText('07/18/2020')).toBeInTheDocument();
    expect(getByText('19:30')).toBeInTheDocument();

    expect(getByText(labelCurrentNotificationNumber)).toBeInTheDocument();
    expect(getByText('3')).toBeInTheDocument();

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
    [labelLast24h, '2020-06-19T20:00:00.000Z'],
    [labelLast7Days, '2020-06-13T20:00:00.000Z'],
    [labelLast31Days, '2020-05-20T20:00:00.000Z'],
  ])(
    `queries performance graphs with %p period when the Graph tab is selected`,
    async (period, startIsoString) => {
      mockedAxios.get
        .mockResolvedValueOnce({ data: retrievedDetails })
        .mockResolvedValueOnce({ data: performanceGraphData })
        .mockResolvedValueOnce({ data: performanceGraphData });

      const { getByText, getAllByText } = renderDetails({
        openTabId: graphTabId,
      });

      act(() => {
        setSelectedServiceResource();
      });

      await waitFor(() => expect(getByText(labelLast24h)).toBeInTheDocument());

      userEvent.click(head(getAllByText(labelLast24h)) as HTMLElement);

      userEvent.click(last(getAllByText(period)) as HTMLElement);

      await waitFor(() =>
        expect(mockedAxios.get).toHaveBeenCalledWith(
          `${retrievedDetails.links.endpoints.performance_graph}?start=${startIsoString}&end=${currentDateIsoString}`,
          cancelTokenRequestParam,
        ),
      );
    },
  );

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

    const { getByText, getAllByText, baseElement } = renderDetails({
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

    expect(getByText('10:40')).toBeInTheDocument();
    expect(getAllByText('Event')).toHaveLength(4);
    expect(getByText('UP')).toBeInTheDocument();
    expect(getByText('Tries: 1')).toBeInTheDocument();
    expect(
      getByText('INITIAL HOST STATE: Centreon-Server;UP;HARD;1;'),
    ).toBeInTheDocument();

    expect(getByText('10:35')).toBeInTheDocument();
    expect(getByText('DOWN')).toBeInTheDocument();
    expect(getByText('Tries: 3')).toBeInTheDocument();
    expect(
      getByText('INITIAL HOST STATE: Centreon-Server;DOWN;HARD;3;'),
    ).toBeInTheDocument();

    expect(getByText('06/21/2020')).toBeInTheDocument();

    expect(getByText('09:40')).toBeInTheDocument();
    expect(getByText('Notification sent to admin')).toBeInTheDocument();
    expect(getByText('My little notification'));

    expect(getByText('06/20/2020')).toBeInTheDocument();

    expect(getByText('09:35')).toBeInTheDocument();
    expect(getByText('Acknowledgement by admin')).toBeInTheDocument();
    expect(getByText('My little ack'));

    expect(getByText('09:30')).toBeInTheDocument();
    expect(getByText('Downtime by admin')).toBeInTheDocument();
    expect(
      getByText('From 06/20/2020 09:30 To 06/22/2020 09:33'),
    ).toBeInTheDocument();
    expect(getByText('My little dt'));

    expect(getByText('08:57')).toBeInTheDocument();
    expect(getByText('Downtime by super_admin')).toBeInTheDocument();
    expect(getByText('From 06/19/2020 09:30')).toBeInTheDocument();
    expect(getByText('My little ongoing dt'));

    expect(getByText('08:55')).toBeInTheDocument();
    expect(getByText('Comment by admin')).toBeInTheDocument();
    expect(getByText('My little comment'));

    const dateRegExp = /\d+\/\d+\/\d+$/;

    expect(
      getAllByText(dateRegExp).map((element) => element.textContent),
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
      id: 2,
      parentId: 3,
      parentType: 'host',
      tab: 'shortcuts',
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
        cancelTokenRequestParam,
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
    });

    const updatedDetailsFromQueryParameters = getUrlQueryParameters()
      .details as DetailsUrlQueryParameters;

    await waitFor(() => {
      expect(updatedDetailsFromQueryParameters).toEqual({
        id: 1,
        tab: 'details',
        type: 'host',
        uuid: 'h1',
      });

      expect(mockedAxios.get).toHaveBeenCalledWith(
        `${resourcesEndpoint}/${updatedDetailsFromQueryParameters.type}s/${updatedDetailsFromQueryParameters.id}`,
        cancelTokenRequestParam,
      );
    });
  });

  it('copies the current URL when the copy resource link button is clicked', async () => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: retrievedDetails,
      })
      .mockResolvedValueOnce({
        data: retrievedDetails,
      });

    const { getByLabelText } = renderDetails();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

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
        data: retrievedDetails,
      })
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

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    act(() => {
      setSelectedHostResource();
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      buildListingEndpoint({
        baseEndpoint: `${monitoringEndpoint}/hosts/${resourceHostId}/services`,
        parameters: {
          limit: 100,
        },
      }),
      cancelTokenRequestParam,
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
  });
});
