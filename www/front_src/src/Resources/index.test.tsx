import React from 'react';

import { useSelector } from 'react-redux';
import axios from 'axios';
import formatISO from 'date-fns/formatISO';
import {
  render,
  waitFor,
  fireEvent,
  RenderResult,
} from '@testing-library/react';
import { Simulate } from 'react-dom/test-utils';

import {
  partition,
  where,
  contains,
  pipe,
  split,
  head,
  map,
  pick,
  last,
} from 'ramda';

import { ThemeProvider } from '@centreon/ui';

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
  labelAcknowledge,
  labelAcknowledgedBy,
  labelAcknowledgeServices,
  labelDowntime,
  labelSetDowntime,
  labelDowntimeBy,
  labelCheck,
  labelFixed,
  labelNotify,
  labelOpen,
  labelShowCriteriasFilters,
  labelChangeEndDate,
  labelEndDate,
  labelEndTime,
  labelStartDate,
  labelStartTime,
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
} from './translatedLabels';
import { defaultSortField, defaultSortOrder, getColumns } from './columns';
import { Resource } from './models';
import {
  acknowledgeEndpoint,
  downtimeEndpoint,
  hostCheckEndpoint,
  serviceCheckEndpoint,
} from './api/endpoint';

import Resources from '.';

import { selectOption } from './test';
import { allFilter } from './Filter/models';

const columns = getColumns({ onAcknowledge: jest.fn() });

const mockedAxios = axios as jest.Mocked<typeof axios>;

jest.mock('react-redux', () => ({
  useSelector: jest.fn(),
}));

jest.mock('./icons/Downtime');

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

const appState = {
  intervals: {
    AjaxTimeReloadMonitoring: 60,
  },
};

const filterStorageKey = 'centreon-events-filter';

const buildParam = (param): string => JSON.stringify(param);

const getEndpoint = ({
  sortBy = defaultSortField,
  sortOrder = defaultSortOrder,
  page = 1,
  limit = 30,
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

  return [
    endpoint,
    '?page=',
    page,
    '&limit=',
    limit,
    sortParam,
    searchParam,
    statesParam,
    resourceTypesParam,
    statusesParam,
    hostGroupsIdsParam,
    serviceGroupIdsParam,
  ].join('');
};

const cancelTokenRequestParam = { cancelToken: {} };

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

const searchableFields = ['h.name', 'h.alias', 'h.address', 's.description'];

const linuxServersHostGroup = {
  id: 0,
  name: 'Linux-servers',
};

const webAccessServiceGroup = {
  id: 1,
  name: 'Web-access',
};

const hostResources = entities.filter(({ type }) => type === 'host');
const serviceResources = entities.filter(({ type }) => type === 'service');

const mockedLocalStorageGetItem = jest.fn();
const mockedLocalStorageSetItem = jest.fn();

Storage.prototype.getItem = mockedLocalStorageGetItem;
Storage.prototype.setItem = mockedLocalStorageSetItem;

const renderResources = (): RenderResult =>
  render(
    <ThemeProvider>
      <Resources />
    </ThemeProvider>,
  );

describe(Resources, () => {
  afterEach(() => {
    useSelector.mockClear();
    mockedAxios.get.mockReset();
    mockedAxios.post.mockReset();
    mockedAxios.all.mockReset();
    mockedLocalStorageSetItem.mockReset();
    mockedLocalStorageGetItem.mockReset();
  });

  beforeEach(() => {
    useSelector.mockImplementation((callback) => {
      return callback(appState);
    });
    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
  });

  const resolveUserToBeAdmin = (): void => {
    mockedAxios.get.mockResolvedValueOnce({
      data: {
        username: 'admin',
      },
    });
  };

  it('displays first part of information when multiple (split by \n) are available', async () => {
    const { getByText, queryByText } = renderResources();

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

  it('expands criterias filters when the expand icon is clicked', async () => {
    const { getByLabelText, queryByText } = renderResources();

    await waitFor(() => {
      expect(queryByText(labelTypeOfResource)).not.toBeVisible();
    });

    fireEvent.click(getByLabelText(labelShowCriteriasFilters));

    await waitFor(() => {
      expect(queryByText(labelTypeOfResource)).toBeVisible();
    });
  });

  it('executes a listing request with "Unhandled problems" filter group by default', async () => {
    renderResources();

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({}),
        cancelTokenRequestParam,
      ),
    );
  });

  it('executes a listing request when a search is typed and enter key is pressed', async () => {
    const { getByPlaceholderText } = render(<Resources />);

    const fieldSearchValue = 'foobar';

    const searchInput = getByPlaceholderText(labelResourceName);

    fireEvent.change(searchInput, {
      target: { value: fieldSearchValue },
    });

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

    Simulate.keyDown(searchInput, { key: 'Enter', keyCode: 13, which: 13 });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      getEndpoint({
        search: {
          mode: '$or',
          fieldPatterns: searchableFields.map((searchableField) => ({
            field: searchableField,
            value: fieldSearchValue,
          })),
        },
      }),
      cancelTokenRequestParam,
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
      const { getByText } = renderResources();

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

      mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

      // @material-ui Select uses a Popover that needs special handling to update options.
      selectOption(getByText(labelUnhandledProblems), filterGroup);

      await waitFor(() =>
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
      optionToSelect: webAccessServiceGroup.name,
      selectEndpointMockAction: (): void => {
        mockedAxios.get.mockResolvedValueOnce({
          data: { result: [webAccessServiceGroup] },
        });
      },
      endpointParamChanged: {
        serviceGroupIds: [webAccessServiceGroup.id],
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
        const { getAllByText, getByTitle } = renderResources();

        await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

        selectEndpointMockAction?.();
        mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

        const filterToChange = getByTitle(`${labelOpen} ${filterName}`);
        fireEvent.click(filterToChange);

        await waitFor(() => {
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
    const { getByLabelText } = renderResources();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalled();
    });

    columns
      .filter(({ sortable }) => sortable !== false)
      .forEach(({ id, label, sortField }) => {
        const sortBy = sortField || id;

        mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

        fireEvent.click(getByLabelText(`Column ${label}`));

        expect(mockedAxios.get).toHaveBeenCalledWith(
          getEndpoint({ sortBy, sortOrder: 'desc' }),
          cancelTokenRequestParam,
        );

        mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

        fireEvent.click(getByLabelText(`Column ${label}`));

        expect(mockedAxios.get).toHaveBeenCalledWith(
          getEndpoint({ sortBy, sortOrder: 'asc' }),
          cancelTokenRequestParam,
        );
      });
  });

  it('executes a listing request with an updated page param when a change page action is clicked', async () => {
    const { getByLabelText } = renderResources();

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
    const { getByDisplayValue } = renderResources();

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
      const { getByPlaceholderText, getByText } = renderResources();

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
    const { getByPlaceholderText, getByText } = renderResources();

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
    const { getByLabelText, getByText } = renderResources();

    const entityInDowntime = entities.find(({ in_downtime }) => in_downtime);

    await waitFor(() => {
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
    const { getByLabelText, getByText } = renderResources();

    const acknowledgedEntity = entities.find(
      ({ acknowledged }) => acknowledged,
    );

    await waitFor(() => {
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

  const selectAllResources = async ({ getByLabelText }): Promise<void> => {
    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    resolveUserToBeAdmin();

    fireEvent.click(getByLabelText('Select all'));
  };

  /**
   * Acknowledgement dialog
   */

  const selectAllResourcesAndPrepareToAcknowledge = async ({
    getByLabelText,
    getByText,
  }): Promise<void> => {
    await selectAllResources({ getByLabelText });

    fireEvent.click(getByText(labelAcknowledge));

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());
  };

  const labelAcknowledgedByAdmin = `${labelAcknowledgedBy} admin`;

  it('cannot send an acknowledgement request when Acknowledgement action is clicked and comment is empty', async () => {
    const { getByLabelText, getByText, getAllByText } = renderResources();

    await selectAllResourcesAndPrepareToAcknowledge({
      getByLabelText,
      getByText,
    });

    fireEvent.change(getByText(labelAcknowledgedByAdmin), {
      target: { value: '' },
    });

    await waitFor(() =>
      expect(
        (last(getAllByText(labelAcknowledge)) as HTMLElement).parentElement,
      ).toBeDisabled(),
    );
  });

  it('sends an acknowledgement request when Resources are selected and the Ackowledgement action is clicked and confirmed', async () => {
    const { getByLabelText, getByText, getAllByText } = renderResources();

    await selectAllResourcesAndPrepareToAcknowledge({
      getByLabelText,
      getByText,
    });

    fireEvent.click(getByLabelText(labelNotify));
    fireEvent.click(getByLabelText(labelAcknowledgeServices));

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
    mockedAxios.all.mockResolvedValueOnce([]);
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    fireEvent.click(last(getAllByText(labelAcknowledge)) as HTMLElement);

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalled();
    });

    expect(mockedAxios.post).toHaveBeenCalledWith(
      acknowledgeEndpoint,
      {
        resources: map(pick(['id', 'parent', 'type']), retrievedListing.result),
        acknowledgement: {
          comment: labelAcknowledgedByAdmin,
          is_notify_contacts: true,
          with_services: true,
        },
      },
      expect.anything(),
    );
  });

  it('does not display the "Acknowledge services attached to host" checkbox when only services are selected and the Acknowledge action is clicked', async () => {
    const { getByLabelText, getByText, queryByText } = renderResources();

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    resolveUserToBeAdmin();

    serviceResources.map(({ id }) =>
      fireEvent.click(getByLabelText(`Select row ${id}`)),
    );

    fireEvent.click(getByText(labelAcknowledge));

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

    expect(getByText(labelAcknowledgedByAdmin)).toBeInTheDocument();
    expect(queryByText(labelAcknowledgeServices)).toBeNull();
  });

  /**
   * Downtime dialog
   */

  const selectAllResourcesAndPrepareToSetDowntime = async ({
    getByLabelText,
    getByText,
  }): Promise<void> => {
    await selectAllResources({ getByLabelText });

    fireEvent.click(getByText(labelDowntime));

    await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());
  };

  const labelDowntimeByAdmin = `${labelDowntimeBy} admin`;

  it('cannot send a downtime request when Downtime action is clicked and comment is empty', async () => {
    const { getByLabelText, getByText } = renderResources();

    await selectAllResourcesAndPrepareToSetDowntime({
      getByLabelText,
      getByText,
    });

    fireEvent.change(getByText(labelDowntimeByAdmin), {
      target: { value: '' },
    });

    await waitFor(() =>
      expect(getByText(labelSetDowntime).parentElement).toBeDisabled(),
    );
  });

  it('cannot send a downtime request when Downtime action is clicked, type is flexible and duration is empty', async () => {
    const { getByLabelText, getByText, getByDisplayValue } = renderResources();

    await selectAllResourcesAndPrepareToSetDowntime({
      getByLabelText,
      getByText,
    });

    fireEvent.click(getByLabelText(labelFixed));
    fireEvent.change(getByDisplayValue('3600'), {
      target: { value: '' },
    });

    await waitFor(() =>
      expect(getByText(labelSetDowntime).parentElement).toBeDisabled(),
    );
  });

  it('cannot send a downtime request when Downtime action is clicked and start date is greater than end date', async () => {
    const { container, getByLabelText, getByText } = renderResources();

    await selectAllResourcesAndPrepareToSetDowntime({
      getByLabelText,
      getByText,
    });

    // set previous day as end date using left arrow key
    fireEvent.click(getByLabelText(labelChangeEndDate));
    fireEvent.keyDown(container, { key: 'ArrowLeft', code: 37 });
    fireEvent.keyDown(container, { key: 'Enter', code: 13 });

    await waitFor(() =>
      expect(getByText(labelSetDowntime).parentElement).toBeDisabled(),
    );
  });

  it('sends a downtime request when Resources are selected and the Downtime action is clicked and confirmed', async () => {
    const { getByLabelText, getByText } = renderResources();

    await selectAllResourcesAndPrepareToSetDowntime({
      getByLabelText,
      getByText,
    });

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
    mockedAxios.all.mockResolvedValueOnce([]);
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    const startDateTime = new Date(
      `${getByLabelText(labelStartTime)?.querySelector('input')?.value || ''} ${
        getByLabelText(labelStartDate)?.querySelector('input')?.value || ''
      }`,
    );
    const endDateTime = new Date(
      `${getByLabelText(labelEndTime)?.querySelector('input')?.value || ''} ${
        getByLabelText(labelEndDate)?.querySelector('input')?.value || ''
      }`,
    );

    fireEvent.click(getByText(labelSetDowntime));

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalled();
    });

    expect(mockedAxios.post).toHaveBeenCalledWith(
      downtimeEndpoint,
      {
        resources: map(pick(['id', 'type', 'parent']), retrievedListing.result),
        downtime: {
          comment: labelDowntimeByAdmin,
          duration: 3600,
          end_time: formatISO(endDateTime),
          is_fixed: true,
          start_time: formatISO(startDateTime),
          with_services: true,
        },
      },
      expect.anything(),
    );
  });

  it('sends a check request when Resources are selected and the Check action is clicked', async () => {
    const { getByLabelText, getByText } = renderResources();

    await selectAllResources({ getByLabelText });

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
    mockedAxios.all.mockResolvedValueOnce([]);
    mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

    fireEvent.click(getByText(labelCheck));

    await waitFor(() => {
      expect(mockedAxios.all).toHaveBeenCalled();
      expect(mockedAxios.post).toHaveBeenCalled();
    });

    expect(mockedAxios.post).toHaveBeenCalledWith(
      hostCheckEndpoint,
      hostResources.map(({ id }) => ({
        parent_resource_id: null,
        resource_id: id,
      })),
      expect.anything(),
    );

    expect(mockedAxios.post).toHaveBeenCalledWith(
      serviceCheckEndpoint,
      serviceResources.map(({ id, parent }) => ({
        parent_resource_id: parent?.id || null,
        resource_id: id,
      })),
      expect.anything(),
    );
  });

  it('executes a listing request when refresh button is clicked', async () => {
    const { getByLabelText } = renderResources();

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({}),
        cancelTokenRequestParam,
      ),
    );

    mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

    const refreshButton = getByLabelText(labelRefresh);

    await waitFor(() => expect(refreshButton).toBeEnabled());

    fireEvent.click(refreshButton);

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({}),
        cancelTokenRequestParam,
      ),
    );
  });

  it('swaps autorefresh icon when the icon is clicked', async () => {
    const { getByLabelText } = renderResources();

    await waitFor(() =>
      expect(mockedAxios.get).toHaveBeenCalledWith(
        getEndpoint({}),
        cancelTokenRequestParam,
      ),
    );

    fireEvent.click(getByLabelText(labelDisableAutorefresh));

    expect(getByLabelText(labelEnableAutorefresh)).toBeTruthy();

    fireEvent.click(getByLabelText(labelEnableAutorefresh));

    expect(getByLabelText(labelDisableAutorefresh)).toBeTruthy();
  });

  it('populates filter with values from localStorage if available', () => {
    const filter = {
      id: '',
      name: '',
      search: 'searching...',
      criterias: {
        resourceTypes: [{ id: 'host', name: labelHost }],
        states: [{ id: 'acknowledged', name: labelAcknowledged }],
        statuses: [{ id: 'OK', name: labelOk }],
        hostGroups: [linuxServersHostGroup],
        serviceGroups: [webAccessServiceGroup],
      },
    };

    mockedLocalStorageGetItem.mockReturnValue(JSON.stringify(filter));

    const {
      getByText,
      getByDisplayValue,
      queryByLabelText,
    } = renderResources();

    expect(mockedLocalStorageGetItem).toHaveBeenCalledWith(filterStorageKey);
    expect(queryByLabelText(labelUnhandledProblems)).not.toBeInTheDocument();
    expect(getByDisplayValue('searching...')).toBeInTheDocument();
    expect(getByText(labelHost)).toBeInTheDocument();
    expect(getByText(labelAcknowledged)).toBeInTheDocument();
    expect(getByText(labelOk)).toBeInTheDocument();
    expect(getByText(linuxServersHostGroup.name)).toBeInTheDocument();
    expect(getByText(webAccessServiceGroup.name)).toBeInTheDocument();
  });

  it('stores filter values in localStorage when updated', async () => {
    const { getByText, getByPlaceholderText } = renderResources();

    mockedAxios.get.mockResolvedValue({ data: retrievedListing });

    selectOption(getByText(labelUnhandledProblems), labelAll);

    expect(mockedLocalStorageSetItem).toHaveBeenCalledWith(
      filterStorageKey,
      JSON.stringify(allFilter),
    );

    fireEvent.change(getByPlaceholderText(labelResourceName), {
      target: { value: 'searching...' },
    });

    expect(mockedLocalStorageSetItem).toHaveBeenCalledWith(
      filterStorageKey,
      JSON.stringify({
        ...allFilter,
        search: 'searching...',
      }),
    );
  });
});
