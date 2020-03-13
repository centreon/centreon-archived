import React from 'react';

import axios from 'axios';
import { render, wait, within, fireEvent } from '@testing-library/react';
import UserEvent from '@testing-library/user-event';

import Resources from '.';
import {
  labelResourceName,
  labelSearch,
  labelInDowntime,
  labelAcknowledged,
  labelTypeOfResource,
  labelHost,
  labelState,
  labelStatus,
  labelOk,
  labelHostGroup,
  labelServiceGroup,
  labelUnhandledProblems,
  labelResourceProblems,
  labelAll,
} from './translatedLabels';
import columns from './columns';
import { Resource } from './models';

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('./columns/icons/Downtime');

interface SearchParam {
  mode: '$or' | '$and';
  fieldPatterns: Array<{ field: string; value: string }>;
}

interface EndpointParams {
  sortBy?: string;
  sortOrder?: string;
  page?: number;
  limit?: number;
  search?: SearchParam;
  states?: Array<string>;
  statuses?: Array<string>;
  resourceTypes?: Array<string>;
  hostGroupsIds?: Array<number>;
  serviceGroupIds?: Array<number>;
}

const defaultStatuses = ['WARNING', 'DOWN', 'CRITICAL', 'UNKNOWN'];
const defaultResourceTypes = [];
const defaultStates = ['unhandled_problems'];

const buildParam = (param): string => JSON.stringify(param);

const getEndpoint = ({
  sortBy = undefined,
  sortOrder = undefined,
  page = 1,
  limit = 10,
  search = undefined,
  states = defaultStates,
  statuses = defaultStatuses,
  resourceTypes = defaultResourceTypes,
  hostGroupsIds = undefined,
  serviceGroupIds = undefined,
}: EndpointParams): string => {
  const baseEndpoint = './api/beta';
  const endpoint = `${baseEndpoint}/monitoring/resources`;
  const sortParam = sortBy ? `&sort_by={"${sortBy}":"${sortOrder}"}` : '';
  const searchParam = search
    ? `&search={"${search.mode}":[${search.fieldPatterns.map(
        ({ field, value }) => `{"${field}":{"$rg":"${value}"}}`,
      )}]}`
    : '';

  const statesParam =
    states.length !== 0 ? `&states=${buildParam(states)}` : '';
  const resourceTypesParam =
    resourceTypes.length !== 0 ? `&types=${buildParam(resourceTypes)}` : '';
  const statusesParam =
    statuses.length !== 0 ? `&statuses=${buildParam(statuses)}` : '';
  const hostGroupsIdsParam = hostGroupsIds
    ? `&hostgroup_ids=${buildParam(hostGroupsIds)}`
    : '';
  const serviceGroupIdsParam = serviceGroupIds
    ? `&servicegroup_ids=${buildParam(serviceGroupIds)}`
    : '';

  return `${endpoint}?page=${page}&limit=${limit}${sortParam}${searchParam}${statesParam}${resourceTypesParam}${statusesParam}${hostGroupsIdsParam}${serviceGroupIdsParam}`;
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
    short_type: 's',
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

const linuxServersHostGroup = {
  id: 0,
  name: 'Linux-servers',
};

const webAccessService = {
  id: 1,
  name: 'Web-access',
};

describe(Resources, () => {
  afterEach(() => {
    mockedAxios.get.mockReset();
  });

  beforeEach(() => {
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
  });

  it('executes a listing request with "Unhandled problems" filter group by default', async () => {
    render(<Resources />);

    await wait(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({}),
        cancelTokenRequestParam,
      ),
    );
  });

  [
    {
      filterGroup: labelResourceProblems,
      criterias: {
        statuses: defaultStatuses,
        states: [],
        resourceTypes: [],
      },
    },
    {
      filterGroup: labelAll,
      criterias: {
        statuses: [],
        states: [],
        resourceTypes: [],
      },
    },
  ].forEach(({ filterGroup, criterias }) => {
    it(`executes a listing request with "${filterGroup}" params when "${filterGroup}" filter group is set`, async () => {
      const { getByText } = render(<Resources />);

      await wait(() => expect(mockedAxios.get).toHaveBeenCalled());

      mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

      // @material-ui Select uses a Popover that needs special handling to update options.
      selectOption(getByText(labelUnhandledProblems), filterGroup);

      await wait(() =>
        expect(mockedAxios.get).toHaveBeenCalledWith(
          getEndpoint({
            resourceTypes: criterias.resourceTypes,
            states: criterias.states,
            statuses: criterias.statuses,
          }),
          cancelTokenRequestParam,
        ),
      );
    });
  });

  [
    {
      filterName: labelTypeOfResource,
      optionToSelect: labelHost,
      endpointParamChanged: { resourceTypes: ['host'] },
    },
    {
      filterName: labelState,
      optionToSelect: labelAcknowledged,
      endpointParamChanged: {
        states: [...defaultStates, 'acknowledged'],
      },
    },
    {
      filterName: labelStatus,
      optionToSelect: labelOk,
      endpointParamChanged: {
        statuses: [...defaultStatuses, 'OK'],
      },
    },
    {
      filterName: labelHostGroup,
      optionToSelect: linuxServersHostGroup.name,
      selectEndpointMockAction: (): void => {
        mockedAxios.get.mockResolvedValueOnce({
          data: { result: [linuxServersHostGroup] },
        });
      },
      endpointParamChanged: {
        hostGroupsIds: [linuxServersHostGroup.id],
      },
    },
    {
      filterName: labelServiceGroup,
      optionToSelect: webAccessService.name,
      selectEndpointMockAction: (): void => {
        mockedAxios.get.mockResolvedValueOnce({
          data: { result: [webAccessService] },
        });
      },
      endpointParamChanged: {
        serviceGroupIds: [webAccessService.id],
      },
    },
  ].forEach(
    ({
      filterName,
      optionToSelect,
      endpointParamChanged,
      selectEndpointMockAction,
    }) => {
      it(`executes a listing request with selected "${filterName}" filter options when it's changed`, async () => {
        const { getAllByText } = render(<Resources />);

        await wait(() => expect(mockedAxios.get).toHaveBeenCalled());

        selectEndpointMockAction?.();
        mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

        const [filterToChange] = getAllByText(filterName);
        fireEvent.click(filterToChange);

        await wait(() => {
          const [selectedOption] = getAllByText(optionToSelect);

          return fireEvent.click(selectedOption);
        });

        expect(mockedAxios.get).toHaveBeenCalledWith(
          getEndpoint({ ...endpointParamChanged }),
          cancelTokenRequestParam,
        );
      });
    },
  );

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
    it(`executes a listing request with an "$and" search param containing ${searchableField} when ${searchableField} is typed in the search field`, () => {
      const { getByPlaceholderText, getByText } = render(<Resources />);

      const fieldSearchValue = 'foobar';

      fireEvent.change(getByPlaceholderText(labelResourceName), {
        target: { value: `${searchableField}:${fieldSearchValue}` },
      });

      mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

      fireEvent.click(getByText(labelSearch));

      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({
          search: {
            mode: '$and',
            fieldPatterns: [
              { field: searchableField, value: fieldSearchValue },
            ],
          },
        }),
        cancelTokenRequestParam,
      );
    });
  });

  it('executes a listing request with an "$or" search param containing all searchable fields when a string that does not correspond to any searchable field is typed in the search field', () => {
    const { getByPlaceholderText, getByText } = render(<Resources />);

    const searchValue = 'foobar';

    fireEvent.change(getByPlaceholderText(labelResourceName), {
      target: { value: searchValue },
    });

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

    fireEvent.click(getByText(labelSearch));

    expect(mockedAxios.get).toHaveBeenCalledWith(
      getEndpoint({
        search: {
          mode: '$or',
          fieldPatterns: searchableFields.map((searchableField) => ({
            field: searchableField,
            value: searchValue,
          })),
        },
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
