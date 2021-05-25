import { CancelToken } from 'axios';

import { getData } from '@centreon/ui';

import { ResourceListing } from '../../models';

import { buildResourcesEndpoint, ListResourcesProps } from './endpoint';

const listResources =
  (cancelToken: CancelToken) =>
  (endpointParams: ListResourcesProps): Promise<ResourceListing> =>
    getData<ResourceListing>(cancelToken)(
      buildResourcesEndpoint(endpointParams),
    );

export { listResources };
