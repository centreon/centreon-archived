import { buildResourcesEndpoint } from '../Listing/api/endpoint';

interface EndpointParams {
  sort?;
  page?: number;
  limit?: number;
  search?: string;
  states?: Array<string>;
  statuses?: Array<string>;
  resourceTypes?: Array<string>;
  hostGroupIds?: Array<number>;
  serviceGroupIds?: Array<number>;
}

const defaultStatuses = ['WARNING', 'DOWN', 'CRITICAL', 'UNKNOWN'];
const defaultResourceTypes = [];
const defaultStates = ['unhandled_problems'];

const getListingEndpoint = ({
  page = 1,
  limit = 30,
  sort = { status_severity_code: 'asc' },
  statuses = defaultStatuses,
  states = defaultStates,
  resourceTypes = defaultResourceTypes,
  hostGroupIds = [],
  serviceGroupIds = [],
  search = '',
}: EndpointParams): string =>
  buildResourcesEndpoint({
    page,
    limit,
    sort,
    statuses,
    states,
    search,
    resourceTypes,
    hostGroupIds,
    serviceGroupIds,
  });

const cancelTokenRequestParam = { cancelToken: {} };

const mockAppStateSelector = (useSelector): void => {
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
};
