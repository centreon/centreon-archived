import { CancelToken } from 'axios';

import { getData } from '@centreon/ui';

import { buildResourcesEndpoint } from './endpoint';
import { ListResourcesEndpointParams, ResourceListing } from '../../models';

const listResources = (cancelToken: CancelToken) => (
  endpointParams: ListResourcesEndpointParams,
): Promise<ResourceListing> =>
  getData<ResourceListing>(cancelToken)(buildResourcesEndpoint(endpointParams));

export { listResources };
