import React from 'react';

import axios from 'axios';
import { render, wait, within, fireEvent } from '@testing-library/react';
import UserEvent from '@testing-library/user-event';

import Resources from '.';
import {
  labelUnhandledProblems,
  labelResourceProblems,
  labelAll,
  labelResourceName,
  labelSearch,
  labelInDowntime,
  labelAcknowledged,
} from './translatedLabels';
import columns from './columns';
import { Resource } from './models';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('./columns/icons/Downtime');

const getEndpoint = ({
  state = 'unhandled_problems',
  sortBy = undefined,
  sortOrder = undefined,
  page = 1,
  limit = 10,
  search = undefined,
}: {
  state?: string;
  sortBy?: string;
  sortOrder?: string;
  page?: number;
  limit?: number;
  search?: Array<{ field: string; value: string }>;
}): string => {
  const baseEndpoint = 'monitoring/resources';
  const sortParam = sortBy ? `&sort_by={"${sortBy}":"${sortOrder}"}` : '';
  const searchParam = search
    ? `&search={"$or":[${search.map(
        ({ field, value }) => `{"${field}":{"$rg":"${value}"}}`,
      )}]}`
    : '';

  return `${baseEndpoint}?state="${state}"${sortParam}&page=${page}&limit=${limit}${searchParam}`;
};

const cancelTokenRequestParam = { cancelToken: {} };

export const selectOption = (element, optionText): void => {
  const selectButton = element.parentNode.querySelector('[role=button]');

  UserEvent.click(selectButton);

  const listbox = document.body.querySelector(
    'ul[role=listbox]',
  ) as HTMLElement;

  const listItem = within(listbox).getByText(optionText);
  UserEvent.click(listItem);
};

const fillEntities = (): Array<Resource> => {
  const entityCount = 31;
  return new Array(entityCount).fill(0).map((_, index) => ({
    id: `${index}`,
    name: `E${index}`,
    status: {
      code: 0,
      name: 'OK',
    },
    acknowledged: index % 2 === 0,
    acknowledgement_endpoint: `/monitoring/acknowledgement/${index}`,
    in_downtime: index % 3 === 0,
    downtime_endpoint: `/monitoring/downtime/${index}`,
    duration: '1m',
    last_check: '1m',
    tries: '1',
    short_name: 's',
    information: `Entity ${index}`,
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

const searchableFields = [
  'host.name',
  'host.alias',
  'host.address',
  'service.description',
];

describe(Resources, () => {
  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  beforeEach(() => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
  });

  it('executes a listing request with unhnandled_problems state by default', async () => {
    render(<Resources />);

    await wait(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({}),
        cancelTokenRequestParam,
      ),
    );
  });

  it('executes a listing request with selected state filter when state filter is changed', async () => {
    const { getByText } = render(<Resources />);

    await wait(() => expect(mockedAxios.get).toHaveBeenCalled());

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

    selectOption(getByText(labelUnhandledProblems), labelResourceProblems);

    await wait(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({ state: 'resources_problems' }),
        cancelTokenRequestParam,
      ),
    );

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

    selectOption(getByText(labelResourceProblems), labelAll);

    await wait(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({ state: 'all' }),
        cancelTokenRequestParam,
      ),
    );
  });

  it('executes a listing request with sort_by param when a sortable column is clicked', async () => {
    const { getByText } = render(<Resources />);

    await wait(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    columns
      .filter(({ sortable }) => sortable !== false)
      .forEach(({ id, label }) => {
        mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

        fireEvent.click(getByText(label));

        expect(mockedAxios.get).toHaveBeenCalledWith(
          getEndpoint({ sortBy: id, sortOrder: 'desc' }),
          cancelTokenRequestParam,
        );

        mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

        fireEvent.click(getByText(label));

        expect(mockedAxios.get).toHaveBeenCalledWith(
          getEndpoint({ sortBy: id, sortOrder: 'asc' }),
          cancelTokenRequestParam,
        );
      });
  });

  it('executes a listing request with an updated page param when a change page action is clicked', async () => {
    const { getByLabelText } = render(<Resources />);

    await wait(() => {
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
      getEndpoint({ page: 2 }),
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
      getEndpoint({ page: 1 }),
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
      getEndpoint({ page: 4 }),
      cancelTokenRequestParam,
    );

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        ...retrievedListing,
        meta: { ...retrievedListing.meta, page: 4 },
      },
    });

    fireEvent.click(getByLabelText('First Page'));

    expect(mockedAxios.get).toHaveBeenLastCalledWith(
      getEndpoint({ page: 1 }),
      cancelTokenRequestParam,
    );
  });

  it('executes a listing request with a limit param when the rows per page value is changed', () => {
    const { getByDisplayValue } = render(<Resources />);

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

    fireEvent.change(getByDisplayValue('10'), {
      target: { value: '20' },
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      getEndpoint({ limit: 20 }),
      cancelTokenRequestParam,
    );
  });

  searchableFields.forEach((searchableField) => {
    it(`executes a listing request with a search param containing ${searchableField} when ${searchableField} is typed in the search field`, () => {
      const { getByPlaceholderText, getByText } = render(<Resources />);

      const fieldSearchValue = 'foobar';

      fireEvent.change(getByPlaceholderText(labelResourceName), {
        target: { value: `${searchableField}:${fieldSearchValue}` },
      });

      mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

      fireEvent.click(getByText(labelSearch));

      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({
          search: [{ field: searchableField, value: fieldSearchValue }],
        }),
        cancelTokenRequestParam,
      );
    });
  });

  it('executes a listing request with a search param containing all searchable fields when a string that does not correspond to any searchable field is typed in the search field', () => {
    const { getByPlaceholderText, getByText } = render(<Resources />);

    const searchValue = 'foobar';

    fireEvent.change(getByPlaceholderText(labelResourceName), {
      target: { value: searchValue },
    });

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

    fireEvent.click(getByText(labelSearch));

    expect(mockedAxios.get).toHaveBeenCalledWith(
      getEndpoint({
        search: searchableFields.map((searchableField) => ({
          field: searchableField,
          value: searchValue,
        })),
      }),
      cancelTokenRequestParam,
    );
  });

  it('displays downtime details when the downtime state chip is hovered', async () => {
    const { getByLabelText, getByText } = render(<Resources />);

    const entityInDowntime = entities.find(({ in_downtime }) => in_downtime);

    await wait(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        author_name: 'admin',
        start_time: '2020-02-28T09:16:16',
        end_time: '2020-02-28T09:18:16',
        is_fixed: true,
        comment: 'Set by admin',
      },
    });

    const chipLabel = `${entityInDowntime?.name} ${labelInDowntime}`;

    fireEvent.mouseEnter(getByLabelText(chipLabel));
    fireEvent.mouseOver(getByLabelText(chipLabel));

    await wait(() => expect(mockedAxios.get).toHaveBeenCalled());

    expect(mockedAxios.get).toHaveBeenLastCalledWith(
      entityInDowntime?.downtime_endpoint,
      cancelTokenRequestParam,
    );

    expect(getByText('admin')).toBeInTheDocument();
    expect(getByText('Yes')).toBeInTheDocument();
    expect(getByText('02/28/2020 9:16')).toBeInTheDocument();
    expect(getByText('02/28/2020 9:18')).toBeInTheDocument();
    expect(getByText('Set by admin')).toBeInTheDocument();
  });

  it('displays acknowledgement details when an acknowledged state chip is hovered', async () => {
    const { getByLabelText, getByText } = render(<Resources />);

    const acknowledgedEntity = entities.find(
      ({ acknowledged }) => acknowledged,
    );

    await wait(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    mockedAxios.get.mockResolvedValueOnce({
      data: {
        author_name: 'admin',
        entry_time: '2020-02-28T09:16:16',
        is_persistent: true,
        is_sticky: false,
        comment: 'Set by admin',
      },
    });

    const chipLabel = `${acknowledgedEntity?.name} ${labelAcknowledged}`;

    fireEvent.mouseEnter(getByLabelText(chipLabel));
    fireEvent.mouseOver(getByLabelText(chipLabel));

    await wait(() => expect(mockedAxios.get).toHaveBeenCalled());

    expect(mockedAxios.get).toHaveBeenLastCalledWith(
      acknowledgedEntity?.acknowledgement_endpoint,
      cancelTokenRequestParam,
    );

    expect(getByText('admin')).toBeInTheDocument();
    expect(getByText('02/28/2020 9:16')).toBeInTheDocument();
    expect(getByText('Yes')).toBeInTheDocument();
    expect(getByText('No')).toBeInTheDocument();
    expect(getByText('Set by admin')).toBeInTheDocument();
  });
});
