import { buildResourcesEndpoint } from '../Listing/api/endpoint';

interface EndpointParams {
  hostGroupIds?: Array<number>;
  limit?: number;
  page?: number;
  resourceTypes?: Array<string>;
  search?: string;
  serviceGroupIds?: Array<number>;
  sort?;
  states?: Array<string>;
  statuses?: Array<string>;
}

const defaultStatuses = ['WARNING', 'DOWN', 'CRITICAL', 'UNKNOWN'];
const defaultResourceTypes = [];
const defaultStates = ['unhandled_problems'];

const searchableFields = [
  'h.name',
  'h.alias',
  'h.address',
  's.description',
  'information',
];

const getListingEndpoint = ({
  page = 1,
  limit = 30,
  sort = { status_severity_code: 'asc' },
  statuses = defaultStatuses,
  states = defaultStates,
  resourceTypes = defaultResourceTypes,
  hostGroupIds = [],
  serviceGroupIds = [],
  search,
}: EndpointParams): string =>
  buildResourcesEndpoint({
    hostGroupIds,
    limit,
    page,
    resourceTypes,
    search: search
      ? {
          regex: {
            fields: [
              'h.name',
              'h.alias',
              'h.address',
              's.description',
              'information',
            ],
            value: search,
          },
        }
      : undefined,
    serviceGroupIds,
    sort,
    states,
    statuses,
  });

const cancelTokenRequestParam = { cancelToken: {} };

const mockAppStateSelector = (useSelector: jest.Mock): void => {
  const appState = {
    intervals: {
      AjaxTimeReloadMonitoring: 60,
    },
  };

  useSelector.mockImplementation((callback) => callback(appState));
};

export {
  mockAppStateSelector,
  getListingEndpoint,
  cancelTokenRequestParam,
  defaultStatuses,
  defaultResourceTypes,
  defaultStates,
  searchableFields,
};
