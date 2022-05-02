import axios from 'axios';
import {
  partition,
  where,
  includes,
  head,
  split,
  pipe,
  identity,
  prop,
  reject,
  map,
  __,
  propEq,
  find,
  isNil,
  last,
  equals,
  not,
} from 'ramda';
import userEvent from '@testing-library/user-event';
import { Provider } from 'jotai';

import {
  render,
  RenderResult,
  waitFor,
  fireEvent,
  Matcher,
  act,
  Column,
} from '@centreon/ui';
import { refreshIntervalAtom, userAtom } from '@centreon/ui-context';

import { Resource, ResourceType } from '../models';
import Context, { ResourceContext } from '../testUtils/Context';
import useActions from '../testUtils/useActions';
import useFilter from '../testUtils/useFilter';
import { labelInDowntime, labelAcknowledged } from '../translatedLabels';
import {
  getListingEndpoint,
  cancelTokenRequestParam,
  defaultSecondSortCriteria,
} from '../testUtils';
import { unhandledProblemsFilter } from '../Filter/models';
import useLoadDetails from '../testUtils/useLoadDetails';
import useDetails from '../Details/useDetails';

import useListing from './useListing';
import { getColumns, defaultSelectedColumnIds } from './columns';

import Listing from '.';

const columns = getColumns({
  actions: { onAcknowledge: jest.fn() },
  t: identity,
}) as Array<Column>;

const mockedAxios = axios as jest.Mocked<typeof axios>;

const mockUser = {
  isExportButtonEnabled: true,
  locale: 'en',
  timezone: 'Europe/Paris',
};
const mockRefreshInterval = 60;

jest.mock('../icons/Downtime');
jest.useFakeTimers();

const fillEntities = (): Array<Resource> => {
  const entityCount = 31;

  return new Array(entityCount).fill(0).map((_, index) => ({
    acknowledged: index % 2 === 0,
    duration: '1m',
    id: index,
    in_downtime: index % 3 === 0,
    information:
      index % 5 === 0 ? `Entity ${index}` : `Entity ${index}\n Line ${index}`,
    last_check: '1m',
    links: {
      endpoints: {
        acknowledgement: `/monitoring/acknowledgement/${index}`,
        details: 'endpoint',
        downtime: `/monitoring/downtime/${index}`,
        metrics: 'endpoint',
        performance_graph: index % 6 === 0 ? 'endpoint' : undefined,
        status_graph: index % 3 === 0 ? 'endpoint' : undefined,
        timeline: 'endpoint',
      },
      externals: {
        notes: {
          url: 'https://centreon.com',
        },
      },
      uris: {
        configuration: index % 7 === 0 ? 'uri' : undefined,
        logs: index % 4 === 0 ? 'uri' : undefined,
        reporting: index % 3 === 0 ? 'uri' : undefined,
      },
    },
    name: `E${index}`,
    passive_checks: index % 8 === 0,
    severity_level: 1,
    short_type: index % 4 === 0 ? 's' : 'h',
    status: {
      name: 'OK',
      severity_code: 5,
    },
    tries: '1',
    type: index % 4 === 0 ? ResourceType.service : ResourceType.host,
    uuid: `${index}`,
  }));
};

const entities = fillEntities();
const retrievedListing = {
  meta: {
    limit: 10,
    page: 1,
    search: {},
    sort_by: {},
    total: entities.length,
  },
  result: entities,
};

let context: ResourceContext;

const ListingTest = (): JSX.Element => {
  const listingState = useListing();
  const actionsState = useActions();
  const detailsState = useLoadDetails();
  const filterState = useFilter();

  useDetails();

  context = {
    ...listingState,
    ...actionsState,
    ...detailsState,
    ...filterState,
  };

  return (
    <Context.Provider value={context}>
      <Listing />
    </Context.Provider>
  );
};

const ListingTestWithJotai = (): JSX.Element => (
  <Provider
    initialValues={[
      [userAtom, mockUser],
      [refreshIntervalAtom, mockRefreshInterval],
    ]}
  >
    <ListingTest />
  </Provider>
);

const renderListing = (): RenderResult => render(<ListingTestWithJotai />);

describe(Listing, () => {
  beforeEach(() => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          meta: {
            limit: 30,
            page: 1,
            total: 0,
          },
          result: [],
        },
      })
      .mockResolvedValue({ data: retrievedListing });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  it('displays first part of information when multiple (split by \n) are available', async () => {
    mockedAxios.get.mockReset();
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          meta: {
            limit: 30,
            page: 1,
            total: 0,
          },
          result: [],
        },
      })
      .mockResolvedValueOnce({ data: retrievedListing });

    const { getByText, queryByText } = renderListing();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    const [resourcesWithMultipleLines, resourcesWithSingleLines] = partition(
      where({ information: includes('\n') }),
      retrievedListing.result,
    );

    await waitFor(() =>
      expect(
        getByText(
          pipe<string, Array<string>, Matcher>(
            split('\n'),
            head,
          )(resourcesWithMultipleLines[0].information as string),
        ),
      ).toBeInTheDocument(),
    );

    resourcesWithMultipleLines.forEach(({ information }) => {
      expect(
        getByText(
          pipe<string, Array<string>, Matcher>(
            split('\n'),
            head,
          )(information as string),
        ),
      ).toBeInTheDocument();
      expect(queryByText(information as string)).not.toBeInTheDocument();
    });

    resourcesWithSingleLines.forEach(({ information }) => {
      expect(getByText(information as string)).toBeInTheDocument();
    });
  });

  describe('column sorting', () => {
    afterEach(async () => {
      act(() => {
        context.setCurrentFilter?.(unhandledProblemsFilter);
      });

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalled();
      });
    });

    it.each(
      columns
        .filter(({ sortable }) => sortable !== false)
        .filter(({ id }) => includes(id, defaultSelectedColumnIds))
        .map(({ id, label, sortField }) => [id, label, sortField]),
    )(
      'executes a listing request with sort_by param and stores the order parameter in the URL when %p column is clicked',
      async (id, label, sortField) => {
        const { getByLabelText } = renderListing();

        await waitFor(() => {
          expect(mockedAxios.get).toHaveBeenCalled();
        });

        mockedAxios.get.mockResolvedValue({ data: retrievedListing });

        const sortBy = (sortField || id) as string;

        userEvent.click(getByLabelText(`Column ${label}`));

        const secondSortCriteria =
          not(equals(sortField, 'last_status_change')) &&
          defaultSecondSortCriteria;

        await waitFor(() => {
          expect(mockedAxios.get).toHaveBeenLastCalledWith(
            getListingEndpoint({
              sort: {
                [sortBy]: 'desc',
                ...secondSortCriteria,
              },
            }),
            cancelTokenRequestParam,
          );
        });

        userEvent.click(getByLabelText(`Column ${label}`));

        await waitFor(() =>
          expect(mockedAxios.get).toHaveBeenLastCalledWith(
            getListingEndpoint({
              sort: {
                [sortBy]: 'asc',
                ...secondSortCriteria,
              },
            }),
            cancelTokenRequestParam,
          ),
        );
      },
    );
  });

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

    await waitFor(() => expect(getByLabelText('Next page')).toBeEnabled());

    fireEvent.click(getByLabelText('Next page'));

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenLastCalledWith(
        getListingEndpoint({ page: 2 }),
        cancelTokenRequestParam,
      );
    });

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        ...retrievedListing,
        meta: { ...retrievedListing.meta, page: 1 },
      },
    });

    fireEvent.click(getByLabelText('Previous page'));

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

    fireEvent.click(getByLabelText('Last page'));

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

    fireEvent.click(getByLabelText('First page'));

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
        getListingEndpoint({ limit: 30 }),
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
            comment: 'Set by admin',
            end_time: '2020-02-28T08:18:16Z',
            id: 0,
            is_fixed: true,
            start_time: '2020-02-28T08:16:16Z',
          },
        ],
      },
    });

    const chipLabel = `${entityInDowntime?.name} ${labelInDowntime}`;

    const chip = await findByLabelText(chipLabel, undefined, {
      timeout: 10000,
    });

    fireEvent.mouseEnter(chip);
    fireEvent.mouseOver(chip);

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenLastCalledWith(
        entityInDowntime?.links?.endpoints.downtime,
        cancelTokenRequestParam,
      ),
    );

    await waitFor(() => expect(getByText('admin')).toBeInTheDocument());
    expect(getByText('Yes')).toBeInTheDocument();
    expect(getByText('02/28/2020 9:16 AM')).toBeInTheDocument();
    expect(getByText('02/28/2020 9:18 AM')).toBeInTheDocument();
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
            comment: 'Set by admin',
            entry_time: '2020-02-28T08:16:00Z',
            id: 0,
            is_persistent_comment: true,
            is_sticky: false,
          },
        ],
      },
    });

    const chipLabel = `${acknowledgedEntity?.name} ${labelAcknowledged}`;

    const chip = await findByLabelText(chipLabel, undefined, {
      timeout: 10000,
    });

    fireEvent.mouseEnter(chip);
    fireEvent.mouseOver(chip);

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenLastCalledWith(
        acknowledgedEntity?.links?.endpoints.acknowledgement,
        cancelTokenRequestParam,
      ),
    );

    await waitFor(() => expect(getByText('admin')).toBeInTheDocument());
    expect(getByText('02/28/2020 9:16 AM')).toBeInTheDocument();
    expect(getByText('Yes')).toBeInTheDocument();
    expect(getByText('No')).toBeInTheDocument();
    expect(getByText('Set by admin')).toBeInTheDocument();
  });

  const columnIds = map(prop('id'), columns);

  const additionalIds = reject(
    includes(__, defaultSelectedColumnIds),
    columnIds,
  );

  it.each(additionalIds)(
    'displays additional columns when selected from the corresponding menu',
    async (columnId) => {
      const { getAllByText, getByLabelText, getByText } = renderListing();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalled();
      });

      fireEvent.click(getByLabelText('Add columns').firstChild as HTMLElement);

      const column = find(propEq('id', columnId), columns);
      const columnLabel = column?.label as string;

      const columnShortLabel = column?.shortLabel as string;

      const hasShortLabel = !isNil(columnShortLabel);

      const columnDisplayLabel = hasShortLabel
        ? `${columnLabel} (${columnShortLabel})`
        : columnLabel;

      fireEvent.click(last(getAllByText(columnDisplayLabel)) as HTMLElement);

      const expectedLabelCount = hasShortLabel ? 1 : 2;

      expect(getAllByText(columnDisplayLabel).length).toEqual(
        expectedLabelCount,
      );

      if (hasShortLabel) {
        expect(getByText(columnDisplayLabel)).toBeInTheDocument();
      }
    },
  );
});
