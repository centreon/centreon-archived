import { CancelToken } from 'axios';

import { getData } from '@centreon/ui';

import { buildResourcesEndpoint, ListResourcesProps } from './endpoint';
import { ResourceListing } from '../../models';

const listResources = (cancelToken: CancelToken) => (
  parameters: ListResourcesProps,
): Promise<ResourceListing> =>
  getData<ResourceListing>(cancelToken)(buildResourcesEndpoint(parameters));

export { listResources };
