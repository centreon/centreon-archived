import * as React from 'react';

import { last, head } from 'ramda';
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
import * as clipboard from './Body/tabs/Details/clipboard';

import Details from '.';
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
} from '../translatedLabels';
import { detailsTabId, graphTabId, TabId } from './Body/tabs';
import Context, { ResourceContext } from '../Context';
import { cancelTokenRequestParam } from '../testUtils';

import useListing from '../Listing/useListing';
import useDetails from './useDetails';
import { ResourceListing } from '../models';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('../icons/Downtime');
jest.mock('./Body/tabs/Details/clipboard');

const detailsEndpoint = '/resource';
const performanceGraphEndpoint = '/performance';

const retrievedDetails = {
  display_name: 'Central',
  severity: { level: 1 },
  status: { name: 'Critical', severity_code: 1 },
  parent: { name: 'Centreon', status: { severity_code: 1 } },
  poller_name: 'Poller',
  acknowledged: false,
  checked: true,
  execution_time: 0.070906,
  last_check: '2020-05-18T18:00',
  last_state_change: '2020-04-18T17:00',
  last_update: '2020-03-18T19:30',
  output:
    'OK - 127.0.0.1 rta 0.100ms lost 0%\n OK - 127.0.0.1 rta 0.99ms lost 0%\n OK - 127.0.0.1 rta 0.98ms lost 0%\n OK - 127.0.0.1 rta 0.97ms lost 0%',
  timezone: 'Europe/Paris',
  criticality: 10,
  active_checks: true,
  command_line: 'base_host_alive',
  last_notification: '2020-07-18T19:30',
  latency: 0.005,
  next_check: '2020-06-18T19:15',
  notification_number: 3,
  flapping: false,
  percent_state_change: 3.5,
  downtimes: [
    {
      start_time: '2020-01-18T18:57:59',
      end_time: '2020-01-18T19:57:59',
      comment: 'First downtime set by Admin',
    },
    {
      start_time: '2020-02-18T18:57:59',
      end_time: '2020-02-18T19:57:59',
      comment: 'Second downtime set by Admin',
    },
  ],
  acknowledgement: {
    author_name: 'Admin',
    entry_time: '2020-03-18T19:57:59',
    comment: 'Acknowledged by Admin',
  },
  performance_data:
    'rta=0.025ms;200.000;400.000;0; rtmax=0.061ms;;;; rtmin=0.015ms;;;; pl=0%;20;50;0;100',
  duration: '22m',
  tries: '3/3 (Hard)',
};

const performanceGraphData = {
  global: {},
  times: [],
  metrics: [],
};

const currentDateIsoString = '2020-06-20T20:00:00.000Z';

let context: ResourceContext;

interface Props {
  defaultTabId: TabId;
}

const DetailsTest = ({ defaultTabId }: Props): JSX.Element => {
  const listingState = useListing();
  const detailState = useDetails();

  detailState.selectedDetailsEndpoints = {
    details: detailsEndpoint,
    performanceGraph: performanceGraphEndpoint,
  };

  detailState.detailsTabIdToOpen = defaultTabId;

  context = {
    ...listingState,
    ...detailState,
  } as ResourceContext;

  return (
    <Context.Provider value={context}>
      <Details />
    </Context.Provider>
  );
};

const renderDetails = (defaultTabId: TabId = detailsTabId): RenderResult =>
  render(<DetailsTest defaultTabId={defaultTabId} />);

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

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        detailsEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(getByText('10')).toBeInTheDocument();
    expect(getByText('CRITICAL')).toBeInTheDocument();
    expect(getByText('Centreon')).toBeInTheDocument();

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
        .mockResolvedValueOnce({ data: performanceGraphData })
        .mockResolvedValueOnce({ data: retrievedDetails })
        .mockResolvedValueOnce({ data: performanceGraphData });

      const { getByText, getAllByText } = renderDetails(graphTabId);

      await waitFor(() => expect(getByText(labelLast24h)).toBeInTheDocument());

      userEvent.click(head(getAllByText(labelLast24h)) as HTMLElement);

      userEvent.click(last(getAllByText(period)) as HTMLElement);

      await waitFor(() =>
        expect(mockedAxios.get).toHaveBeenCalledWith(
          `${performanceGraphEndpoint}?start=${startIsoString}&end=${currentDateIsoString}`,
          cancelTokenRequestParam,
        ),
      );
    },
  );

  it('copies the command line to clipboard when the copy button is clicked', async () => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedDetails });

    const mockedClipboard = clipboard as jest.Mocked<typeof clipboard>;

    const { getByTitle } = renderDetails();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    fireEvent.click(getByTitle(labelCopy));

    await waitFor(() =>
      expect(mockedClipboard.copy).toHaveBeenCalledWith(
        retrievedDetails.command_line,
      ),
    );
  });

  it('refreshes the details when the listing is updated, async () => {
    mockedAxios.get.mockResolvedValue({ data: retrievedDetails });

    const { getByText } = renderDetails();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());
    expect(getByText(labelStatusInformation)).toBeInTheDocument();

    act(() => {
      context.setListing({} as ResourceListing);
    });

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalledTimes(2));
  });
});
