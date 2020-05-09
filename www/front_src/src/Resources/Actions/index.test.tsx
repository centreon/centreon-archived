import * as React from 'react';

import formatISO from 'date-fns/formatISO';
import mockDate from 'mockdate';
import axios from 'axios';
import { useSelector } from 'react-redux';

import {
  render,
  RenderResult,
  waitFor,
  fireEvent,
  act,
} from '@testing-library/react';

import { last } from 'ramda';
import {
  labelAcknowledgedBy,
  labelDowntimeBy,
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
  labelAcknowledge,
  labelDowntime,
  labelSetDowntime,
  labelAcknowledgeServices,
  labelNotify,
  labelFixed,
  labelChangeEndDate,
  labelCheck,
} from '../translatedLabels';
import Actions from '.';
import useLoadResources from '../Listing/useLoadResources';
import useListing from '../Listing/useListing';
import useActions from './useActions';
import useFilter from '../Filter/useFilter';
import Context, { ResourceContext } from '../Context';
import { mockAppStateSelector, cancelTokenRequestParam } from '../testUtils';
import { Resource } from '../models';
import {
  acknowledgeEndpoint,
  downtimeEndpoint,
  hostCheckEndpoint,
  serviceCheckEndpoint,
} from '../api/endpoint';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const onRefresh = jest.fn();
jest.mock('react-redux', () => ({
  useSelector: jest.fn(),
}));
jest.mock('../icons/Downtime');

const ActionsWithLoading = (): JSX.Element => {
  useLoadResources();

  return <Actions onRefresh={onRefresh} />;
};

let context: ResourceContext;

const ActionsWithContext = (): JSX.Element => {
  const listingState = useListing();
  const actionsState = useActions();
  const filterState = useFilter();

  context = {
    ...listingState,
    ...actionsState,
    ...filterState,
  } as ResourceContext;

  return (
    <Context.Provider key="context" value={context}>
      <ActionsWithLoading />
    </Context.Provider>
  );
};

const renderActions = (): RenderResult => {
  return render(<ActionsWithContext />);
};

describe('Actions', () => {
  const labelAcknowledgedByAdmin = `${labelAcknowledgedBy} admin`;
  const labelDowntimeByAdmin = `${labelDowntimeBy} admin`;

  const mockNow = '2020-01-01';

  beforeEach(() => {
    mockedAxios.get.mockResolvedValueOnce({ data: [] }).mockResolvedValueOnce({
      data: {
        username: 'admin',
      },
    });

    mockDate.set(mockNow);
    mockAppStateSelector(useSelector);
  });

  afterEach(() => {
    mockDate.reset();
    mockedAxios.get.mockReset();
  });

  it('executes a listing request when refresh button is clicked', async () => {
    const { getByLabelText } = renderActions();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    mockedAxios.get.mockResolvedValueOnce({ data: {} });

    const refreshButton = getByLabelText(labelRefresh);

    await waitFor(() => expect(refreshButton).toBeEnabled());

    fireEvent.click(refreshButton);

    expect(onRefresh).toHaveBeenCalled();
  });

  it('swaps autorefresh icon when the icon is clicked', async () => {
    const { getByLabelText } = renderActions();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    fireEvent.click(getByLabelText(labelDisableAutorefresh));

    expect(getByLabelText(labelEnableAutorefresh)).toBeTruthy();

    fireEvent.click(getByLabelText(labelEnableAutorefresh));

    expect(getByLabelText(labelDisableAutorefresh)).toBeTruthy();
  });

  it.each([
    [labelAcknowledge, labelAcknowledgedByAdmin, labelAcknowledge],
    [labelDowntime, labelDowntimeByAdmin, labelSetDowntime],
  ])(
    'cannot send a %p request when the corresponding action is fired and the comment field is left empty',
    async (labelAction, labelComment, labelConfirmAction) => {
      const { getByText, getAllByText, findByText } = renderActions();

      const selectedResources = [{} as Resource];

      act(() => {
        context.setSelectedResources(selectedResources);
      });

      await waitFor(() =>
        expect(context.selectedResources).toEqual(selectedResources),
      );

      fireEvent.click(getByText(labelAction));

      const commentField = await findByText(labelComment);

      fireEvent.change(commentField, {
        target: { value: '' },
      });

      await waitFor(() =>
        expect(
          (last(getAllByText(labelConfirmAction)) as HTMLElement).parentElement,
        ).toBeDisabled(),
      );
    },
  );

  it('sends an acknowledgement request when Resources are selected and the Ackowledgement action is clicked and confirmed', async () => {
    const {
      getByText,
      getByLabelText,
      findByLabelText,
      getAllByText,
    } = renderActions();

    const selectedResources = [
      {
        type: 'host',
        id: 0,
      } as Resource,
    ];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    fireEvent.click(getByText(labelAcknowledge));

    const notifyCheckbox = await findByLabelText(labelNotify);

    fireEvent.click(notifyCheckbox);
    fireEvent.click(getByLabelText(labelAcknowledgeServices));

    mockedAxios.get.mockResolvedValueOnce({ data: {} });
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    fireEvent.click(last(getAllByText(labelAcknowledge)) as HTMLElement);

    await waitFor(() =>
      expect(mockedAxios.post).toHaveBeenCalledWith(
        acknowledgeEndpoint,
        {
          resources: selectedResources,

          acknowledgement: {
            comment: labelAcknowledgedByAdmin,
            is_notify_contacts: true,
            with_services: true,
          },
        },
        cancelTokenRequestParam,
      ),
    );
  });

  it('does not display the "Acknowledge services attached to host" checkbox when only services are selected and the Acknowledge action is clicked', async () => {
    const { getByText, findByText, queryByText } = renderActions();

    const selectedResources = [
      {
        type: 'service',
        id: 0,
      } as Resource,
    ];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    fireEvent.click(getByText(labelAcknowledge));

    await findByText(labelAcknowledgedByAdmin);

    expect(queryByText(labelAcknowledgeServices)).toBeNull();
  });

  it('cannot send a downtime request when Downtime action is clicked, type is flexible and duration is empty', async () => {
    const {
      getByText,
      findByText,
      getByLabelText,
      getByDisplayValue,
    } = renderActions();

    const selectedResources = [{} as Resource];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    fireEvent.click(getByText(labelDowntime));

    await findByText(labelDowntimeByAdmin);

    fireEvent.click(getByLabelText(labelFixed));
    fireEvent.change(getByDisplayValue('3600'), {
      target: { value: '' },
    });

    await waitFor(() =>
      expect(getByText(labelSetDowntime).parentElement).toBeDisabled(),
    );
  });

  it('cannot send a downtime request when Downtime action is clicked and start date is greater than end date', async () => {
    const {
      container,
      getByLabelText,
      getByText,
      findByText,
    } = renderActions();

    const selectedResources = [{} as Resource];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    fireEvent.click(getByText(labelDowntime));

    await findByText(labelDowntimeByAdmin);

    // set previous day as end date using left arrow key
    fireEvent.click(getByLabelText(labelChangeEndDate));
    fireEvent.keyDown(container, { key: 'ArrowLeft', code: 37 });
    fireEvent.keyDown(container, { key: 'Enter', code: 13 });

    await waitFor(() =>
      expect(getByText(labelSetDowntime).parentElement).toBeDisabled(),
    );
  });

  it('sends a downtime request when Resources are selected and the Downtime action is clicked and confirmed', async () => {
    const { getByText, findByText } = renderActions();

    const selectedResources = [
      {
        id: 0,
        type: 'host',
      } as Resource,
    ];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    fireEvent.click(getByText(labelDowntime));

    mockedAxios.get.mockResolvedValueOnce({ data: {} });
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    await findByText(labelDowntimeByAdmin);

    fireEvent.click(getByText(labelSetDowntime));

    const now = new Date();
    const twoHoursMs = 2 * 60 * 60 * 1000;
    const twoHoursFromNow = new Date(Date.now() + twoHoursMs);

    await waitFor(() =>
      expect(mockedAxios.post).toHaveBeenCalledWith(
        downtimeEndpoint,
        {
          resources: selectedResources,
          downtime: {
            comment: labelDowntimeByAdmin,
            duration: 3600,
            start_time: formatISO(now),
            end_time: formatISO(twoHoursFromNow),
            is_fixed: true,
            with_services: true,
          },
        },
        cancelTokenRequestParam,
      ),
    );
  });

  it('sends a check request when Resources are selected and the Check action is clicked', async () => {
    const { getByText } = renderActions();

    const host = {
      id: 0,
      type: 'host',
    } as Resource;

    const service = {
      id: 1,
      type: 'service',
    } as Resource;

    const selectedResources = [host, service];

    act(() => {
      context.setSelectedResources(selectedResources);
    });

    mockedAxios.get.mockResolvedValueOnce({ data: {} });
    mockedAxios.all.mockResolvedValueOnce([]);
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    fireEvent.click(getByText(labelCheck));

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalledWith(
        hostCheckEndpoint,
        [
          {
            parent_resource_id: null,
            resource_id: host.id,
          },
        ],
        cancelTokenRequestParam,
      );

      expect(mockedAxios.post).toHaveBeenCalledWith(
        serviceCheckEndpoint,
        [{ parent_resource_id: null, resource_id: service.id }],
        cancelTokenRequestParam,
      );
    });
  });
});
