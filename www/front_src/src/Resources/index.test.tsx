import React from 'react';

import { useSelector } from 'react-redux';
import axios from 'axios';
import formatISO from 'date-fns/formatISO';
import {
  render,
  waitFor,
  fireEvent,
  RenderResult,
  within,
} from '@testing-library/react';
import { Simulate } from 'react-dom/test-utils';
import mockDate from 'mockdate';

import {
  partition,
  where,
  contains,
  pipe,
  split,
  head,
  pick,
  last,
  find,
  propEq,
} from 'ramda';

import { ThemeProvider } from '@centreon/ui';

import {
  labelResourceName,
  labelSearch,
  labelSearchHelp,
  labelSearchOnFields,
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
  labelRefresh,
  labelDisableAutorefresh,
  labelEnableAutorefresh,
  labelClearAll,
} from './translatedLabels';
import {
  defaultSortField,
  defaultSortOrder,
  getColumns,
} from './Listing/columns';
import { Resource } from './models';
import {
  acknowledgeEndpoint,
  downtimeEndpoint,
  hostCheckEndpoint,
  serviceCheckEndpoint,
} from './api/endpoint';

import Resources from '.';

import { selectOption } from './testUtils';
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

const searchableFields = ['h.name', 'h.alias', 'h.address', 's.description'];

const linuxServersHostGroup = {
  id: 0,
  name: 'Linux-servers',
};

const webAccessServiceGroup = {
  id: 1,
  name: 'Web-access',
};

const filtersParams = [
  [labelTypeOfResource, labelHost, { resourceTypes: ['host'] }, undefined],
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
      hostGroupsIds: [linuxServersHostGroup.id],
    },
    (): void => {
      mockedAxios.get.mockResolvedValueOnce({
        data: { result: [linuxServersHostGroup] },
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
        data: { result: [webAccessServiceGroup] },
      });
    },
  ],
];

const savedFilter = {
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

const mockedLocalStorageGetItem = jest.fn();
const mockedLocalStorageSetItem = jest.fn();

Storage.prototype.getItem = mockedLocalStorageGetItem;
Storage.prototype.setItem = mockedLocalStorageSetItem;

window.clearInterval = jest.fn();
window.setInterval = jest.fn();

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

  describe('Listing', () => {
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

      await waitFor(() =>
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
        ),
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

    it.each(
      columns
        .filter(({ sortable }) => sortable !== false)
        .map(({ id, label, sortField }) => [id, label, sortField]),
    )(
      'executes a listing request with sort_by param when %p column is clicked',
      async (id, label, sortField) => {
        const { getByLabelText } = renderResources();

        mockedAxios.get.mockResolvedValue({ data: retrievedListing });

        const sortBy = sortField || id;

        fireEvent.click(getByLabelText(`Column ${label}`));

        expect(mockedAxios.get).toHaveBeenLastCalledWith(
          getEndpoint({ sortBy, sortOrder: 'desc' }),
          cancelTokenRequestParam,
        );

        fireEvent.click(getByLabelText(`Column ${label}`));

        await waitFor(() =>
          expect(mockedAxios.get).toHaveBeenLastCalledWith(
            getEndpoint({ sortBy, sortOrder: 'asc' }),
            cancelTokenRequestParam,
          ),
        );
      },
    );

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

      await waitFor(() =>
        expect(mockedAxios.get).toHaveBeenLastCalledWith(
          getEndpoint({ page: 1 }),
          cancelTokenRequestParam,
        ),
      );
    });

    it('executes a listing request with a limit param when the rows per page value is changed', async () => {
      const { getByDisplayValue } = renderResources();

      mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

      fireEvent.change(getByDisplayValue('10'), {
        target: { value: '20' },
      });

      await waitFor(() =>
        expect(mockedAxios.get).toHaveBeenCalledWith(
          getEndpoint({ limit: 20 }),
          cancelTokenRequestParam,
        ),
      );
    });

    it.each(searchableFields.map((searchableField) => [searchableField]))(
      'executes a listing request with an "$and" search param containing %p when %p is typed in the search field',
      async (searchableField) => {
        const { getByPlaceholderText, getByText } = renderResources();

        const fieldSearchValue = 'foobar';

        fireEvent.change(getByPlaceholderText(labelResourceName), {
          target: { value: `${searchableField}:${fieldSearchValue}` },
        });

        mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

        fireEvent.click(getByText(labelSearch));

        await waitFor(() =>
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
          ),
        );
      },
    );

    it('executes a listing request with an "$or" search param containing all searchable fields when a string that does not correspond to any searchable field is typed in the search field', async () => {
      const { getByPlaceholderText, getByText } = renderResources();

      const searchValue = 'foobar';

      fireEvent.change(getByPlaceholderText(labelResourceName), {
        target: { value: searchValue },
      });

      mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

      fireEvent.click(getByText(labelSearch));

      await waitFor(() =>
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
        ),
      );
    });

    it('displays downtime details when the downtime state chip is hovered', async () => {
      const { findByLabelText, getByText } = renderResources();

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
      const { findByLabelText, getByText } = renderResources();

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

  describe('Filter', () => {
    it.each([
      [
        labelResourceProblems,
        {
          statuses: defaultStatuses,
          states: [],
          resourceTypes: [],
        },
      ],
      [
        labelAll,
        {
          statuses: [],
          states: [],
          resourceTypes: [],
        },
      ],
    ])(
      'executes a listing request with "%p" params when "%p" filter group is set',
      async (filterGroup, criterias) => {
        const { getByText } = renderResources();

        mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

        selectOption(getByText(labelUnhandledProblems), filterGroup);

        await waitFor(() => {
          expect(mockedAxios.get).toHaveBeenLastCalledWith(
            getEndpoint({
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
      "executes a listing request with current search and selected %p filter options when it's changed",
      async (
        filterName,
        optionToSelect,
        endpointParamChanged,
        selectEndpointMockAction,
      ) => {
        const {
          getByTitle,
          getByLabelText,
          getByPlaceholderText,
          findByText,
        } = renderResources();

        fireEvent.click(getByLabelText(labelShowCriteriasFilters));

        selectEndpointMockAction?.();
        mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });

        const searchValue = 'foobar';
        fireEvent.change(getByPlaceholderText(labelResourceName), {
          target: { value: searchValue },
        });

        const filterToChange = getByTitle(`${labelOpen} ${filterName}`);
        fireEvent.click(filterToChange);

        const selectedOption = await findByText(optionToSelect);
        fireEvent.click(selectedOption);

        await waitFor(() =>
          expect(mockedAxios.get).toHaveBeenCalledWith(
            getEndpoint({
              search: {
                mode: '$or',
                fieldPatterns: searchableFields.map((searchableField) => ({
                  field: searchableField,
                  value: searchValue,
                })),
              },
              ...endpointParamChanged,
            }),
            cancelTokenRequestParam,
          ),
        );
      },
    );
  });

  describe('Actions', () => {
    const labelAcknowledgedByAdmin = `${labelAcknowledgedBy} admin`;
    const labelDowntimeByAdmin = `${labelDowntimeBy} admin`;

    const mockNow = '2020-01-01';

    beforeEach(() => {
      resolveUserToBeAdmin();
      mockDate.set(mockNow);
    });

    afterEach(() => {
      mockDate.reset();
    });

    it.each([
      [labelAcknowledge, labelAcknowledgedByAdmin, labelAcknowledge],
      [labelDowntime, labelDowntimeByAdmin, labelSetDowntime],
    ])(
      'cannot send a %p request when the corresponding action is fired and the comment field is left empty',
      async (labelAction, labelComment, labelConfirmAction) => {
        const { findByLabelText, getByText, getByRole } = renderResources();

        const firstRow = await findByLabelText(`Select row 1`);

        fireEvent.click(firstRow);

        fireEvent.click(getByText(labelAction));

        const dialog = getByRole('dialog');

        const commentField = await within(dialog).findByText(labelComment);

        fireEvent.change(commentField, {
          target: { value: '' },
        });

        await waitFor(() =>
          expect(
            (last(
              within(dialog).getAllByText(labelConfirmAction),
            ) as HTMLElement).parentElement,
          ).toBeDisabled(),
        );
      },
    );

    it('sends an acknowledgement request when Resources are selected and the Ackowledgement action is clicked and confirmed', async () => {
      const { findByLabelText, getByText, getByRole } = renderResources();

      const hostEntity = find(propEq('type', 'host'), entities);
      const hostEntityRow = await findByLabelText(
        `Select row ${hostEntity?.id}`,
      );

      fireEvent.click(hostEntityRow);

      fireEvent.click(getByText(labelAcknowledge));

      const dialog = getByRole('dialog');

      const notifyCheckbox = await within(dialog).findByLabelText(labelNotify);

      fireEvent.click(notifyCheckbox);
      fireEvent.click(within(dialog).getByLabelText(labelAcknowledgeServices));

      mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
      mockedAxios.all.mockResolvedValueOnce([]);
      mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

      fireEvent.click(
        last(within(dialog).getAllByText(labelAcknowledge)) as HTMLElement,
      );

      await waitFor(() =>
        expect(mockedAxios.post).toHaveBeenCalledWith(
          acknowledgeEndpoint,
          {
            resources: [pick(['id', 'parent', 'type'], hostEntity)],

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
      const { getByText, getByRole, findByLabelText } = renderResources();

      const serviceEntity = find(propEq('type', 'service'), entities);
      const serviceEntityCheckbox = await findByLabelText(
        `Select row ${serviceEntity?.id}`,
      );

      fireEvent.click(serviceEntityCheckbox);

      fireEvent.click(getByText(labelAcknowledge));

      const dialog = getByRole('dialog');

      await within(dialog).findByText(labelAcknowledgedByAdmin);

      expect(within(dialog).queryByText(labelAcknowledgeServices)).toBeNull();
    });

    it('cannot send a downtime request when Downtime action is clicked, type is flexible and duration is empty', async () => {
      const { getByText, findByLabelText, getByRole } = renderResources();

      const firstRow = await findByLabelText(`Select row 1`);

      fireEvent.click(firstRow);

      fireEvent.click(getByText(labelDowntime));

      const dialog = getByRole('dialog');

      await within(dialog).findByText(labelDowntimeByAdmin);

      fireEvent.click(within(dialog).getByLabelText(labelFixed));
      fireEvent.change(within(dialog).getByDisplayValue('3600'), {
        target: { value: '' },
      });

      await waitFor(() =>
        expect(
          within(dialog).getByText(labelSetDowntime).parentElement,
        ).toBeDisabled(),
      );
    });

    it('cannot send a downtime request when Downtime action is clicked and start date is greater than end date', async () => {
      const {
        container,
        getByLabelText,
        getByText,
        findByLabelText,
      } = renderResources();

      const firstRow = await findByLabelText(`Select row 1`);

      fireEvent.click(firstRow);

      fireEvent.click(getByText(labelDowntime));

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

      // set previous day as end date using left arrow key
      fireEvent.click(getByLabelText(labelChangeEndDate));
      fireEvent.keyDown(container, { key: 'ArrowLeft', code: 37 });
      fireEvent.keyDown(container, { key: 'Enter', code: 13 });

      await waitFor(() =>
        expect(getByText(labelSetDowntime).parentElement).toBeDisabled(),
      );
    });

    it('sends a downtime request when Resources are selected and the Downtime action is clicked and confirmed', async () => {
      const { getByText, findByLabelText, getByRole } = renderResources();

      const firstEntity = head(entities);
      const firstEntityCheckbox = await findByLabelText(
        `Select row ${firstEntity?.id}`,
      );

      fireEvent.click(firstEntityCheckbox);

      fireEvent.click(getByText(labelDowntime));

      mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
      mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

      const dialog = getByRole('dialog');

      await within(dialog).findByText(labelDowntimeByAdmin);

      fireEvent.click(within(dialog).getByText(labelSetDowntime));

      const now = new Date();
      const twoHoursMs = 2 * 60 * 60 * 1000;
      const twoHoursFromNow = new Date(Date.now() + twoHoursMs);

      await waitFor(() =>
        expect(mockedAxios.post).toHaveBeenCalledWith(
          downtimeEndpoint,
          {
            resources: [pick(['id', 'type', 'parent'], firstEntity)],
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
      const { findByLabelText, getByText } = renderResources();

      const hostEntity = find(propEq('type', 'host'), entities);
      const hostEntityCheckbox = await findByLabelText(
        `Select row ${hostEntity?.id}`,
      );

      const serviceEntity = find(propEq('type', 'service'), entities);
      const serviceEntityCheckbox = await findByLabelText(
        `Select row ${serviceEntity?.id}`,
      );

      fireEvent.click(hostEntityCheckbox);
      fireEvent.click(serviceEntityCheckbox);

      mockedAxios.get.mockResolvedValueOnce({ data: retrievedListing });
      mockedAxios.all.mockResolvedValueOnce([]);
      mockedAxios.post.mockResolvedValueOnce({}).mockResolvedValueOnce({});

      fireEvent.click(getByText(labelCheck));

      await waitFor(() => {
        expect(mockedAxios.post).toHaveBeenCalledWith(
          hostCheckEndpoint,
          [
            {
              parent_resource_id: null,
              resource_id: hostEntity?.id,
            },
          ],
          cancelTokenRequestParam,
        );

        expect(mockedAxios.post).toHaveBeenCalledWith(
          serviceCheckEndpoint,
          [{ parent_resource_id: null, resource_id: serviceEntity?.id }],
          cancelTokenRequestParam,
        );
      });
    });
  });

  describe('Filter storage', () => {
    it('populates filter with values from localStorage if available', async () => {
      mockedLocalStorageGetItem.mockReturnValue(JSON.stringify(savedFilter));

      const {
        getByText,
        getByDisplayValue,
        queryByLabelText,
      } = renderResources();

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

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

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalledTimes(2));

      expect(mockedLocalStorageSetItem).toHaveBeenCalledWith(
        filterStorageKey,
        JSON.stringify(allFilter),
      );

      fireEvent.change(getByPlaceholderText(labelResourceName), {
        target: { value: 'searching...' },
      });

      await waitFor(() =>
        expect(mockedLocalStorageSetItem).toHaveBeenCalledWith(
          filterStorageKey,
          JSON.stringify({
            ...allFilter,
            search: 'searching...',
          }),
        ),
      );
    });

    it('clears all filters and set filter group to all when the clear all button is clicked', async () => {
      mockedLocalStorageGetItem.mockReturnValue(JSON.stringify(savedFilter));

      mockedAxios.get.mockResolvedValue({ data: retrievedListing });

      const {
        getByText,
        queryByDisplayValue,
        getByLabelText,
        queryByText,
      } = renderResources();

      fireEvent.click(getByLabelText(labelShowCriteriasFilters));

      fireEvent.click(getByText(labelClearAll));

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalledTimes(2));

      expect(getByText(labelAll)).toBeInTheDocument();
      expect(queryByDisplayValue('searching...')).toBeNull();
      expect(queryByText(labelHost)).toBeNull();
      expect(queryByText(labelAcknowledged)).toBeNull();
      expect(queryByText(labelOk)).toBeNull();
      expect(queryByText(linuxServersHostGroup.name)).toBeNull();
      expect(queryByText(webAccessServiceGroup.name)).toBeNull();
    });

    it('leaves search help tooltip visible when the search input is filled', async () => {
      const {
        getByLabelText,
        getByText,
        getByPlaceholderText,
      } = renderResources();

      await waitFor(() => expect(mockedAxios.get).toHaveBeenCalled());

      fireEvent.click(getByLabelText(labelSearchHelp));

      expect(
        getByText(labelSearchOnFields, { exact: false }),
      ).toBeInTheDocument();

      const searchInput = getByPlaceholderText(labelResourceName);

      fireEvent.change(searchInput, {
        target: { value: 'foobar' },
      });

      expect(
        getByText(labelSearchOnFields, { exact: false }),
      ).toBeInTheDocument();
    });
  });
});
