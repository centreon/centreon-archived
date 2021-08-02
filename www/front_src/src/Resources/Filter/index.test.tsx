import * as React from 'react';

import { useSelector } from 'react-redux';
import axios from 'axios';
import {
  fireEvent,
  waitFor,
  render,
  RenderResult,
  act,
} from '@testing-library/react';
import { Simulate } from 'react-dom/test-utils';
import userEvent from '@testing-library/user-event';

import { setUrlQueryParameters, getUrlQueryParameters } from '@centreon/ui';

import {
  labelResource,
  labelHost,
  labelState,
  labelAcknowledged,
  labelStatus,
  labelOk,
  labelHostGroup,
  labelServiceGroup,
  labelSearch,
  labelResourceProblems,
  labelAll,
  labelUnhandledProblems,
  labelNewFilter,
  labelSearchOptions,
} from '../translatedLabels';
import useListing from '../Listing/useListing';
import useActions from '../Actions/useActions';
import Context, { ResourceContext } from '../Context';
import useLoadResources from '../Listing/useLoadResources';
import {
  defaultStates,
  defaultStatuses,
  getCriteriaValue,
  getFilterWithUpdatedCriteria,
  getListingEndpoint,
  mockAppStateSelector,
  searchableFields,
} from '../testUtils';
import useDetails from '../Details/useDetails';

import { allFilter, Filter as FilterModel } from './models';
import useFilter from './useFilter';
import { filterKey } from './storedFilter';
import { defaultSortField, defaultSortOrder } from './Criterias/default';

import Filter from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('react-redux', () => ({
  ...(jest.requireActual('react-redux') as jest.Mocked<unknown>),
  useSelector: jest.fn(),
}));

const linuxServersHostGroup = {
  id: 0,
  name: 'Linux-servers',
};

const webAccessServiceGroup = {
  id: 1,
  name: 'Web-access',
};

type FilterParameter = [
  string,
  string,
  Record<string, unknown>,
  (() => void) | undefined,
];

const filtersParams: Array<FilterParameter> = [
  [labelResource, labelHost, { resourceTypes: ['host'] }, undefined],
  [
    labelState,
    labelAcknowledged,
    {
      states: [...defaultStates, 'acknowledged'],
    },
    undefined,
  ],
  [
    labelStatus,
    labelOk,
    {
      statuses: [...defaultStatuses, 'OK'],
    },
    undefined,
  ],
  [
    labelHostGroup,
    linuxServersHostGroup.name,
    {
      hostGroupIds: [linuxServersHostGroup.id],
    },
    (): void => {
      mockedAxios.get.mockResolvedValueOnce({
        data: {
          meta: {
            limit: 10,
            total: 1,
          },
          result: [linuxServersHostGroup],
        },
      });
    },
  ],
  [
    labelServiceGroup,
    webAccessServiceGroup.name,

    {
      serviceGroupIds: [webAccessServiceGroup.id],
    },
    (): void => {
      mockedAxios.get.mockResolvedValueOnce({
        data: {
          meta: {
            limit: 10,
            total: 1,
          },
          result: [webAccessServiceGroup],
        },
      });
    },
  ],
];

const filter = {
  criterias: [
    {
      name: 'resource_types',
      value: [{ id: 'host', name: labelHost }],
    },
    {
      name: 'states',
      value: [{ id: 'acknowledged', name: labelAcknowledged }],
    },
    { name: 'statuses', value: [{ id: 'OK', name: labelOk }] },
    {
      name: 'host_groups',
      object_type: 'host_groups',
      value: [linuxServersHostGroup],
    },
    {
      name: 'service_groups',
      object_type: 'service_groups',
      value: [webAccessServiceGroup],
    },
    { name: 'search', value: 'Search me' },
    { name: 'sort', value: [defaultSortField, defaultSortOrder] },
  ],
  id: 0,
  name: 'My filter',
};

const FilterWithLoading = (): JSX.Element => {
  useLoadResources();

  return <Filter />;
};

const FilterTest = (): JSX.Element | null => {
  const filterState = useFilter();
  const detailsState = useDetails();
  const listingState = useListing();
  const actionsState = useActions();

  return (
    <Context.Provider
      value={
        {
          ...listingState,
          ...actionsState,
          ...filterState,
          ...detailsState,
        } as ResourceContext
      }
    >
      <FilterWithLoading />
    </Context.Provider>
  );
};

const renderFilter = (): RenderResult => render(<FilterTest />);

const mockedLocalStorageGetItem = jest.fn();
const mockedLocalStorageSetItem = jest.fn();

Storage.prototype.getItem = mockedLocalStorageGetItem;
Storage.prototype.setItem = mockedLocalStorageSetItem;

const cancelTokenRequestParam = { cancelToken: {} };

mockAppStateSelector(useSelector as jest.Mock);

describe(Filter, () => {
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
      .mockResolvedValueOnce({ data: {} });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
    mockedLocalStorageSetItem.mockReset();
    mockedLocalStorageGetItem.mockReset();

    window.history.pushState({}, '', window.location.pathname);
  });

  it('executes a listing request with "Unhandled problems" filter by default', async () => {
    renderFilter();

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getListingEndpoint({}),
        cancelTokenRequestParam,
      ),
    );
  });

  it.each(searchableFields.map((searchableField) => [searchableField]))(
    'executes a listing request with an "$and" search param containing %p when %p is typed in the search field',
    async (searchableField) => {
      const { getByPlaceholderText, getByText, getByLabelText } =
        renderFilter();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalled();
      });

      const search = 'foobar';
      const fieldSearchValue = `${searchableField}:${search}`;

      userEvent.type(getByPlaceholderText(labelSearch), fieldSearchValue);

      mockedAxios.get.mockResolvedValueOnce({ data: {} });

      userEvent.click(
        getByLabelText(labelSearchOptions).firstElementChild as HTMLElement,
      );

      fireEvent.click(getByText(labelSearch));

      const endpoint = getListingEndpoint({ search: fieldSearchValue });

      expect(decodeURIComponent(endpoint)).toContain(
        `search={"$and":[{"${searchableField}":{"$rg":"${search}"}}]}`,
      );

      await waitFor(() =>
        expect(mockedAxios.get).toHaveBeenCalledWith(
          endpoint,
          cancelTokenRequestParam,
        ),
      );
    },
  );

  it('executes a listing request with an "$or" search param containing all searchable fields when a string that does not correspond to any searchable field is typed in the search field', async () => {
    const { getByPlaceholderText, getByText, getByLabelText } = renderFilter();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    const searchValue = 'foobar';

    userEvent.type(getByPlaceholderText(labelSearch), searchValue);

    mockedAxios.get
      .mockResolvedValueOnce({ data: {} })
      .mockResolvedValueOnce({ data: {} });

    userEvent.click(
      getByLabelText(labelSearchOptions).firstElementChild as HTMLElement,
    );

    fireEvent.click(getByText(labelSearch));

    const endpoint = getListingEndpoint({ search: searchValue });

    const searchableFieldExpressions = searchableFields.map(
      (searchableField) => `{"${searchableField}":{"$rg":"${searchValue}"}}`,
    );

    expect(decodeURIComponent(endpoint)).toContain(
      `search={"$or":[${searchableFieldExpressions}]}`,
    );

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        endpoint,
        cancelTokenRequestParam,
      ),
    );

    const searchInput = getByPlaceholderText(labelSearch);

    Simulate.keyDown(searchInput, { key: 'Enter', keyCode: 13, which: 13 });

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        endpoint,
        cancelTokenRequestParam,
      ),
    );
  });

  it.each([
    [
      labelResourceProblems,
      {
        resourceTypes: [],
        states: [],
        statuses: defaultStatuses,
      },
    ],
    [
      labelAll,
      {
        resourceTypes: [],
        states: [],
        statuses: [],
      },
    ],
  ])(
    'executes a listing request with "%p" params when "%p" filter is set',
    async (filterGroup, criterias) => {
      const { getByText } = renderFilter();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledTimes(2);
      });

      mockedAxios.get.mockResolvedValueOnce({ data: {} });

      userEvent.click(getByText(labelUnhandledProblems));

      userEvent.click(getByText(filterGroup));

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenLastCalledWith(
          getListingEndpoint({
            resourceTypes: criterias.resourceTypes,
            states: criterias.states,
            statuses: criterias.statuses,
          }),
          cancelTokenRequestParam,
        );
      });
    },
  );

  it.each(filtersParams)(
    "executes a listing request with current search and selected %p criteria when it's changed",
    async (
      criteriaName,
      optionToSelect,
      endpointParamChanged,
      selectEndpointMockAction,
    ) => {
      const { getByLabelText, getByPlaceholderText, findByText, getByText } =
        renderFilter();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledTimes(2);
      });

      selectEndpointMockAction?.();
      mockedAxios.get.mockResolvedValueOnce({ data: {} });

      const searchValue = 'foobar';
      userEvent.type(getByPlaceholderText(labelSearch), searchValue);

      fireEvent.click(
        getByLabelText(labelSearchOptions).firstElementChild as HTMLElement,
      );

      fireEvent.click(getByText(criteriaName));

      const selectedOption = await findByText(optionToSelect);
      fireEvent.click(selectedOption);

      fireEvent.click(getByText(labelSearch));

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          getListingEndpoint({
            search: searchValue,
            ...endpointParamChanged,
          }),
          cancelTokenRequestParam,
        );
      });
    },
  );

  it('accepts the first relevant autocomplete suggestion when the beginning of a criteria is input and the tab key is pressed', async () => {
    const { getByPlaceholderText } = renderFilter();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledTimes(2);
    });

    userEvent.type(
      getByPlaceholderText(labelSearch),
      '{selectall}{backspace}stat',
    );

    userEvent.tab();

    expect(getByPlaceholderText(labelSearch)).toHaveValue('state:');

    userEvent.type(getByPlaceholderText(labelSearch), 'u');

    userEvent.tab();

    expect(getByPlaceholderText(labelSearch)).toHaveValue(
      'state:unhandled_problems',
    );

    userEvent.type(getByPlaceholderText(labelSearch), ' st');

    userEvent.tab();

    expect(getByPlaceholderText(labelSearch)).toHaveValue(
      'state:unhandled_problems status:',
    );
  });

  describe('Filter storage', () => {
    it('populates filter with values from localStorage if available', async () => {
      mockedLocalStorageGetItem
        .mockReturnValueOnce(JSON.stringify(filter))
        .mockReturnValueOnce(JSON.stringify(true));

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
        .mockResolvedValueOnce({
          data: {
            meta: {
              limit: 30,
              page: 1,
              total: 0,
            },
            result: [],
          },
        });

      const renderResult = renderFilter();

      const {
        getByText,
        queryByLabelText,
        findByPlaceholderText,
        getByLabelText,
      } = renderResult;

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalledTimes(2));

      expect(mockedLocalStorageGetItem).toHaveBeenCalledWith(filterKey);

      expect(queryByLabelText(labelUnhandledProblems)).not.toBeInTheDocument();

      const searchField = await findByPlaceholderText(labelSearch);

      expect(searchField).toHaveValue(
        'resource_type:host state:acknowledged status:OK host_group:0|Linux-servers service_group:1|Web-access Search me',
      );

      userEvent.click(
        getByLabelText(labelSearchOptions).firstElementChild as HTMLElement,
      );

      userEvent.click(getByText(labelResource));
      expect(getByText(labelHost)).toBeInTheDocument();

      userEvent.click(getByText(labelState));
      expect(getByText(labelAcknowledged)).toBeInTheDocument();

      userEvent.click(getByText(labelStatus));
      expect(getByText(labelOk)).toBeInTheDocument();

      act(() => {
        userEvent.click(getByText(labelHostGroup));
      });

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

      expect(getByText(linuxServersHostGroup.name)).toBeInTheDocument();

      act(() => {
        fireEvent.click(getByText(labelServiceGroup));
      });

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

      expect(getByText(webAccessServiceGroup.name)).toBeInTheDocument();
    });

    it('stores filter values in localStorage when updated', async () => {
      const renderResult = renderFilter();

      const { getByText, findByPlaceholderText, findByText } = renderResult;

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalledTimes(2));

      mockedAxios.get.mockResolvedValue({ data: {} });

      const unhandledProblemsOption = await findByText(labelUnhandledProblems);

      userEvent.click(unhandledProblemsOption);

      userEvent.click(getByText(labelAll));

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalledTimes(3));

      expect(mockedLocalStorageSetItem).toHaveBeenCalledWith(
        filterKey,
        JSON.stringify(allFilter),
      );

      const searchField = await findByPlaceholderText(labelSearch);

      userEvent.type(searchField, 'searching...');

      await waitFor(() =>
        expect(mockedLocalStorageSetItem).toHaveBeenCalledWith(
          filterKey,
          JSON.stringify(
            getFilterWithUpdatedCriteria({
              criteriaName: 'search',
              criteriaValue: 'searching...',
              filter: { ...allFilter, id: '', name: labelNewFilter },
            }),
          ),
        ),
      );
    });
  });

  describe('Filter URL query parameters', () => {
    it('sets the filter according to the filter URL query parameter when given', async () => {
      setUrlQueryParameters([
        {
          name: 'filter',
          value: filter,
        },
      ]);

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
        .mockResolvedValueOnce({
          data: {
            meta: {
              limit: 30,
              page: 1,
              total: 0,
            },
            result: [],
          },
        });

      const {
        getByText,
        getByDisplayValue,
        getAllByPlaceholderText,
        getByLabelText,
      } = renderFilter();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledTimes(2);
      });

      expect(getByText('New filter')).toBeInTheDocument();
      expect(
        getByDisplayValue(
          'resource_type:host state:acknowledged status:OK host_group:0|Linux-servers service_group:1|Web-access Search me',
        ),
      ).toBeInTheDocument();

      fireEvent.click(
        getByLabelText(labelSearchOptions).firstElementChild as HTMLElement,
      );

      fireEvent.click(getByText(labelResource));
      expect(getByText(labelHost)).toBeInTheDocument();

      fireEvent.click(getByText(labelState));
      expect(getByText(labelAcknowledged)).toBeInTheDocument();

      fireEvent.click(getByText(labelStatus));
      expect(getByText(labelOk)).toBeInTheDocument();

      fireEvent.click(getByText(labelHostGroup));
      expect(getByText(linuxServersHostGroup.name)).toBeInTheDocument();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalled();
      });

      fireEvent.click(getByText(labelServiceGroup));
      expect(getByText(webAccessServiceGroup.name)).toBeInTheDocument();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalled();
      });

      fireEvent.change(getAllByPlaceholderText(labelSearch)[0], {
        target: { value: 'Search me two' },
      });

      const filterFromUrlQueryParameters = getUrlQueryParameters()
        .filter as FilterModel;

      expect(
        getCriteriaValue({
          filter: filterFromUrlQueryParameters,
          name: 'search',
        }),
      ).toEqual('Search me two');
    });

    it('resets the filter criterias which are not set in the filter URL query parameter when given', async () => {
      mockedLocalStorageGetItem
        .mockReturnValueOnce(JSON.stringify(filter))
        .mockReturnValueOnce(JSON.stringify(true));

      setUrlQueryParameters([
        {
          name: 'filter',
          value: {
            criterias: [{ name: 'search', value: 'Search me' }],
          },
        },
      ]);

      const { getByDisplayValue, queryByText } = renderFilter();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledTimes(2);
      });

      expect(getByDisplayValue('Search me')).toBeInTheDocument();
      expect(queryByText(labelHost)).toBeNull();
      expect(queryByText(labelAcknowledged)).toBeNull();
      expect(queryByText(labelOk)).toBeNull();
      expect(queryByText(linuxServersHostGroup.name)).toBeNull();
      expect(queryByText(webAccessServiceGroup.name)).toBeNull();

      const filterFromUrlQueryParameters = getUrlQueryParameters()
        .filter as FilterModel;

      expect(
        getCriteriaValue({
          filter: filterFromUrlQueryParameters,
          name: 'search',
        }),
      ).toEqual('Search me');
    });
  });
});
