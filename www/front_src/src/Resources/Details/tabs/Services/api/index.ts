import { getData, buildListingEndpoint } from '@centreon/ui';

import { monitoringEndpoint } from '../../../../api/endpoint';
import { ServiceListing } from '../models';

const hostEndpoint = `${monitoringEndpoint}/hosts`;

const getServicesEndpoint = (hostId: number): string => {
  return `${hostEndpoint}/${hostId}/services`;
};

const buildListServicesEndpoint = ({ parameters, hostId }): string => {
  return buildListingEndpoint({
    baseEndpoint: getServicesEndpoint(hostId),
    parameters,
  });
};

const listServices = (cancelToken) => (hostId): Promise<ServiceListing> => {
  return getData<ServiceListing>(cancelToken)(
    buildListServicesEndpoint({
      parameters: {
        limit: 100,
      },
      hostId,
    }),
  );
};

export { listServices };
