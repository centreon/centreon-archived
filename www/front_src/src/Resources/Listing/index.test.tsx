import * as React from 'react';

import { useSelector } from 'react-redux';
import {
  render,
  RenderResult,
  waitFor,
  fireEvent,
} from '@testing-library/react';
import axios from 'axios';

import { partition, where, contains, head, split, pipe } from 'ramda';
import Listing from '.';
import { getColumns } from './columns';
import { Resource } from '../models';
import Context from '../Context';
import useActions from '../Actions/useActions';
import useDetails from '../Details/useDetails';
import useListing from './useListing';
import useFilter from '../Filter/useFilter';
import { labelInDowntime, labelAcknowledged } from '../translatedLabels';
import { getListingEndpoint } from '../testUtils';

const columns = getColumns({ onAcknowledge: jest.fn() });

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('react-redux', () => ({
  useSelector: jest.fn(),
}));

jest.mock('../icons/Downtime');

const appState = {
  intervals: {
    AjaxTimeReloadMonitoring: 60,
  },
};

const fillEntities = (): Array<Resource> => {
  const entityCount = 31;
  return new Array(entityCount).fill(0).map((_, index) => ({
    id: index,
    name: `E${index}`,
    status: {
      code: 0,
      name: 'OK',
      severity_code: 5,
    },
    acknowledged: index % 2 === 0,
    acknowledgement_endpoint: `/monitoring/acknowledgement/${index}`,
    in_downtime: index % 3 === 0,
    downtime_endpoint: `/monitoring/downtime/${index}`,
    duration: '1m',
    last_check: '1m',
    tries: '1',
    short_type: index % 4 === 0 ? 's' : 'h',
    information:
      index % 5 === 0 ? `Entity ${index}` : `Entity ${index}\n Line ${index}`,
    type: index % 4 === 0 ? 'service' : 'host',
    details_endpoint: 'endpoint',
  }));
};

const entities = fillEntities();
const retrievedListing = {
  result: entities,
  meta: {
    page: 1,
    limit: 10,
    search: {},
    sort_by: {},
    total: entities.length,
  },
};

const ListingTest = (): JSX.Element => {
  const listingState = useListing();
  const actionsState = useActions();
  const detailsState = useDetails();
  const filterState = useFilter();

  return (
    <Context.Provider
      value={{
        ...listingState,
        ...actionsState,
        ...detailsState,
        ...filterState,
      }}
    >
      <Listing />
    </Context.Provider>
  );
};

const renderListing = (): RenderResult => render(<ListingTest />);

const cancelTokenRequestParam = { cancelToken: {} };

window.clearInterval = jest.fn();
window.setInterval = jest.fn();

describe('Listing', () => {
  beforeEach(() => {
    useSelector.mockImplementation((callback) => {
      return callback(appState);
    });
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
  });

  afterEach(() => {
    useSelector.mockClear();
    mockedAxios.get.mockReset();
  });

  it('displays first part of information when multiple (split by \n) are available', async () => {
    const { getByText, queryByText } = renderListing();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    const [resourcesWithMultipleLines, resourcesWithSingleLines] = partition(
      where({ information: contains('\n') }),
      retrievedListing.result,
    );

    resourcesWithMultipleLines.forEach(({ information }) => {
      expect(
        getByText(pipe(split('\n'), head)(information)),
      ).toBeInTheDocument();
      expect(queryByText(information)).not.toBeInTheDocument();
    });

    resourcesWithSingleLines.forEach(({ information }) => {
      expect(getByText(information)).toBeInTheDocument();
    });
  });

  it.each(
    columns
      .filter(({ sortable }) => sortable !== false)
      .map(({ id, label, sortField }) => [id, label, sortField]),
  )(
    'executes a listing request with sort_by param when %p column is clicked',
    async (id, label, sortField) => {
      const { getByLabelText } = renderListing();

      mockedAxios.get.mockResolvedValue({ data: retrievedListing });

      const sortBy = (sortField || id) as string;

      fireEvent.click(getByLabelText(`Column ${label}`));

      expect(mockedAxios.get).toHaveBeenLastCalledWith(
        getListingEndpoint({ sort: { [sortBy]: 'desc' } }),
        cancelTokenRequestParam,
      );

      fireEvent.click(getByLabelText(`Column ${label}`));

      await waitFor(() =>
        expect(mockedAxios.get).toHaveBeenLastCalledWith(
          getListingEndpoint({ sort: { [sortBy]: 'asc' } }),
          cancelTokenRequestParam,
        ),
      );
    },
  );

  it('executes a listing request with an updated page param when a change page action is clicked', async () => {
    const { getByLabelText } = renderListing();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        ...retrievedListing,
        meta: { ...retrievedListing.meta, page: 2 },
      },
    });

    fireEvent.click(getByLabelText('Next Page'));

    expect(mockedAxios.get).toHaveBeenLastCalledWith(
      getListingEndpoint({ page: 2 }),
      cancelTokenRequestParam,
    );

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        ...retrievedListing,
        meta: { ...retrievedListing.meta, page: 1 },
      },
    });

    fireEvent.click(getByLabelText('Previous Page'));

    expect(mockedAxios.get).toHaveBeenLastCalledWith(
      getListingEndpoint({ page: 1 }),
      cancelTokenRequestParam,
    );

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        ...retrievedListing,
        meta: { ...retrievedListing.meta, page: 4 },
      },
    });

    fireEvent.click(getByLabelText('Last Page'));

    expect(mockedAxios.get).toHaveBeenLastCalledWith(
      getListingEndpoint({ page: 4 }),
      cancelTokenRequestParam,
    );

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        ...retrievedListing,
        meta: { ...retrievedListing.meta, page: 4 },
      },
    });

    fireEvent.click(getByLabelText('First Page'));

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenLastCalledWith(
        getListingEndpoint({ page: 1 }),
        cancelTokenRequestParam,
      ),
    );
  });

  it('executes a listing request with a limit param when the rows per page value is changed', async () => {
    const { getByDisplayValue } = renderListing();

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

    fireEvent.change(getByDisplayValue('10'), {
      target: { value: '20' },
    });

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getListingEndpoint({ limit: 20 }),
        cancelTokenRequestParam,
      ),
    );
  });

  it('displays downtime details when the downtime state chip is hovered', async () => {
    const { findByLabelText, getByText } = renderListing();

    const entityInDowntime = entities.find(({ in_downtime }) => in_downtime);

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        result: [
          {
            author_name: 'admin',
            start_time: '2020-02-28T09:16:16',
            end_time: '2020-02-28T09:18:16',
            is_fixed: true,
            comment: 'Set by admin',
          },
        ],
      },
    });

    const chipLabel = `${entityInDowntime?.name} ${labelInDowntime}`;

    const chip = await findByLabelText(chipLabel);

    fireEvent.mouseEnter(chip);
    fireEvent.mouseOver(chip);

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenLastCalledWith(
        entityInDowntime?.downtime_endpoint,
        cancelTokenRequestParam,
      ),
    );

    expect(getByText('admin')).toBeInTheDocument();
    expect(getByText('Yes')).toBeInTheDocument();
    expect(getByText('02/28/2020 09:16')).toBeInTheDocument();
    expect(getByText('02/28/2020 09:18')).toBeInTheDocument();
    expect(getByText('Set by admin')).toBeInTheDocument();
  });

  it('displays acknowledgement details when an acknowledged state chip is hovered', async () => {
    const { findByLabelText, getByText } = renderListing();

    const acknowledgedEntity = entities.find(
      ({ acknowledged }) => acknowledged,
    );

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        result: [
          {
            author_name: 'admin',
            entry_time: '2020-02-28T09:16:16',
            is_persistent_comment: true,
            is_sticky: false,
            comment: 'Set by admin',
          },
        ],
      },
    });

    const chipLabel = `${acknowledgedEntity?.name} ${labelAcknowledged}`;

    const chip = await findByLabelText(chipLabel);

    fireEvent.mouseEnter(chip);
    fireEvent.mouseOver(chip);

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenLastCalledWith(
        acknowledgedEntity?.acknowledgement_endpoint,
        cancelTokenRequestParam,
      ),
    );

    expect(getByText('admin')).toBeInTheDocument();
    expect(getByText('02/28/2020 09:16')).toBeInTheDocument();
    expect(getByText('Yes')).toBeInTheDocument();
    expect(getByText('No')).toBeInTheDocument();
    expect(getByText('Set by admin')).toBeInTheDocument();
  });
});
