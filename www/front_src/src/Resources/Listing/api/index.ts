import { getData } from '@centreon/ui';

import { buildResourcesEndpoint } from './endpoint';
import { ResourceListing } from '../../models';

const listResources = (cancelToken) => (
  endpointParams,
): Promise<ResourceListing> =>
  getData<ResourceListing>(cancelToken)(buildResourcesEndpoint(endpointParams));

export { listResources };
