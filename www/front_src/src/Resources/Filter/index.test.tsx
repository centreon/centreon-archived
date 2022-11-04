/* eslint-disable react/jsx-no-constructed-context-values */
import axios from 'axios';
import { Simulate } from 'react-dom/test-utils';
import userEvent from '@testing-library/user-event';
import { Provider } from 'jotai';

import {
  setUrlQueryParameters,
  getUrlQueryParameters,
  fireEvent,
  waitFor,
  render,
  RenderResult,
  act,
  TestQueryProvider,
  resetMocks,
  mockResponseOnce,
} from '@centreon/ui';
import { refreshIntervalAtom, userAtom } from '@centreon/ui-context';

import {
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
  labelStatusType,
  labelSoft,
  labelType,
} from '../translatedLabels';
import useListing from '../Listing/useListing';
import useActions from '../testUtils/useActions';
import Context, { ResourceContext } from '../testUtils/Context';
import useLoadResources from '../Listing/useLoadResources';
import {
  defaultStates,
  defaultStatuses,
  getCriteriaValue,
  getFilterWithUpdatedCriteria,
  getListingEndpoint,
  searchableFields,
  defaultStateTypes,
} from '../testUtils';
import useLoadDetails from '../testUtils/useLoadDetails';
import useDetails from '../Details/useDetails';

import { allFilter, Filter as FilterModel } from './models';
import useFilter from './useFilter';
import { filterKey } from './filterAtoms';
import { defaultSortField, defaultSortOrder } from './Criterias/default';
import { buildHostGroupsEndpoint } from './api/endpoint';

import Filter from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const mockUser = {
  isExportButtonEnabled: true,
  locale: 'en',
  timezone: 'Europe/Paris',
};
const mockRefreshInterval = 60;

jest.useFakeTimers();

const linuxServersHostGroup = {
  id: 0,
  name: 'Linux-servers',
};

const webAccessServiceGroup = {
  id: 0,
  name: 'Web-access',
};

type FilterParameter = [
  string,
  string,
  Record<string, unknown>,
  (() => void) | undefined,
];

const filterParams: Array<FilterParameter> = [
  [labelType, labelHost, { resourceTypes: ['host'] }, undefined],
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
    labelStatusType,
    labelSoft,
    {
      statusTypes: [...defaultStateTypes, 'soft'],
    },
    undefined,
  ],
  [
    labelHostGroup,
    linuxServersHostGroup.name,
    {
      hostGroups: [linuxServersHostGroup.name],
    },
    (): void => {
      mockResponseOnce({
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
      serviceGroups: [webAccessServiceGroup.name],
    },
    (): void => {
      mockResponseOnce({
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

const hostGroupsData = {
  meta: {
    limit: 5,
    page: 1,
    search: {},
    sort_by: {},
    total: 3,
  },
  result: [
    {
      id: 72,
      name: 'ESX-Servers',
    },
    {
      id: 60,
      name: 'Firewall',
    },
    {
      id: 70,
      name: 'IpCam-Hardware',
    },
  ],
};

const FilterWithLoading = (): JSX.Element => {
  useLoadResources();

  return <Filter />;
};

const FilterTest = (): JSX.Element | null => {
  useFilter();
  const detailsState = useLoadDetails();
  const listingState = useListing();
  const actionsState = useActions();

  useDetails();

  return (
    <TestQueryProvider>
      <Context.Provider
        value={
          {
            ...listingState,
            ...actionsState,
            ...detailsState,
          } as ResourceContext
        }
      >
        <FilterWithLoading />
      </Context.Provider>
    </TestQueryProvider>
  );
};

const FilterTestWitJotai = (): JSX.Element => (
  <Provider
    initialValues={[
      [userAtom, mockUser],
      [refreshIntervalAtom, mockRefreshInterval],
    ]}
  >
    <FilterTest />
  </Provider>
);

const renderFilter = (): RenderResult => render(<FilterTestWitJotai />);

const mockedLocalStorageGetItem = jest.fn();
const mockedLocalStorageSetItem = jest.fn();

Storage.prototype.getItem = mockedLocalStorageGetItem;
Storage.prototype.setItem = mockedLocalStorageSetItem;

const cancelTokenRequestParam = { cancelToken: {} };
const dynamicCriteriaRequests = (): void => {
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
    .mockResolvedValueOnce({ data: {} })
    .mockResolvedValue({ data: hostGroupsData });
};

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
      .mockResolvedValue({ data: {} });
  });

  afterEach(() => {
    mockedAxios.get.mockReset();
    mockedLocalStorageSetItem.mockReset();
    mockedLocalStorageGetItem.mockReset();
    resetMocks();

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
        statusTypes: [],
        statuses: defaultStatuses,
      },
    ],
    [
      labelAll,
      {
        resourceTypes: [],
        states: [],
        statusTypes: [],
        statuses: [],
      },
    ],
  ])(
    'executes a listing request with "%p" parameters when "%p" filter is set',
    async (filterGroup, criterias) => {
      const { getByText } = renderFilter();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledTimes(2);
      });

      mockedAxios.get.mockResolvedValueOnce({ data: {} });

      await waitFor(() =>
        expect(getByText(labelUnhandledProblems)).toBeInTheDocument(),
      );

      userEvent.click(getByText(labelUnhandledProblems));

      userEvent.click(getByText(filterGroup));

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenLastCalledWith(
          getListingEndpoint(criterias),
          cancelTokenRequestParam,
        );
      });
    },
  );

  it.each(filterParams)(
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

  it.each([
    ['tab', (): void => userEvent.tab()],
    [
      'enter',
      (): void => {
        userEvent.keyboard('{Enter}');
      },
    ],
  ])(
    'accepts the selected autocomplete suggestion when the beginning of a dynamic criteria is input and the %p key is pressed',
    async (_, keyboardAction) => {
      dynamicCriteriaRequests();
      const { getByPlaceholderText, getByText } = renderFilter();

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledTimes(2);
      });

      userEvent.type(
        getByPlaceholderText(labelSearch),
        '{selectall}{backspace}host',
      );

      keyboardAction();

      expect(getByPlaceholderText(labelSearch)).toHaveValue('host_group:');

      userEvent.type(getByPlaceholderText(labelSearch), 'ESX');

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          buildHostGroupsEndpoint({
            limit: 5,
            page: 1,
            search: {
              conditions: [],
              regex: {
                fields: ['name'],
                value: 'ESX',
              },
            },
          }),
          cancelTokenRequestParam,
        );
      });

      await waitFor(() => expect(getByText('ESX-Servers')).toBeInTheDocument());

      keyboardAction();

      await waitFor(() =>
        expect(getByPlaceholderText(labelSearch)).toHaveValue(
          'host_group:ESX-Servers',
        ),
      );

      userEvent.type(getByPlaceholderText(labelSearch), ',');

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalledWith(
          buildHostGroupsEndpoint({
            limit: 5,
            page: 1,
            search: {
              conditions: [
                {
                  field: 'name',
                  values: { $ni: ['ESX-Servers'] },
                },
              ],
              regex: {
                fields: ['name'],
                value: '',
              },
            },
          }),
          cancelTokenRequestParam,
        );
      });

      await waitFor(() => expect(getByText('Firewall')).toBeInTheDocument());

      userEvent.keyboard('{ArrowDown}');

      keyboardAction();

      await waitFor(() =>
        expect(getByPlaceholderText(labelSearch)).toHaveValue(
          'host_group:ESX-Servers,Firewall',
        ),
      );
    },
  );

  it('accepts the selected autocomplete suggestion when the beginning of a criteria is input and the tab key is pressed', async () => {
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

    expect(getByPlaceholderText(labelSearch)).toHaveValue('state:unhandled');

    userEvent.type(getByPlaceholderText(labelSearch), ' st');

    userEvent.tab();

    expect(getByPlaceholderText(labelSearch)).toHaveValue(
      'state:unhandled status:',
    );

    userEvent.type(getByPlaceholderText(labelSearch), ' type:');

    userEvent.keyboard('{ArrowDown}');

    userEvent.tab();

    expect(getByPlaceholderText(labelSearch)).toHaveValue(
      'state:unhandled status: type:service',
    );
  });

  describe('Filter storage', () => {
    it('populates filter with values from localStorage if available', async () => {
      mockedLocalStorageGetItem
        .mockReturnValueOnce(JSON.stringify(filter))
        .mockReturnValueOnce(JSON.stringify(true));

      mockResponseOnce({
        data: {
          meta: {
            limit: 30,
            page: 1,
            total: 0,
          },
          result: [linuxServersHostGroup],
        },
      });

      mockResponseOnce({
        data: {
          meta: {
            limit: 30,
            page: 1,
            total: 0,
          },
          result: [webAccessServiceGroup],
        },
      });

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
        findByText,
      } = renderResult;

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalledTimes(3));

      await waitFor(() => {
        expect(mockedLocalStorageGetItem).toHaveBeenCalledWith(filterKey);
      });

      expect(queryByLabelText(labelUnhandledProblems)).not.toBeInTheDocument();

      const searchField = await findByPlaceholderText(labelSearch);

      expect(searchField).toHaveValue(
        'type:host state:acknowledged status:ok host_group:Linux-servers service_group:Web-access Search me',
      );

      userEvent.click(
        getByLabelText(labelSearchOptions).firstElementChild as HTMLElement,
      );

      userEvent.click(getByText(labelType));
      expect(getByText(labelHost)).toBeInTheDocument();

      userEvent.click(getByText(labelState));
      expect(getByText(labelAcknowledged)).toBeInTheDocument();

      userEvent.click(getByText(labelStatus));
      expect(getByText(labelOk)).toBeInTheDocument();

      userEvent.click(getByText(labelHostGroup));

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

      const linuxServerOption = await findByText(linuxServersHostGroup.name);

      expect(linuxServerOption).toBeInTheDocument();

      act(() => {
        fireEvent.click(getByText(labelServiceGroup));
      });

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

      const webAccessServiceGroupOption = await findByText(
        webAccessServiceGroup.name,
      );

      expect(webAccessServiceGroupOption).toBeInTheDocument();
    });

    it('stores filter values in localStorage when updated', async () => {
      const renderResult = renderFilter();

      const { getByText, findByPlaceholderText, findByText } = renderResult;

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalledTimes(2));

      mockedAxios.get.mockResolvedValue({ data: {} });

      const newFilterOption = await findByText(labelNewFilter);

      userEvent.click(newFilterOption);

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

      mockResponseOnce({
        data: {
          meta: {
            limit: 30,
            page: 1,
            total: 0,
          },
          result: [linuxServersHostGroup],
        },
      });

      mockResponseOnce({
        data: {
          meta: {
            limit: 30,
            page: 1,
            total: 0,
          },
          result: [webAccessServiceGroup],
        },
      });

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

      await waitFor(() => {
        expect(getByText('New filter')).toBeInTheDocument();
      });
      expect(
        getByDisplayValue(
          'type:host state:acknowledged status:ok host_group:Linux-servers service_group:Web-access Search me',
        ),
      ).toBeInTheDocument();

      fireEvent.click(
        getByLabelText(labelSearchOptions).firstElementChild as HTMLElement,
      );

      fireEvent.click(getByText(labelType));
      expect(getByText(labelHost)).toBeInTheDocument();

      fireEvent.click(getByText(labelState));
      expect(getByText(labelAcknowledged)).toBeInTheDocument();

      fireEvent.click(getByText(labelStatus));
      expect(getByText(labelOk)).toBeInTheDocument();

      userEvent.click(getByText(labelHostGroup));

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

      await waitFor(() =>
        expect(getByText(linuxServersHostGroup.name)).toBeInTheDocument(),
      );

      await waitFor(() => {
        expect(mockedAxios.get).toHaveBeenCalled();
      });

      userEvent.click(getByText(labelServiceGroup));

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

      await waitFor(() =>
        expect(getByText(webAccessServiceGroup.name)).toBeInTheDocument(),
      );

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
        expect(mockedAxios.get).toHaveBeenCalledTimes(3);
      });

      await waitFor(() =>
        expect(getByDisplayValue('Search me')).toBeInTheDocument(),
      );
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
