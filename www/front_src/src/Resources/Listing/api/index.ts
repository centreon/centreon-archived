import { getData } from '@centreon/ui';

import { ResourceListing } from '../../models';

import { buildResourcesEndpoint } from './endpoint';

const listResources = (cancelToken) => (
  endpointParams,
): Promise<ResourceListing> =>
  getData<ResourceListing>(cancelToken)(buildResourcesEndpoint(endpointParams));

export { listResources };
